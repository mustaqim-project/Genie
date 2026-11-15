<?php

namespace Tests\Feature;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\Client;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\PayslipLine;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Accounting\AccountingService;
use App\Services\Hrm\AnnualTaxReconciliationService;
use App\Services\Hrm\BiometricVerificationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingAndTaxFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_opening_balance_creates_balanced_journal_entry(): void
    {
        $service = app(AccountingService::class);
        $entry = $service->recordOpeningBalance();

        $this->assertNotNull($entry);
        $this->assertEquals('OPENING_BALANCE', $entry->source_type);

        $totalDebit = $entry->lines->sum('debit');
        $totalCredit = $entry->lines->sum('credit');

        $this->assertEquals($totalDebit, $totalCredit, 'Total Debit harus sama persis dengan Total Kredit (Balanced)');
        $this->assertEquals(850000000.0, $totalDebit, 'Total modal awal harus Rp 850.000.000');
    }

    public function test_accounting_period_lock_observer_blocks_journal_mutations(): void
    {
        // Kunci periode buku bulan Januari 2026
        AccountingPeriod::create([
            'name' => 'Periode Januari 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'is_closed' => true,
            'closed_at' => now(),
            'closing_notes' => 'Periode audit Januari dikunci',
        ]);

        $this->assertTrue(AccountingPeriod::isDateLocked('2026-01-15'));

        // Coba buat JournalEntry pada tanggal terkunci
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/dikunci|closed/i');

        $kasCoa = ChartOfAccount::first();

        JournalEntry::create([
            'entry_number' => 'JV-202601-TEST',
            'entry_date' => '2026-01-15',
            'source_type' => 'MANUAL',
            'source_id' => 'TEST-01',
            'description' => 'Jurnal uji coba di periode terkunci',
        ]);
    }

    public function test_customs_billing_payment_enforces_escrow_verification_gate(): void
    {
        $client = Client::first();
        $shipment = Shipment::create([
            'project_no' => 'SHP-202611-TEST01',
            'client_id' => $client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BLTEST998811',
            'status_tahap' => 'BILLING_ISSUED',
        ]);

        // Login sebagai staf biasa tanpa role R-OWN
        $staffUser = User::whereHas('roles', fn($q) => $q->where('code', 'R-DOC'))->first();
        $this->actingAs($staffUser);

        $service = app(AccountingService::class);

        // Harus melempar DomainException karena belum ada dana talangan masuk (FUNDS_CLEARED)
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Escrow Gate|FUNDS_CLEARED/i');

        $service->recordCustomsBillingPayment($shipment, 18500000.0);
    }

    public function test_annual_tax_reconciliation_calculates_article_17_correctly(): void
    {
        // PKP Rp 100.000.000:
        // Lapisan 1: 60 Juta x 5% = 3.000.000
        // Lapisan 2: 40 Juta x 15% = 6.000.000
        // Total PPh 21 = Rp 9.000.000
        $tax = AnnualTaxReconciliationService::calculateArticle17AnnualTax(100000000.0);
        $this->assertEquals(9000000.0, $tax);

        // PKP Rp 50.000.000:
        // 50 Juta x 5% = 2.500.000
        $taxSmall = AnnualTaxReconciliationService::calculateArticle17AnnualTax(50000000.0);
        $this->assertEquals(2500000.0, $taxSmall);
    }

    public function test_biometric_verification_cosine_similarity(): void
    {
        $bioService = new BiometricVerificationService();

        // Uji vektor identik -> Cosine similarity harus mendekati 1.0
        $vecA = array_fill(0, 128, 0.5);
        $vecB = array_fill(0, 128, 0.5);

        $similarity = $bioService->calculateCosineSimilarity($vecA, $vecB);
        $this->assertEqualsWithDelta(1.0, $similarity, 0.001);

        // Uji vektor berlawanan arah -> Cosine similarity = -1.0
        $vecOpposite = array_fill(0, 128, -0.5);
        $simOpposite = $bioService->calculateCosineSimilarity($vecA, $vecOpposite);
        $this->assertEqualsWithDelta(-1.0, $simOpposite, 0.001);

        // Uji vektor ortogonal (tegak lurus) -> Cosine similarity = 0.0
        $vecOrthoA = array_merge(array_fill(0, 64, 1.0), array_fill(0, 64, 0.0));
        $vecOrthoB = array_merge(array_fill(0, 64, 0.0), array_fill(0, 64, 1.0));
        $simOrtho = $bioService->calculateCosineSimilarity($vecOrthoA, $vecOrthoB);
        $this->assertEqualsWithDelta(0.0, $simOrtho, 0.001);
    }
}
