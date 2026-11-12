<?php

namespace App\Services\Shipment;

use App\Models\AccountingPeriod;
use App\Models\AuditLog;
use App\Models\ProjectJumpRequest;
use App\Models\Shipment;
use App\Models\ShipmentStageEntry;
use App\Models\StagePrerequisite;
use Illuminate\Support\Facades\DB;

class StageJumpService
{
    /**
     * Memvalidasi apakah proyek memenuhi prasyarat minimum untuk melompat ke tahap tertentu
     */
    public function validatePrerequisites(Shipment $shipment, string $targetStage): array
    {
        $prereq = StagePrerequisite::where('stage_code', $targetStage)->first();

        if (!$prereq) {
            return [
                'can_jump' => true,
                'missing_fields' => [],
                'missing_docs' => [],
                'needs_approval' => false,
                'approver_role' => null,
            ];
        }

        $missingFields = [];
        foreach ($prereq->required_fields as $field) {
            if (empty($shipment->{$field})) {
                $missingFields[] = $field;
            }
        }

        $missingDocs = [];
        if (!empty($prereq->required_documents)) {
            $existingDocTypes = $shipment->documents()->pluck('jenis_dokumen')->toArray();
            foreach ($prereq->required_documents as $docType) {
                if (!in_array($docType, $existingDocTypes, true)) {
                    $missingDocs[] = $docType;
                }
            }
        }

        $canJump = empty($missingFields) && empty($missingDocs);

        return [
            'can_jump' => $canJump,
            'prerequisite' => $prereq,
            'missing_fields' => $missingFields,
            'missing_docs' => $missingDocs,
            'needs_approval' => $prereq->is_approval_required,
            'approver_role' => $prereq->approver_role,
        ];
    }

    /**
     * Ajukan Permintaan Lompat Tahap
     */
    public function requestJump(
        Shipment $shipment,
        string $targetStage,
        string $reason,
        string $financialTreatment = 'OPENING_BALANCE',
        ?string $userId = null
    ): ProjectJumpRequest {
        $validation = $this->validatePrerequisites($shipment, $targetStage);

        if (!$validation['can_jump']) {
            $msg = 'Prasyarat tahap tujuan belum terpenuhi. Field kurang: ' . implode(', ', $validation['missing_fields'])
                . ' | Dokumen kurang: ' . implode(', ', $validation['missing_docs']);
            throw new \DomainException($msg);
        }

        $user = auth()->user();
        $isApprover = $user && ($user->isSuperAdmin() || ($validation['approver_role'] && $user->hasRole($validation['approver_role'])));

        // Jika tidak membutuhkan approval atau pengguna sendiri adalah penyetuju berwenang
        if (!$validation['needs_approval'] || $isApprover) {
            $this->executeJump($shipment, $targetStage, $financialTreatment, $reason);

            return ProjectJumpRequest::create([
                'shipment_id' => $shipment->id,
                'from_stage' => $shipment->getOriginal('status_tahap') ?? $shipment->status_tahap,
                'to_stage' => $targetStage,
                'reason' => $reason,
                'financial_treatment' => $financialTreatment,
                'status' => 'APPROVED',
                'requested_by_id' => $userId ?? auth()->id(),
                'approved_by_id' => $userId ?? auth()->id(),
                'decided_at' => now(),
            ]);
        }

        return ProjectJumpRequest::create([
            'shipment_id' => $shipment->id,
            'from_stage' => $shipment->status_tahap,
            'to_stage' => $targetStage,
            'reason' => $reason,
            'financial_treatment' => $financialTreatment,
            'status' => 'PENDING',
            'requested_by_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Eksekusi Lompat Tahap Proyek Secara Nyata
     */
    public function executeJump(
        Shipment $shipment,
        string $targetStage,
        string $financialTreatment = 'OPENING_BALANCE',
        string $sourceNote = 'Impor Data Historis / Lompat Tahap'
    ): void {
        $prereqs = StagePrerequisite::orderBy('stage_number', 'asc')->get()->keyBy('stage_code');
        $targetPrereq = $prereqs->get($targetStage);
        $targetNumber = $targetPrereq ? $targetPrereq->stage_number : 1;

        // Validasi aturan finansial F16: Larangan posting historis ke periode yang terkunci
        if ($financialTreatment === 'HISTORICAL_POSTING') {
            if (AccountingPeriod::isDateLocked(now())) {
                throw new \DomainException(
                    "Posting historis ditolak: Periode buku saat ini berstatus DIKUNCI. Silakan pilih opsi 'Saldo Awal' untuk melanjutkan lompat tahap tanpa jurnal ganda."
                );
            }
        }

        DB::transaction(function () use ($shipment, $targetStage, $targetNumber, $prereqs, $sourceNote, $financialTreatment) {
            $oldStage = $shipment->status_tahap;

            // 1. Tandai seluruh tahap sebelum tahap target sebagai IMPORTED
            foreach ($prereqs as $code => $pr) {
                if ($pr->stage_number < $targetNumber) {
                    ShipmentStageEntry::updateOrCreate(
                        [
                            'shipment_id' => $shipment->id,
                            'stage_number' => $pr->stage_number,
                        ],
                        [
                            'stage_code' => $pr->stage_code,
                            'entry_mode' => 'IMPORTED',
                            'original_date' => $shipment->tgl_bl_awb ?? now()->toDateString(),
                            'source_note' => $sourceNote,
                            'backfill_status' => 'COMPLETE',
                        ]
                    );
                } elseif ($pr->stage_number === $targetNumber) {
                    // Tahap target ditandai sebagai NATIVE (tahap mulai aktif)
                    ShipmentStageEntry::updateOrCreate(
                        [
                            'shipment_id' => $shipment->id,
                            'stage_number' => $pr->stage_number,
                        ],
                        [
                            'stage_code' => $pr->stage_code,
                            'entry_mode' => 'NATIVE',
                            'original_date' => now()->toDateString(),
                            'source_note' => "Titik mulai proses aktif",
                            'backfill_status' => 'COMPLETE',
                        ]
                    );
                }
            }

            // 2. Perbarui status tahap shipment
            $shipment->update([
                'status_tahap' => $targetStage,
                'catatan' => "[Lompat Tahap: {$targetStage}] Dimulai dari data historis ({$financialTreatment}) pada " . now()->format('d/m/Y H:i') . "\n" . ($shipment->catatan ?? ''),
            ]);

            // 3. Catat jejak audit
            AuditLog::record('STAGE_JUMP_EXECUTED', 'Shipment', $shipment->id, ['stage' => $oldStage], [
                'target_stage' => $targetStage,
                'financial_treatment' => $financialTreatment,
                'source_note' => $sourceNote,
            ]);
        });
    }

    /**
     * Fitur Backfill: Melengkapi data pada tahap lampau yang dilewati tanpa merusak tahap aktif
     */
    public function backfillStageData(Shipment $shipment, int $stageNumber, array $data): ShipmentStageEntry
    {
        $entry = ShipmentStageEntry::where('shipment_id', $shipment->id)
            ->where('stage_number', $stageNumber)
            ->firstOrFail();

        $shipment->update($data);
        $entry->update([
            'backfill_status' => 'COMPLETE',
            'source_note' => ($entry->source_note ? $entry->source_note . " | " : "") . "Dilengkapi via Backfill pada " . now()->format('d/m/Y H:i'),
        ]);

        AuditLog::record('STAGE_BACKFILL', 'Shipment', $shipment->id, null, [
            'stage_number' => $stageNumber,
            'data' => $data,
        ]);

        return $entry;
    }
}
