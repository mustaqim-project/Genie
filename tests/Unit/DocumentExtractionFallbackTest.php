<?php

namespace Tests\Unit;

use App\Services\Shipment\DocumentExtractionService;
use Tests\TestCase;

class DocumentExtractionFallbackTest extends TestCase
{
    public function test_stream_fallback_extraction(): void
    {
        $service = app(DocumentExtractionService::class);
        $tmp = tempnam(sys_get_temp_dir(), 'test_pdf_') . '.pdf';
        
        $streamContent = gzcompress("(SAMPLE INVOICE TEXT) Tj");
        $pdfDummy = "%PDF-1.4\nstream\r\n" . $streamContent . "\r\nendstream\n%%EOF";
        file_put_contents($tmp, $pdfDummy);

        try {
            $extracted = $service->extractTextFromFile($tmp);
            $this->assertStringContainsString('SAMPLE INVOICE TEXT', $extracted);
        } finally {
            @unlink($tmp);
        }
    }
}
