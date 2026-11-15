<?php

namespace Tests\Feature\Accounting;

use App\Models\AccountingPeriod;
use App\Models\CashBankAccount;
use App\Models\ChartOfAccount;
use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Accounting\AccountingService;
use DomainException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class GeneralLedgerAndLockPeriodTest extends TestCase
{
    use DatabaseTransactions;

    protected User $financeUser;
    protected User $adminUser;
    protected Client $client;
    protected ChartOfAccount $arAccount;
    protected ChartOfAccount $revenueAccount;
    protected ChartOfAccount $cashBcaAccount;
    protected ChartOfAccount $cashMandiriAccount;
    protected CashBankAccount $bankBca;
    protected CashBankAccount $bankMandiri;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles & Permissions Setup
        $finPerm = Permission::firstOrCreate(
            ['code' => 'finance.manage'],
            ['name' => 'Manage Finance', 'module' => 'finance']
        );
        $finViewPerm = Permission::firstOrCreate(
            ['code' => 'finance.view'],
            ['name' => 'View Finance', 'module' => 'finance']
        );
        $finInvoicePerm = Permission::firstOrCreate(
            ['code' => 'finance.invoice'],
            ['name' => 'Create Finance Invoice', 'module' => 'finance']
        );
        $accPerm = Permission::firstOrCreate(
            ['code' => 'accounting.view'],
            ['name' => 'View Accounting', 'module' => 'accounting']
        );

        $finRole = Role::firstOrCreate(
            ['code' => 'R-FIN'],
            ['name' => 'Finance Staff', 'description' => 'Finance Staff']
        );
        $finRole->permissions()->syncWithoutDetaching([$finPerm->id, $finViewPerm->id, $finInvoicePerm->id, $accPerm->id]);

        $this->financeUser = User::firstOrCreate(
            ['email' => 'fin_gl_test@agungtrans.co.id'],
            [
                'name' => 'Finance GL Tester',
                'password' => bcrypt('password'),
                'role' => 'R-FIN',
                'is_active' => true,
            ]
        );
        $this->financeUser->roles()->syncWithoutDetaching([$finRole->id]);

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_gl_test@agungtrans.co.id'],
            [
                'name' => 'Admin GL Tester',
                'password' => bcrypt('password'),
                'role' => 'R-ADM',
                'is_active' => true,
            ]
        );

        // 2. Chart of Accounts Setup
        $this->arAccount = ChartOfAccount::firstOrCreate(
            ['code' => '1-1200'],
            ['name' => 'Piutang Usaha Klien', 'type' => 'ASSET', 'category' => 'CURRENT_ASSET', 'normal_balance' => 'DEBIT']
        );

        $this->revenueAccount = ChartOfAccount::firstOrCreate(
            ['code' => '4-1100'],
            ['name' => 'Pendapatan Jasa PPJK', 'type' => 'REVENUE', 'category' => 'OPERATING_REVENUE', 'normal_balance' => 'CREDIT']
        );

        $this->cashBcaAccount = ChartOfAccount::firstOrCreate(
            ['code' => '1-1130'],
            ['name' => 'Bank Central Asia (BCA)', 'type' => 'ASSET', 'category' => 'CASH_BANK', 'normal_balance' => 'DEBIT']
        );

        $this->cashMandiriAccount = ChartOfAccount::firstOrCreate(
            ['code' => '1-1120'],
            ['name' => 'Bank Mandiri', 'type' => 'ASSET', 'category' => 'CASH_BANK', 'normal_balance' => 'DEBIT']
        );

        // 3. Cash Bank Accounts Setup
        $this->bankBca = CashBankAccount::firstOrCreate(
            ['account_no' => '8880011223'],
            [
                'name' => 'BCA Operasional Utama',
                'bank' => 'BCA',
                'currency_id' => null,
            ]
        );

        $this->bankMandiri = CashBankAccount::firstOrCreate(
            ['account_no' => '1230004567'],
            [
                'name' => 'Mandiri Rekening Talangan',
                'bank' => 'Mandiri',
                'currency_id' => null,
            ]
        );

        // 4. Client Setup
        $this->client = Client::firstOrCreate(
            ['code' => 'CL-ACC-01'],
            [
                'name' => 'PT Akuntansi Maju Terus',
                'npwp' => '01.999.888.7-654.000',
                'address' => 'Jakarta Barat',
                'is_active' => true,
            ]
        );
    }

    /**
     * Tier 1: GL Balanced Entry Creation (Debit == Credit)
     */
    public function test_gl_creates_balanced_entry_successfully(): void
    {
        $entryDate = now()->format('Y-m-d');
        $lines = [
            ['account_id' => $this->arAccount->id, 'debit' => 1500000.00, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 1500000.00],
        ];

        $entry = AccountingService::createBalancedEntry(
            'TEST_SOURCE',
            (string) \Illuminate\Support\Str::uuid(),
            $entryDate,
            'Uji Jurnal Berimbang PPJK',
            $lines,
            $this->financeUser->id
        );

        $this->assertNotNull($entry);
        $this->assertEquals($entryDate, $entry->entry_date->format('Y-m-d'));
        $this->assertEquals(2, $entry->lines()->count());

        $totalDebit = $entry->lines()->sum('debit');
        $totalCredit = $entry->lines()->sum('credit');
        $this->assertEquals($totalDebit, $totalCredit);
        $this->assertEquals(1500000.00, (float) $totalDebit);
    }

    /**
     * Tier 2 (Boundary): GL Double-Entry Rounding Tolerance <= 0.05
     */
    public function test_gl_tolerates_minor_rounding_discrepancy_up_to_five_cents(): void
    {
        $entryDate = now()->format('Y-m-d');
        // Selisih 0.04 (dalam batas toleransi <= 0.05)
        $lines = [
            ['account_id' => $this->arAccount->id, 'debit' => 1000000.04, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 1000000.00],
        ];

        $entry = AccountingService::createBalancedEntry(
            'TEST_TOLERANCE',
            (string) \Illuminate\Support\Str::uuid(),
            $entryDate,
            'Uji Toleransi Desimal 0.04',
            $lines,
            $this->financeUser->id
        );

        $this->assertNotNull($entry);
    }

    /**
     * Tier 2 (Boundary): GL Double-Entry Imbalance > 0.05 Throws InvalidArgumentException
     */
    public function test_gl_rejects_imbalance_greater_than_five_cents(): void
    {
        $entryDate = now()->format('Y-m-d');
        // Selisih 0.06 (melebihi batas toleransi 0.05)
        $lines = [
            ['account_id' => $this->arAccount->id, 'debit' => 1000000.06, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 1000000.00],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Jurnal Akuntansi tidak seimbang');

        AccountingService::createBalancedEntry(
            'TEST_REJECT_IMBALANCE',
            (string) \Illuminate\Support\Str::uuid(),
            $entryDate,
            'Uji Penolakan Selisih 0.06',
            $lines,
            $this->financeUser->id
        );
    }

    /**
     * Tier 1 & Tier 2: JournalEntryObserver Blocks Creation on Locked Accounting Period
     */
    public function test_journal_entry_observer_blocks_creating_on_locked_period(): void
    {
        $start = now()->subMonths(3)->startOfMonth()->format('Y-m-d');
        $end = now()->subMonths(3)->endOfMonth()->format('Y-m-d');
        $lockedDate = now()->subMonths(3)->startOfMonth()->addDays(14)->format('Y-m-d');

        AccountingPeriod::create([
            'name' => 'Periode Kunci 3 Bulan Lalu',
            'start_date' => $start,
            'end_date' => $end,
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by_id' => $this->adminUser->id,
        ]);

        $this->assertTrue(AccountingPeriod::isDateLocked($lockedDate));

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('berada pada periode akuntansi yang telah DIKUNCI');

        JournalEntry::create([
            'entry_date' => $lockedDate,
            'source_type' => 'MANUAL_TEST',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'memo' => 'Percobaan posting ke periode terkunci',
            'posted_by_id' => $this->financeUser->id,
        ]);
    }

    /**
     * Tier 1 & Tier 2: JournalEntryObserver Blocks Updating on Locked Accounting Period
     */
    public function test_journal_entry_observer_blocks_updating_on_locked_period(): void
    {
        $activeDate = now()->format('Y-m-d');
        $start = now()->subMonths(2)->startOfMonth()->format('Y-m-d');
        $end = now()->subMonths(2)->endOfMonth()->format('Y-m-d');
        $lockedDate = now()->subMonths(2)->startOfMonth()->addDays(9)->format('Y-m-d');

        AccountingPeriod::create([
            'name' => 'Periode Kunci 2 Bulan Lalu',
            'start_date' => $start,
            'end_date' => $end,
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by_id' => $this->adminUser->id,
        ]);

        $entry = JournalEntry::create([
            'entry_date' => $activeDate,
            'source_type' => 'MANUAL_UPDATE_TEST',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'memo' => 'Jurnal Aktif',
            'posted_by_id' => $this->financeUser->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('berada pada periode akuntansi yang telah DIKUNCI');

        // Coba geser tanggal entri ke periode terkunci
        $entry->update(['entry_date' => $lockedDate]);
    }

    /**
     * Tier 1 & Tier 2: JournalEntryObserver Blocks Deleting on Locked Accounting Period
     */
    public function test_journal_entry_observer_blocks_deleting_on_locked_period(): void
    {
        $start = now()->subMonths(1)->startOfMonth()->format('Y-m-d');
        $end = now()->subMonths(1)->endOfMonth()->format('Y-m-d');
        $date = now()->subMonths(1)->startOfMonth()->addDays(4)->format('Y-m-d');

        $entry = JournalEntry::create([
            'entry_date' => $date,
            'source_type' => 'MANUAL_DELETE_TEST',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'memo' => 'Jurnal Sebelum Kunci',
            'posted_by_id' => $this->financeUser->id,
        ]);

        // Kunci periode
        AccountingPeriod::create([
            'name' => 'Periode Kunci Bulan Lalu',
            'start_date' => $start,
            'end_date' => $end,
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by_id' => $this->adminUser->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Penghapusan jurnal tanggal');

        $entry->delete();
    }

    /**
     * Tier 2: JournalLineObserver Blocks Modifying Lines Belonging to Locked Parent Entry
     */
    public function test_journal_line_observer_blocks_modifying_lines_of_locked_parent_entry(): void
    {
        $start = now()->subMonths(4)->startOfMonth()->format('Y-m-d');
        $end = now()->subMonths(4)->endOfMonth()->format('Y-m-d');
        $date = now()->subMonths(4)->startOfMonth()->addDays(19)->format('Y-m-d');

        $entry = JournalEntry::create([
            'entry_date' => $date,
            'source_type' => 'PARENT_LINE_TEST',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'memo' => 'Parent Jurnal',
            'posted_by_id' => $this->financeUser->id,
        ]);

        $line = JournalLine::create([
            'entry_id' => $entry->id,
            'account_id' => $this->arAccount->id,
            'debit' => 500000,
            'credit' => 0,
        ]);

        // Kunci periode buku
        AccountingPeriod::create([
            'name' => 'Periode Kunci 4 Bulan Lalu',
            'start_date' => $start,
            'end_date' => $end,
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by_id' => $this->adminUser->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('dilarang karena periode buku tanggal');

        $line->update(['debit' => 600000]);
    }

    /**
     * Tier 3: Cash & Bank Account COA Resolution and Account Balances
     */
    public function test_cash_bank_coa_resolution_and_integrity(): void
    {
        $bcaCoaId = AccountingService::getCoaForCashBank($this->bankBca);
        $this->assertEquals($this->cashBcaAccount->id, $bcaCoaId);

        $mandiriCoaId = AccountingService::getCoaForCashBank($this->bankMandiri);
        $this->assertEquals($this->cashMandiriAccount->id, $mandiriCoaId);

        $defaultCoaId = AccountingService::getCoaForCashBank(null);
        $this->assertEquals($this->cashBcaAccount->id, $defaultCoaId);
    }
}
