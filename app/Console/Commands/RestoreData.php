<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RestoreData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:data {backup_path : Path to backup directory} {--table=all : Specific table to restore} {--confirm : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore website data from backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupPath = $this->argument('backup_path');
        $table = $this->option('table');
        $confirm = $this->option('confirm');
        
        $this->info("Starting data restore...");
        $this->info("Backup path: {$backupPath}");
        $this->info("Table: {$table}");
        
        // Check if backup directory exists
        if (!Storage::exists($backupPath)) {
            $this->error("❌ Backup directory not found: {$backupPath}");
            return 1;
        }
        
        // Show backup summary if available
        if (Storage::exists("{$backupPath}/backup_summary.json")) {
            $summary = json_decode(Storage::get("{$backupPath}/backup_summary.json"), true);
            $this->info("Backup Date: " . $summary['backup_date']);
            $this->info("Total Records: " . $summary['total_records']);
            $this->info("Tables: " . implode(', ', $summary['tables_backed_up']));
        }
        
        // Confirmation prompt
        if (!$confirm) {
            if (!$this->confirm('⚠️  This will overwrite existing data. Are you sure you want to continue?')) {
                $this->info('Restore cancelled.');
                return 0;
            }
        }
        
        try {
            if ($table === 'all') {
                $this->restoreAll($backupPath);
            } else {
                $this->restoreTable($backupPath, $table);
            }
            
            $this->info("✅ Restore completed successfully!");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Restore failed: " . $e->getMessage());
            return 1;
        }
    }
    
    private function restoreAll($backupPath)
    {
        $this->info("Restoring all data...");
        
        $tables = [
            'users',
            'cities', 
            'articles',
            'deals',
            'newsletter_subscribers',
            'favorites',
            'coworking_spaces',
            'cost_items',
            'visa_rules',
            'affiliate_links'
        ];
        
        foreach ($tables as $table) {
            if (Storage::exists("{$backupPath}/{$table}.json")) {
                $this->restoreTable($backupPath, $table);
            }
        }
    }
    
    private function restoreTable($backupPath, $table)
    {
        $this->info("Restoring {$table}...");
        
        $jsonFile = "{$backupPath}/{$table}.json";
        
        if (!Storage::exists($jsonFile)) {
            $this->warn("⚠️  No backup file found for table: {$table}");
            return;
        }
        
        $data = json_decode(Storage::get($jsonFile), true);
        
        if (empty($data)) {
            $this->warn("⚠️  No data found in backup for table: {$table}");
            return;
        }
        
        // Clear existing data
        DB::table($table)->truncate();
        
        // Insert restored data
        foreach ($data as $record) {
            // Remove foreign key data that was added during backup
            $cleanRecord = $this->cleanRecord($table, $record);
            
            DB::table($table)->insert($cleanRecord);
        }
        
        $this->info("✅ {$table} restored: " . count($data) . " records");
    }
    
    private function cleanRecord($table, $record)
    {
        // Remove foreign key data that was added during backup
        $foreignKeys = [
            'cities' => ['country_name', 'country_code'],
            'articles' => ['city_name', 'country_name'],
            'favorites' => ['user_name', 'user_email'],
            'coworking_spaces' => ['city_name'],
            'cost_items' => ['city_name'],
            'visa_rules' => ['country_name']
        ];
        
        if (isset($foreignKeys[$table])) {
            foreach ($foreignKeys[$table] as $key) {
                unset($record[$key]);
            }
        }
        
        return $record;
    }
}