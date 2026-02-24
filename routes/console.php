<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

\Illuminate\Support\Facades\Schedule::command('inventory:check-expiry')->daily();
\Illuminate\Support\Facades\Schedule::command('subscription:check-expiry')->daily();
\Illuminate\Support\Facades\Schedule::command('app:check-communication-triggers')->dailyTime('09:00');
