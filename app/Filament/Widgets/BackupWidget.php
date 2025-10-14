<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupWidget extends Widget
{
    protected static string $view = 'filament.widgets.backup-widget';
    
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'backupStats' => $this->getBackupStats(),
            'recentBackups' => $this->getRecentBackups(),
            'databaseStats' => $this->getDatabaseStats(),
        ];
    }

    private function getBackupStats(): array
    {
        $backupDir = 'backups';
        $backups = Storage::directories($backupDir);
        
        $totalBackups = count($backups);
        $totalSize = 0;
        $oldestBackup = null;
        $newestBackup = null;
        
        foreach ($backups as $backup) {
            $files = Storage::allFiles($backup);
            foreach ($files as $file) {
                $totalSize += Storage::size($file);
            }
            
            $backupName = basename($backup);
            if (!$oldestBackup || $backupName < $oldestBackup) {
                $oldestBackup = $backupName;
            }
            if (!$newestBackup || $backupName > $newestBackup) {
                $newestBackup = $backupName;
            }
        }
        
        return [
            'total' => $totalBackups,
            'total_size' => $this->formatBytes($totalSize),
            'oldest' => $oldestBackup ? Carbon::createFromFormat('Y-m-d_H-i-s', $oldestBackup)->format('M j, Y') : 'None',
            'newest' => $newestBackup ? Carbon::createFromFormat('Y-m-d_H-i-s', $newestBackup)->format('M j, Y') : 'None',
        ];
    }

    private function getRecentBackups(): array
    {
        $backupDir = 'backups';
        $backups = Storage::directories($backupDir);
        
        // Sort by name (timestamp) descending
        usort($backups, function($a, $b) {
            return strcmp(basename($b), basename($a));
        });
        
        $recentBackups = [];
        foreach (array_slice($backups, 0, 5) as $backup) {
            $backupName = basename($backup);
            $files = Storage::allFiles($backup);
            $size = 0;
            foreach ($files as $file) {
                $size += Storage::size($file);
            }
            
            $recentBackups[] = [
                'name' => $backupName,
                'date' => Carbon::createFromFormat('Y-m-d_H-i-s', $backupName)->format('M j, Y H:i'),
                'size' => $this->formatBytes($size),
                'files' => count($files),
            ];
        }
        
        return $recentBackups;
    }

    private function getDatabaseStats(): array
    {
        $tables = [
            'users', 'cities', 'articles', 'deals', 'newsletter_subscribers', 
            'favorites', 'coworking_spaces', 'cost_items', 'visa_rules', 
            'affiliate_links', 'companies', 'jobs', 'job_user_interactions'
        ];
        
        $stats = [];
        $totalRecords = 0;
        
        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $stats[$table] = $count;
                $totalRecords += $count;
            } catch (\Exception $e) {
                $stats[$table] = 0;
            }
        }
        
        return [
            'tables' => $stats,
            'total_records' => $totalRecords,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
