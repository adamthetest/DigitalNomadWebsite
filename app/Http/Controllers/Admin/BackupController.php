<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Display a listing of backups.
     */
    public function index()
    {
        $backups = $this->getBackups();

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Create a new backup.
     */
    public function create(Request $request)
    {
        $type = $request->input('type', 'all');
        $format = $request->input('format', 'json');

        try {
            Artisan::call('backup:data', [
                '--type' => $type,
                '--format' => $format,
            ]);

            $output = Artisan::output();

            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup created successfully!',
                    'output' => $output,
                ]);
            }

            // Handle regular form submissions
            return redirect()->back()->with('success', 'Backup created successfully!');

        } catch (\Exception $e) {
            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup failed: '.$e->getMessage(),
                ], 500);
            }

            // Handle regular form submissions
            return redirect()->back()->with('error', 'Backup failed: '.$e->getMessage());
        }
    }

    /**
     * Download a backup file.
     */
    public function download($backupDir, $filename)
    {
        $filePath = "backups/{$backupDir}/{$filename}";

        if (! Storage::exists($filePath)) {
            abort(404, 'Backup file not found');
        }

        return Storage::download($filePath);
    }

    /**
     * Download entire backup directory as zip file.
     */
    public function downloadZip($backupDir)
    {
        $backupPath = "backups/{$backupDir}";

        if (! Storage::exists($backupPath)) {
            abort(404, 'Backup directory not found');
        }

        $files = Storage::allFiles($backupPath);

        if (empty($files)) {
            abort(404, 'No files found in backup directory');
        }

        // Create a temporary zip file
        $zipFileName = "{$backupDir}.zip";
        $zipPath = storage_path("app/temp/{$zipFileName}");

        // Ensure temp directory exists
        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            abort(500, 'Cannot create zip file');
        }

        // Add files to zip
        foreach ($files as $file) {
            $relativePath = str_replace("{$backupPath}/", '', $file);
            $zip->addFromString($relativePath, Storage::get($file));
        }

        $zip->close();

        // Return the zip file as download
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Cleanup old backups (keep last 5, delete the rest).
     */
    public function cleanup(Request $request)
    {
        try {
            $backupDir = 'backups';
            $directories = Storage::directories($backupDir);
            
            // Sort backups by name (timestamp) descending (newest first)
            usort($directories, function ($a, $b) {
                return strcmp(basename($b), basename($a));
            });
            
            $keepCount = 5; // Keep the last 5 backups
            $deletedCount = 0;
            
            // Delete all backups except the last 5
            if (count($directories) > $keepCount) {
                $backupsToDelete = array_slice($directories, $keepCount);
                
                foreach ($backupsToDelete as $directory) {
                    Storage::deleteDirectory($directory);
                    $deletedCount++;
                }
            }

            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "✅ Cleanup completed! Deleted {$deletedCount} old backups. Kept the last 5 backups.",
                ]);
            }

            // Handle regular form submissions
            return redirect()->back()->with('success', "✅ Cleanup completed! Deleted {$deletedCount} old backups. Kept the last 5 backups.");

        } catch (\Exception $e) {
            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cleanup failed: '.$e->getMessage(),
                ], 500);
            }

            // Handle regular form submissions
            return redirect()->back()->with('error', 'Cleanup failed: '.$e->getMessage());
        }
    }

    /**
     * Delete a backup.
     */
    public function destroy($backupDir)
    {
        try {
            Storage::deleteDirectory("backups/{$backupDir}");

            // Handle AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup deleted successfully!',
                ]);
            }

            // Handle regular form submissions
            return redirect()->back()->with('success', 'Backup deleted successfully!');

        } catch (\Exception $e) {
            // Handle AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete backup: '.$e->getMessage(),
                ], 500);
            }

            // Handle regular form submissions
            return redirect()->back()->with('error', 'Failed to delete backup: '.$e->getMessage());
        }
    }

    /**
     * Restore from backup.
     */
    public function restore(Request $request, $backupDir)
    {
        $table = $request->input('table', 'all');

        try {
            Artisan::call('restore:data', [
                'backup_path' => "backups/{$backupDir}",
                '--table' => $table,
                '--confirm' => true,
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Data restored successfully!',
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of available backups.
     */
    private function getBackups()
    {
        $backups = [];

        if (Storage::exists('backups')) {
            $directories = Storage::directories('backups');

            foreach ($directories as $directory) {
                $backupDir = basename($directory);
                $backupInfo = $this->getBackupInfo($directory);

                $backups[] = [
                    'directory' => $backupDir,
                    'date' => $backupInfo['date'],
                    'size' => $backupInfo['size'],
                    'files' => $backupInfo['files'],
                    'summary' => $backupInfo['summary'],
                ];
            }
        }

        // Sort by date (newest first)
        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }

    /**
     * Get backup information.
     */
    private function getBackupInfo($directory)
    {
        $files = Storage::files($directory);
        $size = 0;

        foreach ($files as $file) {
            $size += Storage::size($file);
        }

        $summary = null;
        if (Storage::exists("{$directory}/backup_summary.json")) {
            $summary = json_decode(Storage::get("{$directory}/backup_summary.json"), true);
        }

        return [
            'date' => Carbon::createFromFormat('Y-m-d_H-i-s', basename($directory))->format('Y-m-d H:i:s'),
            'size' => $this->formatBytes($size),
            'files' => count($files),
            'summary' => $summary,
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
