<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule social profile automation tasks
Schedule::command('nomads:update-locations --limit=50')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('nomads:suggest-skills --limit=20')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule job board automation tasks
Schedule::command('jobs:scrape --limit=20')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule AI data processing tasks
Schedule::command('ai:process city --queue')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('ai:process job --queue')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('ai:process user --queue')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

// Full AI data refresh weekly
Schedule::command('ai:process all --queue --force')
    ->weekly()
    ->withoutOverlapping()
    ->runInBackground();
