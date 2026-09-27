<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('auth:prune-recovery-codes', function () {
    $count = DB::table('password_reset_codes')->where('expires_at', '<', now())->delete();
    $this->info("Removed {$count} expired recovery codes.");
})->purpose('Remove expired password recovery codes');

Schedule::command('auth:prune-recovery-codes')->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
