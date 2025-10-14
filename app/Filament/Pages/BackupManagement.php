<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class BackupManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    protected static string $view = 'filament.pages.backup-management';
    
    protected static ?string $navigationLabel = 'Backup Management';
    
    protected static ?string $title = 'Backup Management';
    
    protected static ?int $navigationSort = 10;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_backup')
                ->label('Create Full Backup')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->action(function () {
                    Artisan::call('backup:data', ['--type' => 'all']);
                    $this->dispatch('backup-created');
                })
                ->successNotificationTitle('Backup created successfully!'),
                
            Action::make('create_users_backup')
                ->label('Backup Users Only')
                ->icon('heroicon-o-users')
                ->color('info')
                ->action(function () {
                    Artisan::call('backup:data', ['--type' => 'users']);
                    $this->dispatch('backup-created');
                })
                ->successNotificationTitle('Users backup created successfully!'),
                
            Action::make('create_jobs_backup')
                ->label('Backup Job Board')
                ->icon('heroicon-o-briefcase')
                ->color('warning')
                ->action(function () {
                    Artisan::call('backup:data', ['--type' => 'companies']);
                    Artisan::call('backup:data', ['--type' => 'jobs']);
                    Artisan::call('backup:data', ['--type' => 'job_interactions']);
                    $this->dispatch('backup-created');
                })
                ->successNotificationTitle('Job board backup created successfully!'),
                
            Action::make('create_security_logs_backup')
                ->label('Backup Security Logs')
                ->icon('heroicon-o-shield-check')
                ->color('danger')
                ->action(function () {
                    Artisan::call('backup:data', ['--type' => 'security_logs']);
                    $this->dispatch('backup-created');
                })
                ->successNotificationTitle('Security logs backup created successfully!'),
        ];
    }

    public function getBackups(): array
    {
        $backupDir = 'backups';
        $backups = Storage::directories($backupDir);
        
        // Sort by name (timestamp) descending
        usort($backups, function($a, $b) {
            return strcmp(basename($b), basename($a));
        });
        
        $backupList = [];
        foreach ($backups as $backup) {
            $backupName = basename($backup);
            $files = Storage::allFiles($backup);
            $size = 0;
            foreach ($files as $file) {
                $size += Storage::size($file);
            }
            
            $backupList[] = [
                'name' => $backupName,
                'date' => Carbon::createFromFormat('Y-m-d_H-i-s', $backupName)->format('M j, Y H:i'),
                'size' => $this->formatBytes($size),
                'files' => count($files),
                'path' => $backup,
            ];
        }
        
        return $backupList;
    }

    public function deleteBackup(string $backupName): void
    {
        Storage::deleteDirectory("backups/{$backupName}");
        $this->dispatch('backup-deleted');
    }

    public function downloadBackup(string $backupName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $backupPath = "backups/{$backupName}";
        $files = Storage::allFiles($backupPath);
        
        return response()->streamDownload(function () use ($files) {
            $zip = new \ZipArchive();
            $tempFile = tempnam(sys_get_temp_dir(), 'backup_');
            $zip->open($tempFile, \ZipArchive::CREATE);
            
            foreach ($files as $file) {
                $content = Storage::get($file);
                $zip->addFromString(basename($file), $content);
            }
            
            $zip->close();
            echo file_get_contents($tempFile);
            unlink($tempFile);
        }, "backup_{$backupName}.zip");
    }

    public function cleanupOldBackups(): void
    {
        $backupDir = 'backups';
        $backups = Storage::directories($backupDir);
        $cutoffDate = Carbon::now()->subDays(30);
        
        foreach ($backups as $backup) {
            $backupName = basename($backup);
            $backupDate = Carbon::createFromFormat('Y-m-d_H-i-s', $backupName);
            
            if ($backupDate->lt($cutoffDate)) {
                Storage::deleteDirectory($backup);
            }
        }
        
        $this->dispatch('backups-cleaned');
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
