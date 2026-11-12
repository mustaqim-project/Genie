<?php

namespace App\Services\Ceisa;

use App\Models\CeisaDocument;

class CeisaDocumentService
{
    protected CeisaClientService $client;

    public function __construct(CeisaClientService $client)
    {
        $this->client = $client;
    }

    /**
     * Validasi Pre-Check Skema JSON Dokumen Pabean ke Gateway DJBC (/document/check)
     */
    public function checkSchema(CeisaDocument $document): array
    {
        if ((bool) config('ceisa.api.read_only', true)) {
            return [
                'status_code' => 403,
                'success' => false,
                'message' => 'Mode Tarik Data Saja (Read-Only) aktif: validasi/upload skema ke CEISA diblokir.',
                'data' => ['message' => 'Mode Tarik Data Saja (Read-Only) aktif.'],
            ];
        }

        if (empty($document->raw_payload)) {
            $shipment = $document->shipment ?? \App\Models\Shipment::where('ceisa_document_id', $document->id)->first();
            if ($shipment) {
                app(\App\Services\Ceisa\CeisaHsCodeService::class)->generatePibOrPebFromPackingList($shipment);
                $document->refresh();
            }
        }

        $payload = $document->raw_payload;
        if (empty($payload)) {
            throw new \Exception('Payload dokumen belum disiapkan untuk validasi skema.');
        }

        $endpoint = config('ceisa.endpoints.check_schema', '/document/check');
        $result = $this->client->send('POST', $endpoint, $payload, $document->nomor_aju);

        return $result;
    }

    /**
     * Kirim dokumen ke CEISA 4.0 DJBC (/document)
     */
    public function submit(CeisaDocument $document): array
    {
        if ((bool) config('ceisa.api.read_only', true)) {
            return [
                'status_code' => 403,
                'success' => false,
                'message' => 'Mode Tarik Data Saja (Read-Only) aktif: pengiriman/upload dokumen ke CEISA diblokir.',
                'data' => ['message' => 'Mode Tarik Data Saja (Read-Only) aktif.'],
            ];
        }

        if (empty($document->raw_payload)) {
            $shipment = $document->shipment ?? \App\Models\Shipment::where('ceisa_document_id', $document->id)->first();
            if ($shipment) {
                app(\App\Services\Ceisa\CeisaHsCodeService::class)->generatePibOrPebFromPackingList($shipment);
                $document->refresh();
            }
        }

        $payload = $document->raw_payload;
        if (empty($payload)) {
            throw new \Exception('Payload dokumen belum disiapkan.');
        }

        $endpoint = config('ceisa.endpoints.kirim_dokumen', '/document');
        $result = $this->client->send('POST', $endpoint, $payload, $document->nomor_aju);

        if ($result['success'] ?? false) {
            $document->update([
                'status' => 'SUBMITTED',
                'status_description' => 'Dokumen berhasil dikirim ke gateway CEISA 4.0 DJBC, menunggu pemrosesan sistem.',
                'last_response' => $result['data'] ?? [],
            ]);
        } else {
            $document->update([
                'status' => 'REJECT',
                'status_description' => 'Penolakan gateway: ' . json_encode($result['data'] ?? []),
                'last_response' => $result['data'] ?? [],
            ]);
        }

        return $result;
    }

    /**
     * Cek status dokumen ke CEISA 4.0 (GET /status/{nomorAju})
     */
    public function checkStatus(CeisaDocument $document): array
    {
        $baseEndpoint = config('ceisa.endpoints.status_dokumen', '/status');
        $endpoint = rtrim($baseEndpoint, '/') . '/' . $document->nomor_aju;

        $result = $this->client->send('GET', $endpoint, [], $document->nomor_aju);

        if ($result['success'] ?? false) {
            $data = $result['data'] ?? [];

            // Parsing format resmi CEISA 4.0 dataStatus
            $latestStatus = null;
            $keterangan = null;
            if (!empty($data['dataStatus']) && is_array($data['dataStatus'])) {
                $lastStatusItem = end($data['dataStatus']);
                $latestStatus = $lastStatusItem['kodeStatus'] ?? null;
                $keterangan = $lastStatusItem['keterangan'] ?? null;
            }

            $newStatus = $data['status'] ?? ($latestStatus ?? $document->status);
            $newKeterangan = $data['keterangan'] ?? ($keterangan ?? $document->status_description);

            $document->update([
                'status' => $newStatus,
                'status_description' => $newKeterangan,
                'last_response' => $data,
            ]);
        }

        return $result;
    }

    /**
     * Tarik Respon Resmi (Billing / SPPB / NPE) via GET /status/{nomorAju}
     */
    public function fetchResponse(CeisaDocument $document): array
    {
        $baseEndpoint = config('ceisa.endpoints.status_dokumen', '/status');
        $endpoint = rtrim($baseEndpoint, '/') . '/' . $document->nomor_aju;

        $result = $this->client->send('GET', $endpoint, [], $document->nomor_aju);

        if ($result['success'] ?? false) {
            $data = $result['data'] ?? [];
            $parsed = CeisaDocumentSyncService::parseCeisaStatusResponse(
                is_array($data['data'] ?? null) ? $data['data'] : $data,
                (string) ($document->jenis_dokumen ?? '20')
            );

            $updates = [
                'last_response' => $data,
                'status' => $parsed['status'],
                'status_description' => $parsed['status_description'],
                'status_billing' => $parsed['status_billing'],
            ];

            if (!empty($parsed['nomor_daftar'])) {
                $updates['nomor_daftar'] = $parsed['nomor_daftar'];
            }
            if (!empty($parsed['tanggal_daftar'])) {
                $updates['tanggal_daftar'] = $parsed['tanggal_daftar'];
            }
            if (!empty($parsed['jalur'])) {
                $updates['jalur'] = strtoupper($parsed['jalur']);
            }
            if (!empty($parsed['nomor_sppb_npe'])) {
                $updates['nomor_sppb_npe'] = $parsed['nomor_sppb_npe'];
            }
            if (!empty($parsed['tanggal_sppb_npe'])) {
                $updates['tanggal_sppb_npe'] = $parsed['tanggal_sppb_npe'];
            }
            if (!empty($parsed['kode_billing'])) {
                $updates['kode_billing'] = $parsed['kode_billing'];
            }
            if (!empty($parsed['total_billing'])) {
                $updates['total_billing'] = $parsed['total_billing'];
            }
            if (!empty($parsed['tgl_jatuh_tempo_billing'])) {
                $updates['tgl_jatuh_tempo_billing'] = $parsed['tgl_jatuh_tempo_billing'];
            }

            $document->update($updates);
        }

        return $result;
    }
}
