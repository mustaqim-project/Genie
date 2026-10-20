<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;

class FinanceRecordCashMutationTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'cashbank_record_mutation';
    }

    public function getDescription(): string
    {
        return 'Mencatat mutasi penerimaan kas/bank (IN) atau pengeluaran operasional (OUT) seperti biaya behandle pelabuhan, jaminan kontainer, operasional driver, atau transfer kas.';
    }

    public function getCategory(): string
    {
        return 'Keuangan & Akuntansi';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => ['IN', 'OUT'],
                    'description' => 'Tipe mutasi: IN (Penerimaan kas/dana masuk) atau OUT (Pengeluaran kas/biaya).',
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Nominal transaksi dalam Rupiah (IDR).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Uraian keterangan transaksi (misal: Biaya Behandle Jalur Merah Kontainer WHSU5219482).',
                ],
                'account_name' => [
                    'type' => 'string',
                    'description' => 'Nama atau bank rekening yang digunakan (misal: BCA, Mandiri, Kas Kecil).',
                ],
            ],
            'required' => ['type', 'amount', 'description'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $type = strtoupper($arguments['type'] ?? 'OUT');
        $rawAmount = abs((float) ($arguments['amount'] ?? 0));
        $amount = ($type === 'OUT') ? -$rawAmount : $rawAmount;
        $description = trim((string) ($arguments['description'] ?? 'Mutasi Kas/Bank'));

        $account = null;
        if (!empty($arguments['account_name'])) {
            $name = trim($arguments['account_name']);
            $account = CashBankAccount::where('name', 'like', "%{$name}%")
                ->orWhere('bank', 'like', "%{$name}%")
                ->first();
        }

        if (!$account) {
            $account = CashBankAccount::first();
        }

        if (!$account) {
            $account = CashBankAccount::create([
                'name' => 'BCA Operasional Utama',
                'bank' => 'BCA',
                'account_no' => '521-098-2811',
            ]);
        }

        $trx = CashBankTransaction::create([
            'account_id' => $account->id,
            'trx_date' => now(),
            'amount' => $amount,
            'description' => $description,
            'reconciled' => true,
        ]);

        return [
            'success' => true,
            'message' => "Mutasi kas {$type} senilai Rp " . number_format($rawAmount, 0, ',', '.') . " berhasil dicatat.",
            'transaction' => [
                'id' => $trx->id,
                'account' => $account->name,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'date' => now()->toDateTimeString(),
            ],
        ];
    }
}
