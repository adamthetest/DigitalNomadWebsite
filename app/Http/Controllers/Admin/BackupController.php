<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

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
                '--format' => $format
            ]);
            
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully!',
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download a backup file.
     */
    public function download($backupDir, $filename)
    {
        $filePath = "backups/{$backupDir}/{$filename}";
        
        if (!Storage::exists($filePath)) {
            abort(404, 'Backup file not found');
        }
        
        return Storage::download($filePath);
    }
    
    /**
     * Delete a backup.
     */
    public function destroy($backupDir)
    {
        try {
            Storage::deleteDirectory("backups/{$backupDir}");
            
            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage()
            ], 500);
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
                '--confirm' => true
            ]);
            
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Data restored successfully!',
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
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
                    'summary' => $backupInfo['summary']
                ];
            }
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
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
            'summary' => $summary
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
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}