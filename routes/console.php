<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Rutin: Reset sesi pengguna 1x dalam 7 hari setiap hari Senin pukul 00:01 WIB
Schedule::command('session:reset-weekly-monday')->weeklyOn(1, '00:01');

// Jadwal Rutin: Pembersihan foto dan data biometrik kedaluwarsa 2 tahun (UU PDP No. 27/2022)
Schedule::command('biometric:purge-expired')->dailyAt('02:00');

// Jadwal Rutin: Peringatan dini denda Demurrage kontainer (H-3, H-2, H-1)
Schedule::command('shipment:check-demurrage')->dailyAt('07:30');

// Jadwal Rutin: Deteksi otomatis realtime kedatangan & ETA kargo via Free Open APIs (Setiap jam)
Schedule::command('tracking:sync-live-eta')->hourly()->withoutOverlapping();

// Jadwal Rutin: Backup otomatis database harian & retensi arsip dokumen 10 tahun (Sesuai MoM Poin 11)
Schedule::command('backup:archive-10yr')->dailyAt('01:00');

// Alias untuk audit dan CLI check
Artisan::command('demurrage:check-alerts {--dry-run?}', function () {
    return $this->call('shipment:check-demurrage', array_filter(['--dry-run' => $this->option('dry-run')]));
})->purpose('Alias untuk pemeriksaan harian sisa Free Time kontainer');
