<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Necesita el cron de DirectAdmin que corre "php artisan schedule:run" cada minuto.
foreach (config('services.social.times') as $time) {
    Schedule::command('tnl:redes')->dailyAt($time)->withoutOverlapping(10);
}
