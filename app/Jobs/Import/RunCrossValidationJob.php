<?php

namespace App\Jobs\Import;

use App\Models\ImportBatch;
use App\Models\ImportDocument;
use App\Services\Shipment\CrossDocumentValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunCrossValidationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ImportBatch $batch;

    /**
     * Create a new job instance.
     */
    public function __construct(ImportBatch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Execute the job.
     */
    public function handle(CrossDocumentValidationService $validationService): void
    {
        Log::info("Memulai validasi silang dokumen untuk ImportBatch: {$this->batch->batch_no}");

        $documents = $this->batch->documents()->get();
        foreach ($documents as $doc) {
            $validationService->validateDocument($doc);
        }

        $this->batch->update(['status' => 'READY']);
        Log::info("Validasi silang selesai untuk batch {$this->batch->batch_no}. Status READY.");
    }
}
