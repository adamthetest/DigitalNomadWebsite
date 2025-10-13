<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\City;
use App\Models\Article;
use App\Models\Deal;
use App\Models\NewsletterSubscriber;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\UserStatsWidget::class,
            \App\Filament\Widgets\SecurityStatsWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
