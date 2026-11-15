<?php

namespace App\Services\Hrm;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\SystemSetting;

class AnnualTaxReconciliationService
{
    /**
     * Hitung nilai PTKP tahunan (UU Harmonisasi Peraturan Perpajakan / UU HPP)
     */
    public static function getAnnualPtkp(?string $ptkp): float
    {
        $ptkp = strtoupper(trim($ptkp ?? 'TK/0'));
        $rules = SystemSetting::get('tax_ptkp_rules', []);

        if (!empty($rules[$ptkp])) {
            return (float) $rules[$ptkp];
        }

        return match ($ptkp) {
            'TK/0' => 54000000.0,
            'TK/1', 'K/0' => 58500000.0,
            'TK/2', 'K/1' => 63000000.0,
            'TK/3', 'K/2' => 67500000.0,
            'K/3' => 72000000.0,
            default => 54000000.0,
        };
    }

    /**
     * Hitung PPh 21 terutang setahun menggunakan Tarif Progresif Pasal 17 ayat (1) huruf a UU PPh/HPP
     */
    public static function calculateArticle17AnnualTax(float $pkp): float
    {
        if ($pkp <= 0) {
            return 0.0;
        }

        $params = SystemSetting::get('tax_pph21_parameters', []);
        $brackets = $params['brackets'] ?? [
            ['max' => 60000000.0, 'rate' => 0.05],
            ['max' => 250000000.0, 'rate' => 0.15],
            ['max' => 500000000.0, 'rate' => 0.25],
            ['max' => 5000000000.0, 'rate' => 0.30],
            ['max' => null, 'rate' => 0.35],
        ];

        $tax = 0.0;
        $prevLimit = 0.0;

        foreach ($brackets as $b) {
            $rate = (float) $b['rate'];
            $maxLimit = $b['max'] ? (float) $b['max'] : null;

            if ($maxLimit === null) {
                // Bracket tertinggi
                if ($pkp > $prevLimit) {
                    $tax += ($pkp - $prevLimit) * $rate;
                }
                break;
            }

            $band = $maxLimit - $prevLimit;
            if ($pkp > $prevLimit) {
                $taxableInBand = min($pkp - $prevLimit, $band);
                $tax += $taxableInBand * $rate;
            }

            $prevLimit = $maxLimit;
            if ($pkp <= $prevLimit) {
                break;
            }
        }

        return round($tax);
    }

    /**
     * Rekonsiliasi Pajak PPh 21 Tahunan (Masa Pajak Terakhir / Masa Desember)
     */
    public function reconcileEmployee(Employee $employee, int $year): array
    {
        // Ambil seluruh payslip pegawai pada tahun bersangkutan
        $payslips = Payslip::where('employee_id', $employee->id)
            ->whereHas('payrollRun', function ($q) use ($year) {
                $q->whereYear('period_end', $year);
            })
            ->with('lines')
            ->get();

        $monthCount = $payslips->count();
        $totalGross = (float) $payslips->sum('gross');

        // Total potongan pajak PPh 21 yang sudah dipotong bulanan (TER)
        $taxAlreadyWithheld = 0.0;
        $totalJhtJpDeductions = 0.0;

        foreach ($payslips as $slip) {
            foreach ($slip->lines as $line) {
                if ($line->code === 'DED_PPH21') {
                    $taxAlreadyWithheld += abs((float) $line->amount);
                }
                if (in_array($line->code, ['DED_BPJS_JHT', 'DED_BPJS_JP'])) {
                    $totalJhtJpDeductions += abs((float) $line->amount);
                }
            }
        }

        // Biaya Jabatan (Default 5% dari bruto, maksimal 500.000 per bulan atau 6.000.000 per tahun)
        $taxParams = SystemSetting::get('tax_pph21_parameters', []);
        $biayaJabatanRate = (float) ($taxParams['biaya_jabatan_rate'] ?? 0.05);
        $maxTahunan = (float) ($taxParams['max_biaya_jabatan_tahunan'] ?? 6000000.0);
        $maxBulanan = (float) ($taxParams['max_biaya_jabatan_bulanan'] ?? 500000.0);

        $maxBiayaJabatan = min($maxTahunan, $maxBulanan * max(1, $monthCount));
        $biayaJabatan = min($totalGross * $biayaJabatanRate, $maxBiayaJabatan);

        // Total Pengurang Penghasilan Bruto
        $totalPengurang = $biayaJabatan + $totalJhtJpDeductions;

        // Penghasilan Neto Setahun
        $penghasilanNeto = max(0.0, $totalGross - $totalPengurang);

        // PTKP Setahun
        $ptkp = self::getAnnualPtkp($employee->tax_ptkp_status);

        // Penghasilan Kena Pajak (PKP) dibulatkan ke bawah ribuan rupiah penuh
        $pkpRaw = max(0.0, $penghasilanNeto - $ptkp);
        $pkp = floor($pkpRaw / 1000) * 1000;

        // PPh 21 Terutang Setahun (Pasal 17)
        $pph21AnnualOwed = self::calculateArticle17AnnualTax($pkp);

        // Selisih Masa Pajak Terakhir (Desember)
        // Jika positif: Kurang Bayar (dipotongkan pada gaji Des)
        // Jika negatif: Lebih Bayar (dikembalikan/restitusi ke karyawan)
        $difference = $pph21AnnualOwed - $taxAlreadyWithheld;

        return [
            'year' => $year,
            'employee' => $employee,
            'months_worked' => $monthCount,
            'total_gross' => $totalGross,
            'biaya_jabatan' => round($biayaJabatan),
            'iuran_pensiun_jht' => round($totalJhtJpDeductions),
            'total_pengurang' => round($totalPengurang),
            'penghasilan_neto' => round($penghasilanNeto),
            'ptkp_status' => $employee->tax_ptkp_status ?? 'TK/0',
            'ptkp_amount' => $ptkp,
            'pkp' => $pkp,
            'pph21_annual_owed' => $pph21AnnualOwed,
            'pph21_already_withheld' => round($taxAlreadyWithheld),
            'difference' => round($difference),
            'status' => $difference > 0 ? 'UNDERPAID' : ($difference < 0 ? 'OVERPAID' : 'BALANCED'),
        ];
    }
}
