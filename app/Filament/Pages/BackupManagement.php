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
                
            Action::make('restore_from_backup')
                ->label('Restore from Backup')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->action(function () {
                    $this->dispatch('open-restore-modal');
                })
                ->requiresConfirmation()
                ->modalHeading('Restore from Backup')
                ->modalDescription('This will overwrite all existing data with data from a backup. Are you sure you want to continue?')
                ->modalSubmitActionLabel('Yes, Restore'),
        ];
    }

    public function getBackups(): array
    {
        $backupDir = 'backups';
        $backups = Storage::disk('local')->directories($backupDir);
        
        // Sort by name (timestamp) descending
        usort($backups, function($a, $b) {
            return strcmp(basename($b), basename($a));
        });
        
        $backupList = [];
        foreach ($backups as $backup) {
            $backupName = basename($backup);
            $files = Storage::disk('local')->allFiles($backup);
            $size = 0;
            foreach ($files as $file) {
                $size += Storage::disk('local')->size($file);
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
        Storage::disk('local')->deleteDirectory("backups/{$backupName}");
        $this->dispatch('backup-deleted');
    }

    public function downloadBackup(string $backupName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $backupPath = "backups/{$backupName}";
        $files = Storage::disk('local')->allFiles($backupPath);
        
        return response()->streamDownload(function () use ($files) {
            $zip = new \ZipArchive();
            $tempFile = tempnam(sys_get_temp_dir(), 'backup_');
            $zip->open($tempFile, \ZipArchive::CREATE);
            
            foreach ($files as $file) {
                $content = Storage::disk('local')->get($file);
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
        $backups = Storage::disk('local')->directories($backupDir);
        $cutoffDate = Carbon::now()->subDays(30);
        
        foreach ($backups as $backup) {
            $backupName = basename($backup);
            $backupDate = Carbon::createFromFormat('Y-m-d_H-i-s', $backupName);
            
            if ($backupDate->lt($cutoffDate)) {
                Storage::disk('local')->deleteDirectory($backup);
            }
        }
        
        $this->dispatch('backups-cleaned');
    }

    public function restoreFromBackup(string $backupName): void
    {
        try {
            // Log that the method was called
            \Log::info('Restore method called with backup: ' . $backupName);
            
            $backupPath = "backups/{$backupName}";
            
            // Check if backup exists in local storage
            if (!Storage::disk('local')->exists($backupPath)) {
                \Log::error('Backup not found: ' . $backupPath);
                $this->dispatch('backup-restore-failed', 'Backup not found: ' . $backupPath);
                return;
            }
            
            \Log::info('Backup found, starting restore process...');
            
            // Run restore command
            $exitCode = Artisan::call('restore:data', [
                'backup_path' => $backupPath,
                '--confirm' => true
            ]);
            
            // Get the command output
            $output = Artisan::output();
            
            \Log::info('Restore command completed with exit code: ' . $exitCode);
            \Log::info('Restore output: ' . $output);
            
            if ($exitCode === 0) {
                $this->dispatch('backup-restored');
            } else {
                $this->dispatch('backup-restore-failed', 'Restore command failed with exit code: ' . $exitCode . '. Output: ' . $output);
            }
            
        } catch (\Exception $e) {
            \Log::error('Restore exception: ' . $e->getMessage());
            $this->dispatch('backup-restore-failed', 'Exception: ' . $e->getMessage());
        }
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
