<?php

namespace App\Filament\Widgets;

use App\Models\SecurityLog;
use App\Models\BannedIp;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SecurityStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $last24Hours = now()->subHours(24);
        
        return [
            Stat::make('Failed Logins (24h)', SecurityLog::where('event_type', 'failed_login')
                ->where('created_at', '>=', $last24Hours)
                ->count())
                ->description('Failed login attempts in the last 24 hours')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
                
            Stat::make('Banned IPs', BannedIp::where('is_active', true)->count())
                ->description('Currently active IP bans')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('warning'),
                
            Stat::make('Security Events (Today)', SecurityLog::where('created_at', '>=', $today)->count())
                ->description('Total security events today')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
                
            Stat::make('Critical Events (24h)', SecurityLog::where('severity', 'critical')
                ->where('created_at', '>=', $last24Hours)
                ->count())
                ->description('Critical security events in the last 24 hours')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
        ];
    }
}
