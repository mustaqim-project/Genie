<?php

namespace App\Services\Hrm;

class TaxTerCalculatorService
{
    // Batas Plafon Upah
    public const BPJS_KES_MAX_BASE = 12000000.0;
    public const BPJS_JP_MAX_BASE = 11086300.0; // Batas upah JP berlaku mulai Maret 2026

    // Tarif BPJS Karyawan
    public const BPJS_KES_EE_RATE = 0.01; // 1%
    public const BPJS_JHT_EE_RATE = 0.02; // 2%
    public const BPJS_JP_EE_RATE = 0.01;  // 1%

    // Tarif BPJS Perusahaan (Beban Perusahaan)
    public const BPJS_KES_ER_RATE = 0.04;  // 4%
    public const BPJS_JHT_ER_RATE = 0.037; // 3.7%
    public const BPJS_JP_ER_RATE = 0.02;   // 2%
    public const BPJS_JKK_ER_RATE = 0.0024; // 0.24% (Risiko Sangat Rendah - Golongan I)
    public const BPJS_JKM_ER_RATE = 0.003;  // 0.3%

    /**
     * Tentukan Kategori TER (A, B, atau C) berdasarkan status PTKP
     */
    public static function getTerCategory(?string $ptkp): string
    {
        $ptkp = strtoupper(trim($ptkp ?? 'TK/0'));

        return match ($ptkp) {
            'TK/0', 'TK/1', 'K/0' => 'A',
            'TK/2', 'TK/3', 'K/1', 'K/2' => 'B',
            'K/3' => 'C',
            default => 'A',
        };
    }

    /**
     * Hitung Rincian BPJS Karyawan dan Perusahaan
     */
    public static function calculateBpjs(float $baseSalary): array
    {
        $kesBase = min($baseSalary, self::BPJS_KES_MAX_BASE);
        $jpBase = min($baseSalary, self::BPJS_JP_MAX_BASE);

        // Potongan Karyawan (Deduction)
        $kesEe = round($kesBase * self::BPJS_KES_EE_RATE);
        $jhtEe = round($baseSalary * self::BPJS_JHT_EE_RATE);
        $jpEe = round($jpBase * self::BPJS_JP_EE_RATE);
        $totalEmployeeBpjs = $kesEe + $jhtEe + $jpEe;

        // Tanggungan Perusahaan (Company Expense)
        $kesEr = round($kesBase * self::BPJS_KES_ER_RATE);
        $jhtEr = round($baseSalary * self::BPJS_JHT_ER_RATE);
        $jpEr = round($jpBase * self::BPJS_JP_ER_RATE);
        $jkkEr = round($baseSalary * self::BPJS_JKK_ER_RATE);
        $jkmEr = round($baseSalary * self::BPJS_JKM_ER_RATE);
        $totalCompanyBpjs = $kesEr + $jhtEr + $jpEr + $jkkEr + $jkmEr;

        return [
            'employee' => [
                'kes' => $kesEe,
                'jht' => $jhtEe,
                'jp' => $jpEe,
                'total' => $totalEmployeeBpjs,
            ],
            'company' => [
                'kes' => $kesEr,
                'jht' => $jhtEr,
                'jp' => $jpEr,
                'jkk' => $jkkEr,
                'jkm' => $jkmEr,
                'total' => $totalCompanyBpjs,
            ],
        ];
    }

    /**
     * Cari Tarif Efektif Rata-rata (TER) PMK 168/2023 berdasarkan Bruto Pajak
     */
    public static function getTerRate(string $category, float $grossTaxable): float
    {
        return match ($category) {
            'A' => self::getTerRateA($grossTaxable),
            'B' => self::getTerRateB($grossTaxable),
            'C' => self::getTerRateC($grossTaxable),
            default => self::getTerRateA($grossTaxable),
        };
    }

    /**
     * Hitung Pajak PPh 21 Bulanan Lengkap
     */
    public static function calculatePph21(
        float $cashEarnings,
        float $companyPremiums,
        ?string $ptkp = 'TK/0',
        bool $hasNpwp = true
    ): array {
        $category = self::getTerCategory($ptkp);
        $taxGross = $cashEarnings + $companyPremiums;
        $rate = self::getTerRate($category, $taxGross);

        $pph21 = round($taxGross * $rate);
        if (!$hasNpwp && $pph21 > 0) {
            $pph21 = round($pph21 * 1.2); // Tarif 20% lebih tinggi tanpa NPWP
        }

        return [
            'category' => $category,
            'tax_gross' => $taxGross,
            'rate' => $rate,
            'rate_percent' => $rate * 100,
            'pph21' => $pph21,
        ];
    }

    protected static function getTerRateA(float $bruto): float
    {
        if ($bruto <= 5400000) return 0.0;
        if ($bruto <= 5650000) return 0.0025;
        if ($bruto <= 5950000) return 0.005;
        if ($bruto <= 6300000) return 0.0075;
        if ($bruto <= 6750000) return 0.01;
        if ($bruto <= 7500000) return 0.0125;
        if ($bruto <= 8550000) return 0.015;
        if ($bruto <= 9650000) return 0.0175;
        if ($bruto <= 10050000) return 0.02;
        if ($bruto <= 10350000) return 0.0225;
        if ($bruto <= 10700000) return 0.025;
        if ($bruto <= 11050000) return 0.03;
        if ($bruto <= 11600000) return 0.035;
        if ($bruto <= 12500000) return 0.04;
        if ($bruto <= 13750000) return 0.05;
        if ($bruto <= 15100000) return 0.06;
        if ($bruto <= 16950000) return 0.07;
        if ($bruto <= 19750000) return 0.08;
        if ($bruto <= 24150000) return 0.09;
        if ($bruto <= 26450000) return 0.10;
        if ($bruto <= 28000000) return 0.11;
        if ($bruto <= 30050000) return 0.12;
        if ($bruto <= 32400000) return 0.13;
        if ($bruto <= 35400000) return 0.14;
        if ($bruto <= 39100000) return 0.15;
        if ($bruto <= 43850000) return 0.16;
        if ($bruto <= 47800000) return 0.17;
        if ($bruto <= 51400000) return 0.18;
        if ($bruto <= 56300000) return 0.19;
        if ($bruto <= 62200000) return 0.20;
        if ($bruto <= 68600000) return 0.21;
        if ($bruto <= 77500000) return 0.22;
        if ($bruto <= 89000000) return 0.23;
        if ($bruto <= 103000000) return 0.24;
        if ($bruto <= 125000000) return 0.25;
        if ($bruto <= 157000000) return 0.26;
        if ($bruto <= 206000000) return 0.27;
        if ($bruto <= 337000000) return 0.28;
        if ($bruto <= 454000000) return 0.29;
        if ($bruto <= 550000000) return 0.30;
        if ($bruto <= 695000000) return 0.31;
        if ($bruto <= 910000000) return 0.32;
        if ($bruto <= 1400000000) return 0.33;
        return 0.34;
    }

    protected static function getTerRateB(float $bruto): float
    {
        if ($bruto <= 6200000) return 0.0;
        if ($bruto <= 6500000) return 0.0025;
        if ($bruto <= 6850000) return 0.005;
        if ($bruto <= 7300000) return 0.0075;
        if ($bruto <= 9200000) return 0.01;
        if ($bruto <= 10750000) return 0.015;
        if ($bruto <= 11250000) return 0.02;
        if ($bruto <= 11600000) return 0.025;
        if ($bruto <= 12600000) return 0.03;
        if ($bruto <= 13600000) return 0.04;
        if ($bruto <= 14950000) return 0.05;
        if ($bruto <= 16400000) return 0.06;
        if ($bruto <= 18450000) return 0.07;
        if ($bruto <= 21850000) return 0.08;
        if ($bruto <= 26000000) return 0.09;
        if ($bruto <= 27700000) return 0.10;
        if ($bruto <= 29350000) return 0.11;
        if ($bruto <= 31450000) return 0.12;
        if ($bruto <= 33950000) return 0.13;
        if ($bruto <= 37100000) return 0.14;
        if ($bruto <= 41100000) return 0.15;
        if ($bruto <= 45800000) return 0.16;
        if ($bruto <= 49500000) return 0.17;
        if ($bruto <= 53800000) return 0.18;
        if ($bruto <= 58500000) return 0.19;
        if ($bruto <= 64000000) return 0.20;
        if ($bruto <= 71000000) return 0.21;
        if ($bruto <= 80000000) return 0.22;
        if ($bruto <= 93000000) return 0.23;
        if ($bruto <= 109000000) return 0.24;
        if ($bruto <= 129000000) return 0.25;
        if ($bruto <= 163000000) return 0.26;
        if ($bruto <= 211000000) return 0.27;
        if ($bruto <= 374000000) return 0.28;
        if ($bruto <= 459000000) return 0.29;
        if ($bruto <= 555000000) return 0.30;
        if ($bruto <= 704000000) return 0.31;
        if ($bruto <= 957000000) return 0.32;
        if ($bruto <= 1405000000) return 0.33;
        return 0.34;
    }

    protected static function getTerRateC(float $bruto): float
    {
        if ($bruto <= 6600000) return 0.0;
        if ($bruto <= 6950000) return 0.0025;
        if ($bruto <= 7350000) return 0.005;
        if ($bruto <= 7800000) return 0.0075;
        if ($bruto <= 8850000) return 0.01;
        if ($bruto <= 10900000) return 0.015;
        if ($bruto <= 11200000) return 0.02;
        if ($bruto <= 12050000) return 0.03;
        if ($bruto <= 12950000) return 0.04;
        if ($bruto <= 14150000) return 0.05;
        if ($bruto <= 15550000) return 0.06;
        if ($bruto <= 17050000) return 0.07;
        if ($bruto <= 19500000) return 0.08;
        if ($bruto <= 22700000) return 0.09;
        if ($bruto <= 24700000) return 0.10;
        if ($bruto <= 26350000) return 0.11;
        if ($bruto <= 28000000) return 0.12;
        if ($bruto <= 30300000) return 0.13;
        if ($bruto <= 32750000) return 0.14;
        if ($bruto <= 36000000) return 0.15;
        if ($bruto <= 39900000) return 0.16;
        if ($bruto <= 43800000) return 0.17;
        if ($bruto <= 47800000) return 0.18;
        if ($bruto <= 53000000) return 0.19;
        if ($bruto <= 57000000) return 0.20;
        if ($bruto <= 62000000) return 0.21;
        if ($bruto <= 70000000) return 0.22;
        if ($bruto <= 81000000) return 0.23;
        if ($bruto <= 96000000) return 0.24;
        if ($bruto <= 114000000) return 0.25;
        if ($bruto <= 134000000) return 0.26;
        if ($bruto <= 169000000) return 0.27;
        if ($bruto <= 221000000) return 0.28;
        if ($bruto <= 390000000) return 0.29;
        if ($bruto <= 463000000) return 0.30;
        if ($bruto <= 561000000) return 0.31;
        if ($bruto <= 709000000) return 0.32;
        if ($bruto <= 965000000) return 0.33;
        if ($bruto <= 1419000000) return 0.34;
        return 0.35;
    }
}
