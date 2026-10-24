<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Retención de datos biométricos (Ley 1581 de 2012): requiere `php artisan schedule:work` o un cron con `schedule:run`.
Schedule::command('biometrics:purge')->dailyAt('02:00');
