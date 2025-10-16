<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                ->form([
                    \Filament\Forms\Components\Select::make('backup_name')
                        ->label('Select Backup to Restore')
                        ->options(function () {
                            $backups = $this->getBackups();
                            $options = [];
                            foreach ($backups as $backup) {
                                $options[$backup['name']] = $backup['date'].' ('.$backup['size'].')';
                            }

                            return $options;
                        })
                        ->required()
                        ->searchable()
                        ->placeholder('Choose a backup to restore from...'),
                ])
                ->action(function (array $data) {
                    $this->restoreFromBackup($data['backup_name']);
                })
                ->modalHeading('Restore from Backup')
                ->modalDescription('⚠️ WARNING: This will overwrite ALL existing data with data from the selected backup. This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, Restore')
                ->modalCancelActionLabel('Cancel')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalIconColor('warning'),

            Action::make('upload_and_restore')
                ->label('Upload & Restore')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('backup_file')
                        ->label('Upload Backup File or Folder')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'application/x-tar', 'application/gzip'])
                        ->maxSize(1024 * 1024 * 200) // 200MB max
                        ->required()
                        ->helperText('Upload a ZIP file or compressed folder containing backup data. Maximum size: 200MB')
                        ->disk('local')
                        ->directory('temp-uploads')
                        ->multiple(false)
                        ->reorderable(false)
                        ->appendFiles(false),
                ])
                ->action(function (array $data) {
                    $this->restoreFromUploadedFile($data['backup_file']);
                })
                ->modalHeading('Upload & Restore from File')
                ->modalDescription('⚠️ WARNING: This will overwrite ALL existing data with data from the uploaded backup file. This action cannot be undone.')
                ->modalSubmitActionLabel('Upload & Restore')
                ->modalCancelActionLabel('Cancel')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalIconColor('warning'),
        ];
    }

    public function getBackups(): array
    {
        $backupDir = 'backups';
        $backups = Storage::disk('local')->directories($backupDir);

        // Sort by name (timestamp) descending
        usort($backups, function ($a, $b) {
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
        try {
            \Log::info("Attempting to delete backup: {$backupName}");

            Storage::disk('local')->deleteDirectory("backups/{$backupName}");

            \Log::info("Backup deleted successfully: {$backupName}");

            // Use Filament's notification system
            \Filament\Notifications\Notification::make()
                ->title('Backup Deleted')
                ->body("✅ Backup '{$backupName}' deleted successfully!")
                ->success()
                ->send();

            // Redirect to refresh the page
            $this->redirect(request()->url());

        } catch (\Exception $e) {
            \Log::error("Failed to delete backup {$backupName}: ".$e->getMessage());

            \Filament\Notifications\Notification::make()
                ->title('Delete Failed')
                ->body('❌ Failed to delete backup: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function downloadBackup(string $backupName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $backupPath = "backups/{$backupName}";
        $files = Storage::disk('local')->allFiles($backupPath);

        return response()->streamDownload(function () use ($files) {
            $zip = new \ZipArchive;
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

    public function getTotalBackupSize(): string
    {
        $backups = $this->getBackups();
        $totalSizeBytes = 0;

        foreach ($backups as $backup) {
            $sizeStr = $backup['size'];
            $sizeValue = (float) str_replace([' KB', ' MB', ' GB', ' B'], '', $sizeStr);

            if (strpos($sizeStr, 'GB') !== false) {
                $totalSizeBytes += $sizeValue * 1024 * 1024 * 1024;
            } elseif (strpos($sizeStr, 'MB') !== false) {
                $totalSizeBytes += $sizeValue * 1024 * 1024;
            } elseif (strpos($sizeStr, 'KB') !== false) {
                $totalSizeBytes += $sizeValue * 1024;
            } else {
                $totalSizeBytes += $sizeValue;
            }
        }

        // Convert to MB
        $totalSizeMB = $totalSizeBytes / (1024 * 1024);

        return number_format($totalSizeMB, 2).' MB';
    }

    public function cleanupOldBackups(): void
    {
        $backupDir = 'backups';
        $backups = Storage::disk('local')->directories($backupDir);

        // Sort backups by name (timestamp) descending (newest first)
        usort($backups, function ($a, $b) {
            return strcmp(basename($b), basename($a));
        });

        $keepCount = 5; // Keep the last 5 backups
        $deletedCount = 0;

        // Delete all backups except the last 5
        if (count($backups) > $keepCount) {
            $backupsToDelete = array_slice($backups, $keepCount);

            foreach ($backupsToDelete as $backup) {
                Storage::disk('local')->deleteDirectory($backup);
                $deletedCount++;
            }
        }

        \Filament\Notifications\Notification::make()
            ->title('Cleanup Completed')
            ->body("✅ Cleanup completed! Deleted {$deletedCount} old backups. Kept the last 5 backups.")
            ->success()
            ->send();

        $this->dispatch('backups-cleaned');
    }

    public function restoreFromUploadedFile(string $filePath): void
    {
        try {
            \Log::info('Restore from uploaded file: '.$filePath);

            // Check if uploaded file exists
            if (! Storage::disk('local')->exists($filePath)) {
                \Log::error('Uploaded file not found: '.$filePath);
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Uploaded file not found: '.$filePath,
                ]);

                return;
            }

            // Get file info
            $fullFilePath = Storage::disk('local')->path($filePath);
            $fileInfo = pathinfo($fullFilePath);
            $extension = strtolower($fileInfo['extension'] ?? '');

            \Log::info('File extension: '.$extension);

            // Create temporary directory for extraction
            $tempDir = 'temp-restore-'.time();
            $extractPath = "temp/{$tempDir}";

            // Ensure temp directory exists
            Storage::disk('local')->makeDirectory($extractPath);

            // Handle different file types
            if (in_array($extension, ['zip'])) {
                // Handle ZIP files
                $zip = new \ZipArchive;

                if ($zip->open($fullFilePath) !== true) {
                    \Log::error('Cannot open ZIP file: '.$fullFilePath);
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'Cannot open uploaded ZIP file. Please ensure it\'s a valid backup file.',
                    ]);

                    return;
                }

                // Extract all files
                $zip->extractTo(Storage::disk('local')->path($extractPath));
                $zip->close();

                \Log::info('ZIP file extracted to: '.$extractPath);

            } elseif (in_array($extension, ['tar', 'gz', 'tgz'])) {
                // Handle TAR files
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'TAR files are not yet supported. Please use ZIP files for backup uploads.',
                ]);

                // Cleanup
                Storage::disk('local')->deleteDirectory($extractPath);
                Storage::disk('local')->delete($filePath);

                return;

            } else {
                // Handle unknown file types - try to treat as directory
                \Log::info('Unknown file type, checking if it\'s a directory structure...');

                // Check if the uploaded path contains backup files directly
                if (Storage::disk('local')->exists("{$filePath}/backup_summary.json")) {
                    // It's already a backup directory, copy it
                    $this->copyDirectory($filePath, $extractPath);
                } else {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'Unsupported file type. Please upload a ZIP file containing backup data.',
                    ]);

                    // Cleanup
                    Storage::disk('local')->deleteDirectory($extractPath);
                    Storage::disk('local')->delete($filePath);

                    return;
                }
            }

            // Check if backup_summary.json exists (validate it's a backup)
            if (! Storage::disk('local')->exists("{$extractPath}/backup_summary.json")) {
                \Log::error('Invalid backup file - no backup_summary.json found');
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Invalid backup file. The uploaded file does not contain a valid backup (missing backup_summary.json).',
                ]);

                // Cleanup
                Storage::disk('local')->deleteDirectory($extractPath);
                Storage::disk('local')->delete($filePath);

                return;
            }

            // Run restore command with extracted files
            $exitCode = Artisan::call('restore:data', [
                'backup_path' => $extractPath,
                '--confirm' => true,
            ]);

            $output = Artisan::output();

            \Log::info('Restore from uploaded file completed with exit code: '.$exitCode);
            \Log::info('Restore output: '.$output);

            // Cleanup temporary files
            Storage::disk('local')->deleteDirectory($extractPath);
            Storage::disk('local')->delete($filePath);

            if ($exitCode === 0) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => '✅ Backup restored successfully from uploaded file! All data has been restored.',
                ]);
                $this->dispatch('backup-restored');
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => '❌ Restore failed: '.$output,
                ]);
                $this->dispatch('backup-restore-failed', 'Restore command failed with exit code: '.$exitCode.'. Output: '.$output);
            }

        } catch (\Exception $e) {
            \Log::error('Restore from uploaded file exception: '.$e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => '❌ Restore failed: '.$e->getMessage(),
            ]);
            $this->dispatch('backup-restore-failed', 'Exception: '.$e->getMessage());

            // Cleanup on error
            Storage::disk('local')->deleteDirectory($extractPath);
            Storage::disk('local')->delete($filePath);
        }
    }

    private function copyDirectory(string $source, string $destination): void
    {
        $sourcePath = Storage::disk('local')->path($source);
        $destPath = Storage::disk('local')->path($destination);

        if (! is_dir($sourcePath)) {
            throw new \Exception("Source directory does not exist: {$sourcePath}");
        }

        if (! is_dir($destPath)) {
            mkdir($destPath, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destPath.DIRECTORY_SEPARATOR.$iterator->getSubPathName();

            if ($item->isDir()) {
                mkdir($target, 0755, true);
            } else {
                copy($item, $target);
            }
        }
    }

    public function restoreFromBackup(string $backupName): void
    {
        try {
            // Log that the method was called
            \Log::info('Restore method called with backup: '.$backupName);

            $backupPath = "backups/{$backupName}";

            // Check if backup exists in local storage
            if (! Storage::disk('local')->exists($backupPath)) {
                \Log::error('Backup not found: '.$backupPath);
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Backup not found: '.$backupPath,
                ]);

                return;
            }

            \Log::info('Backup found, starting restore process...');

            // Run restore command
            $exitCode = Artisan::call('restore:data', [
                'backup_path' => $backupPath,
                '--confirm' => true,
            ]);

            // Get the command output
            $output = Artisan::output();

            \Log::info('Restore command completed with exit code: '.$exitCode);
            \Log::info('Restore output: '.$output);

            if ($exitCode === 0) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => '✅ Backup restored successfully! All data has been restored from: '.$backupName,
                ]);
                $this->dispatch('backup-restored');
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => '❌ Restore failed: '.$output,
                ]);
                $this->dispatch('backup-restore-failed', 'Restore command failed with exit code: '.$exitCode.'. Output: '.$output);
            }

        } catch (\Exception $e) {
            \Log::error('Restore exception: '.$e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => '❌ Restore failed: '.$e->getMessage(),
            ]);
            $this->dispatch('backup-restore-failed', 'Exception: '.$e->getMessage());
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
