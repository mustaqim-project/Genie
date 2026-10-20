<?php

namespace App\Services\Accounting;

use App\Models\CashBankAccount;
use App\Models\ChartOfAccount;
use App\Models\ClientInvoice;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Shipment;
use App\Models\VendorBill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountingService
{
    protected static ?array $coaCache = null;

    /**
     * Dapatkan ID Bagan Akun (COA) berdasarkan kodenya
     */
    public static function getCoaId(string $code): ?string
    {
        if (self::$coaCache === null) {
            self::$coaCache = ChartOfAccount::pluck('id', 'code')->toArray();
        }

        return self::$coaCache[$code] ?? null;
    }

    /**
     * Dapatkan Akun COA yang berelasi dengan Rekening Kas/Bank
     */
    public static function getCoaForCashBank(?CashBankAccount $account): ?string
    {
        if (!$account) {
            return self::getCoaId('1-1130'); // Default BCA
        }

        $bankLower = strtolower($account->bank . ' ' . $account->name);
        if (str_contains($bankLower, 'mandiri')) {
            return self::getCoaId('1-1120');
        } elseif (str_contains($bankLower, 'kas') || str_contains($bankLower, 'tunai') || str_contains($bankLower, 'kecil')) {
            return self::getCoaId('1-1110');
        }

        return self::getCoaId('1-1130'); // Default Bank BCA
    }

    /**
     * Helper untuk membuat JournalEntry dan JournalLines secara berimbang (Atomic)
     * Memastikan Debit == Credit
     */
    public static function createBalancedEntry(
        string $sourceType,
        string $sourceId,
        string $entryDate,
        string $memo,
        array $lines,
        ?string $postedById = null
    ): ?JournalEntry {
        // Cek apakah sudah pernah diposting untuk mencegah duplikasi (Idempotent)
        $existing = JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->first();

        if ($existing) {
            return $existing;
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        // Toleransi selisih pembulatan desimal sen
        if (abs($totalDebit - $totalCredit) > 0.05) {
            throw new \InvalidArgumentException(
                "Jurnal Akuntansi tidak seimbang! Total Debit (Rp " . number_format($totalDebit, 2) . 
                ") != Total Kredit (Rp " . number_format($totalCredit, 2) . ")"
            );
        }

        return DB::transaction(function () use ($sourceType, $sourceId, $entryDate, $memo, $lines, $postedById) {
            $entry = JournalEntry::create([
                'id' => (string) Str::uuid(),
                'entry_date' => $entryDate,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'memo' => $memo,
                'posted_by_id' => $postedById ?? auth()->id(),
            ]);

            foreach ($lines as $line) {
                if (($line['debit'] ?? 0) <= 0 && ($line['credit'] ?? 0) <= 0) {
                    continue;
                }

                JournalLine::create([
                    'id' => (string) Str::uuid(),
                    'entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            return $entry;
        });
    }

    /**
     * 1. AUTO-JOURNAL: Penerbitan Invoice Klien (Billing Forwarding & PPJK)
     * Standard:
     * DR: 1-1200 Piutang Usaha Klien (AR)
     * CR: 4-1100 Pendapatan Jasa Pengurusan Kepabeanan (PPJK Fee)
     * CR: 4-1200 Pendapatan Jasa Trucking & Angkutan
     * CR: 4-1300 Pendapatan Handling & Biaya Pelayaran
     * CR: 2-1600 Hutang PPN Keluaran (PPN 1.1% PMK 71/2022)
     * CR: 1-1300 Piutang Talangan Pabean (Kredit Reimbursable Outlay)
     */
    public function recordClientInvoiceIssued(ClientInvoice $invoice, ?string $userId = null): ?JournalEntry
    {
        $totalTagihan = (float) $invoice->total_tagihan_idr;
        $jasaPpjk = (float) $invoice->jasa_ppjk_idr;
        $trucking = (float) $invoice->biaya_trucking_idr;
        $pelayaran = (float) $invoice->biaya_pelayaran_idr;
        $ppnJasa = (float) $invoice->ppn_jasa_idr;
        $talanganPabean = (float) $invoice->talangan_pabean_idr;

        $lines = [];

        // Debit: Piutang Usaha Klien
        $lines[] = [
            'account_id' => self::getCoaId('1-1200'),
            'debit' => $totalTagihan,
            'credit' => 0,
        ];

        // Kredit: Pendapatan Jasa PPJK
        if ($jasaPpjk > 0) {
            $lines[] = [
                'account_id' => self::getCoaId('4-1100'),
                'debit' => 0,
                'credit' => $jasaPpjk,
            ];
        }

        // Kredit: Pendapatan Trucking
        if ($trucking > 0) {
            $lines[] = [
                'account_id' => self::getCoaId('4-1200'),
                'debit' => 0,
                'credit' => $trucking,
            ];
        }

        // Kredit: Pendapatan Handling & Pelayaran
        if ($pelayaran > 0) {
            $lines[] = [
                'account_id' => self::getCoaId('4-1300'),
                'debit' => 0,
                'credit' => $pelayaran,
            ];
        }

        // Kredit: Hutang PPN Keluaran
        if ($ppnJasa > 0) {
            $lines[] = [
                'account_id' => self::getCoaId('2-1600'),
                'debit' => 0,
                'credit' => $ppnJasa,
            ];
        }

        // Kredit: Penggantian Talangan Pabean Reimbursable (Pass-Through)
        if ($talanganPabean > 0) {
            $lines[] = [
                'account_id' => self::getCoaId('1-1300'),
                'debit' => 0,
                'credit' => $talanganPabean,
            ];
        }

        $memo = "Penerbitan Invoice {$invoice->invoice_no} a/n " . ($invoice->client?->name ?? 'Klien');
        $date = $invoice->tanggal ? $invoice->tanggal->format('Y-m-d') : now()->toDateString();

        return self::createBalancedEntry('INVOICE', $invoice->id, $date, $memo, $lines, $userId);
    }

    /**
     * 2. AUTO-JOURNAL: Pelunasan Invoice Klien
     * Standard:
     * DR: Kas & Bank (BCA 1-1130)
     * CR: 1-1200 Piutang Usaha Klien (AR)
     */
    public function recordClientInvoicePayment(
        ClientInvoice $invoice,
        float $amount,
        ?CashBankAccount $account = null,
        ?string $paidDate = null,
        ?string $userId = null
    ): ?JournalEntry {
        $bankCoaId = self::getCoaForCashBank($account);
        $arCoaId = self::getCoaId('1-1200');

        $lines = [
            [
                'account_id' => $bankCoaId,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_id' => $arCoaId,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        $memo = "Pelunasan Invoice {$invoice->invoice_no} via " . ($account?->name ?? 'Rekening Bank');
        $date = $paidDate ?? now()->toDateString();

        return self::createBalancedEntry('INVOICE_PAYMENT', $invoice->id, $date, $memo, $lines, $userId);
    }

    /**
     * 3. AUTO-JOURNAL: Pengakuan Tagihan Vendor (Accounts Payable)
     * Standard:
     * DR: 5-1100 / 5-1200 / 5-1300 Beban Pokok Forwarding (HPP)
     * CR: 2-1100 / 2-1110 / 2-1120 Hutang Usaha Vendor (AP)
     */
    public function recordVendorBillApproved(VendorBill $bill, ?string $userId = null): ?JournalEntry
    {
        $amount = (float) $bill->amount;

        $expenseCoaId = match ($bill->vendor_type) {
            'TRUCKING' => self::getCoaId('5-1200'),
            'SHIPPING_LINE' => self::getCoaId('5-1100'),
            'TPS' => self::getCoaId('5-1300'),
            default => self::getCoaId('5-1400'),
        };

        $payableCoaId = match ($bill->vendor_type) {
            'TRUCKING' => self::getCoaId('2-1110'),
            'SHIPPING_LINE' => self::getCoaId('2-1100'),
            'TPS' => self::getCoaId('2-1120'),
            default => self::getCoaId('2-1100'),
        };

        $lines = [
            [
                'account_id' => $expenseCoaId,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_id' => $payableCoaId,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        $memo = "Pencatatan Tagihan Vendor [{$bill->vendor_type}] Ref: {$bill->ref_no}";
        $date = $bill->created_at ? $bill->created_at->format('Y-m-d') : now()->toDateString();

        return self::createBalancedEntry('VENDOR_BILL', $bill->id, $date, $memo, $lines, $userId);
    }

    /**
     * 4. AUTO-JOURNAL: Pembayaran Tagihan Vendor (AP Settlement)
     * Standard:
     * DR: 2-1100 / 2-1110 / 2-1120 Hutang Usaha Vendor (AP)
     * CR: Kas & Bank (BCA 1-1130 / Mandiri 1-1120 / Kas Kecil 1-1110)
     */
    public function recordVendorBillPayment(
        VendorBill $bill,
        float $amount,
        CashBankAccount $account,
        ?string $paidDate = null,
        ?string $userId = null
    ): ?JournalEntry {
        $payableCoaId = match ($bill->vendor_type) {
            'TRUCKING' => self::getCoaId('2-1110'),
            'SHIPPING_LINE' => self::getCoaId('2-1100'),
            'TPS' => self::getCoaId('2-1120'),
            default => self::getCoaId('2-1100'),
        };

        $bankCoaId = self::getCoaForCashBank($account);

        $lines = [
            [
                'account_id' => $payableCoaId,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_id' => $bankCoaId,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        $memo = "Pelunasan Tagihan Vendor {$bill->ref_no} via {$account->name}";
        $date = $paidDate ?? now()->toDateString();

        return self::createBalancedEntry('BILL_PAYMENT', $bill->id, $date, $memo, $lines, $userId);
    }

    /**
     * 5. AUTO-JOURNAL: Talangan Billing Bea Cukai (MPN G3)
     * Standard:
     * DR: 1-1300 Piutang Talangan Pabean (Reimbursable Outlay)
     * CR: 1-1120 Bank Mandiri (Billing Pabean MPN G3)
     */
    public function recordCustomsBillingPayment(
        Shipment $shipment,
        float $amount,
        ?CashBankAccount $account = null,
        ?string $paidDate = null,
        ?string $userId = null
    ): ?JournalEntry {
        $piutangTalanganCoaId = self::getCoaId('1-1300');
        $mandiriCoaId = self::getCoaId('1-1120');

        $lines = [
            [
                'account_id' => $piutangTalanganCoaId,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_id' => $mandiriCoaId,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        $nomorAju = $shipment->ceisaDocument?->nomor_aju ?? $shipment->shipment_number;
        $memo = "Pembayaran Talangan Billing Bea Cukai (MPN G3) Aju: {$nomorAju}";
        $date = $paidDate ?? now()->toDateString();

        return self::createBalancedEntry('CUSTOMS_BILLING', $shipment->id, $date, $memo, $lines, $userId);
    }

    /**
     * Sinkronisasi Seluruh Transaksi Lampau yang Belum Terjurnal (Idempotent Backfill)
     */
    public function syncHistoricalTransactions(): array
    {
        $invoices = ClientInvoice::all();
        $bills = VendorBill::all();

        $invoicesJournaled = 0;
        $billsJournaled = 0;

        foreach ($invoices as $inv) {
            $entry = $this->recordClientInvoiceIssued($inv);
            if ($entry && $entry->wasRecentlyCreated) {
                $invoicesJournaled++;
            }

            if ($inv->status === 'PAID') {
                $bca = CashBankAccount::where('bank', 'like', '%BCA%')->first();
                $payEntry = $this->recordClientInvoicePayment(
                    $inv,
                    (float) $inv->total_tagihan_idr,
                    $bca,
                    $inv->paid_at ? $inv->paid_at->format('Y-m-d') : null
                );
                if ($payEntry && $payEntry->wasRecentlyCreated) {
                    $invoicesJournaled++;
                }
            }
        }

        foreach ($bills as $bill) {
            if ($bill->status === 'APPROVED' || $bill->status === 'PAID') {
                $entry = $this->recordVendorBillApproved($bill);
                if ($entry && $entry->wasRecentlyCreated) {
                    $billsJournaled++;
                }
            }

            if ($bill->status === 'PAID') {
                $account = CashBankAccount::first();
                $payEntry = $this->recordVendorBillPayment(
                    $bill,
                    (float) $bill->amount,
                    $account,
                    $bill->updated_at ? $bill->updated_at->format('Y-m-d') : null
                );
                if ($payEntry && $payEntry->wasRecentlyCreated) {
                    $billsJournaled++;
                }
            }
        }

        return [
            'invoices_journaled' => $invoicesJournaled,
            'bills_journaled' => $billsJournaled,
        ];
    }
}
