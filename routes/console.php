<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Rutin: Reset sesi pengguna 1x dalam 7 hari setiap hari Senin pukul 00:01 WIB
Schedule::command('session:reset-weekly-monday')->weeklyOn(1, '00:01');
