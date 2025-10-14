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
            // Core tables first (no dependencies)
            'countries',
            'users',
            'cities',
            'companies',
            'newsletter_subscribers',
            'affiliate_links',
            'visa_rules',
            'jobs',
            'favorites',
            'job_user_interactions',
            'security_logs',
            'banned_ips',
            // Dependent tables (after their dependencies)
            'neighborhoods', // depends on cities
            'coworking_spaces', // depends on cities and neighborhoods
            'cost_items', // depends on cities
            'articles', // depends on cities
            'deals', // depends on cities
            // Skip temporary tables
            // 'cache', 'sessions', 'password_reset_tokens'
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
        
        // Get current table columns
        $currentColumns = $this->getTableColumns($table);
        
        // Clear existing data
        DB::table($table)->truncate();
        
        // Insert restored data
        foreach ($data as $record) {
            // Remove foreign key data that was added during backup
            $cleanRecord = $this->cleanRecord($table, $record);
            
            // Only include columns that exist in current schema
            $filteredRecord = array_intersect_key($cleanRecord, array_flip($currentColumns));
            
            // Handle special cases for required fields
            $filteredRecord = $this->handleRequiredFields($table, $filteredRecord);
            
            // Skip if record should not be restored
            if ($filteredRecord === null) {
                continue;
            }
            
            DB::table($table)->insert($filteredRecord);
        }
        
        $this->info("✅ {$table} restored: " . count($data) . " records");
    }
    
    private function getTableColumns($table)
    {
        try {
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            return $columns;
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not get columns for table {$table}: " . $e->getMessage());
            return [];
        }
    }
    
    private function cleanRecord($table, $record)
    {
        // Remove foreign key data that was added during backup
        $foreignKeys = [
            'cities' => ['country_name', 'country_code'],
            'neighborhoods' => ['city_name'],
            'articles' => ['city_name', 'country_name'],
            'favorites' => ['user_name', 'user_email'],
            'coworking_spaces' => ['city_name'],
            'cost_items' => ['city_name'],
            'visa_rules' => ['country_name'],
            'jobs' => ['company_name'],
            'job_user_interactions' => ['user_name', 'user_email', 'job_title', 'company_name'],
            'security_logs' => ['user_name', 'user_email'],
            'banned_ips' => ['banned_by_name', 'banned_by_email'],
            'sessions' => ['user_name', 'user_email']
        ];
        
        if (isset($foreignKeys[$table])) {
            foreach ($foreignKeys[$table] as $key) {
                unset($record[$key]);
            }
        }
        
        return $record;
    }
    
    private function handleRequiredFields($table, $record)
    {
        // Handle special cases for required fields that might not be in backup
        switch ($table) {
            case 'users':
                // Decrypt password if it's encrypted
                if (isset($record['password']) && !empty($record['password'])) {
                    try {
                        // Check if password is encrypted (starts with eyJpdiI6)
                        if (str_starts_with($record['password'], 'eyJpdiI6')) {
                            $record['password'] = decrypt($record['password']);
                        }
                        // Password is already decrypted, keep as is
                    } catch (\Exception $e) {
                        // If decryption fails, set a default password
                        $this->warn("Failed to decrypt password for user {$record['email']}, setting default password");
                        $record['password'] = bcrypt('password_reset_required');
                    }
                } else {
                    // If password is missing, set a default (user will need to reset)
                    $record['password'] = bcrypt('password_reset_required');
                }
                
                // Decrypt remember_token if it's encrypted
                if (isset($record['remember_token']) && !empty($record['remember_token'])) {
                    try {
                        // Check if token is encrypted (starts with eyJpdiI6)
                        if (str_starts_with($record['remember_token'], 'eyJpdiI6')) {
                            $record['remember_token'] = decrypt($record['remember_token']);
                        }
                        // Token is already decrypted, keep as is
                    } catch (\Exception $e) {
                        // If decryption fails, clear the token
                        $this->warn("Failed to decrypt remember_token for user {$record['email']}, clearing token");
                        $record['remember_token'] = null;
                    }
                }
                break;
                
            case 'sessions':
                // Skip sessions as they're temporary and shouldn't be restored
                return null;
                
            case 'password_reset_tokens':
                // Skip password reset tokens as they're temporary
                return null;
                
            case 'cache':
                // Skip cache as it's temporary
                return null;
        }
        
        return $record;
    }
}