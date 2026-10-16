<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('jalvan:generate-daily-orders', function () {
    $count = app(\App\Services\RecurringOrderService::class)->generateDue();
    $this->info("Generated {$count} daily order(s).");
})->purpose('Generate due daily orders from active recurring plans');
