<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $usersWithFavorites = User::has('favorites')->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Verified Users', $verifiedUsers)
                ->description('Email verified users')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Recent Users', $recentUsers)
                ->description('Joined last 30 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Active Users', $usersWithFavorites)
                ->description('Users with favorites')
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning'),
        ];
    }
}
