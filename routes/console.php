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

// Schedule AI content generation tasks - Phase 4
Schedule::command('content:generate weekly --queue')
    ->weekly()
    ->sundays()
    ->at('09:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('content:generate city-guides --queue')
    ->weekly()
    ->sundays()
    ->at('10:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('content:generate top-cities --queue')
    ->monthly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule predictive analytics tasks - Phase 5
Schedule::command('analytics:process all --queue')
    ->daily()
    ->at('02:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('analytics:process cost_trends --queue')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('analytics:process trending_cities --queue')
    ->daily()
    ->at('04:00')
    ->withoutOverlapping()
    ->runInBackground();
