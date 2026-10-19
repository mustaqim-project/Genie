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
            
            $updates = [
                'last_response' => $data,
            ];

            // 1. Ekstraksi data pendaftaran dari dataStatus / root
            if (!empty($data['dataStatus']) && is_array($data['dataStatus'])) {
                $statusItem = end($data['dataStatus']);
                if (!empty($statusItem['nomorDaftar'])) {
                    $updates['nomor_daftar'] = $statusItem['nomorDaftar'];
                }
                if (!empty($statusItem['tanggalDaftar'])) {
                    $updates['tanggal_daftar'] = $statusItem['tanggalDaftar'];
                }
            }

            if (!empty($data['nomorDaftar'])) {
                $updates['nomor_daftar'] = $data['nomorDaftar'];
            }
            if (!empty($data['tanggalDaftar'])) {
                $updates['tanggal_daftar'] = $data['tanggalDaftar'];
            }
            if (!empty($data['jalur'])) {
                $updates['jalur'] = $data['jalur'];
            }
            if (!empty($data['status'])) {
                $updates['status'] = $data['status'];
            }
            if (!empty($data['keterangan'])) {
                $updates['status_description'] = $data['keterangan'];
            }

            // 2. Ekstraksi data respon resmi dari dataRespon DJBC
            if (!empty($data['dataRespon']) && is_array($data['dataRespon'])) {
                $responItem = end($data['dataRespon']);
                $kodeRespon = strtoupper($responItem['kodeRespon'] ?? '');

                if ($kodeRespon === 'SPPB') {
                    $updates['nomor_sppb_npe'] = $responItem['nomorRespon'] ?? null;
                    $updates['tanggal_sppb_npe'] = $responItem['tanggalRespon'] ?? now();
                    $updates['status'] = 'SPPB';
                } elseif ($kodeRespon === 'NPE') {
                    $updates['nomor_sppb_npe'] = $responItem['nomorRespon'] ?? null;
                    $updates['tanggal_sppb_npe'] = $responItem['tanggalRespon'] ?? now();
                    $updates['status'] = 'NPE';
                }

                if (!empty($responItem['keterangan'])) {
                    $updates['status_description'] = $responItem['keterangan'];
                }
            }

            // 3. Billing info
            if (!empty($data['billing'])) {
                $updates['kode_billing'] = $data['billing']['kodeBilling'] ?? null;
                $updates['total_billing'] = $data['billing']['totalNominal'] ?? null;
                $updates['tgl_jatuh_tempo_billing'] = $data['billing']['tglKadaluarsa'] ?? null;
                $updates['status_billing'] = 'UNPAID';
            }

            // 4. SPPB / NPE fallback
            if (!empty($data['sppb'])) {
                $updates['nomor_sppb_npe'] = $data['sppb']['nomorSppb'] ?? null;
                $updates['tanggal_sppb_npe'] = $data['sppb']['tanggalSppb'] ?? null;
                $updates['status'] = 'SPPB';
            }

            if (!empty($data['npe'])) {
                $updates['nomor_sppb_npe'] = $data['npe']['nomorNpe'] ?? null;
                $updates['tanggal_sppb_npe'] = $data['npe']['tanggalNpe'] ?? null;
                $updates['status'] = 'NPE';
            }

            $document->update($updates);
        }

        return $result;
    }
}
