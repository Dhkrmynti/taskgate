<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('logs:prune --days=180')->daily();

// Prune activity logs older than 30 days
Schedule::call(function () {
    DB::table('activity_logs')->where('created_at', '<', now()->subDays(30))->delete();
})->daily();
