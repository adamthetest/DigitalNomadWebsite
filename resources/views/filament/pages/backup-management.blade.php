<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Backup Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" style="color: black !important;">Total Backups</p>
                        <p class="text-2xl font-semibold" style="color: black !important;">{{ count($this->getBackups()) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" style="color: black !important;">Latest Backup</p>
                        <p class="text-sm font-semibold" style="color: black !important;">
                            @if(count($this->getBackups()) > 0)
                                {{ $this->getBackups()[0]['date'] }}
                            @else
                                No backups
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" style="color: black !important;">Total Size</p>
                        <p class="text-sm font-semibold" style="color: black !important;">
                            @php
                                $totalSize = 0;
                                foreach($this->getBackups() as $backup) {
                                    $totalSize += str_replace([' KB', ' MB', ' GB', ' B'], '', $backup['size']);
                                }
                            @endphp
                            {{ number_format($totalSize, 2) }} MB
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" style="color: black !important;">Database Tables</p>
                        <p class="text-sm font-semibold" style="color: black !important;">13 Tables</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4" style="color: black !important;">Quick Backup Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <button wire:click="create_backup" class="bg-blue-600 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors" style="color: black !important;">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Full Backup
                    </div>
                </button>

                <button wire:click="create_users_backup" class="bg-green-600 px-4 py-3 rounded-lg hover:bg-green-700 transition-colors" style="color: black !important;">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Backup Users Only
                    </div>
                </button>

                <button wire:click="create_jobs_backup" class="bg-yellow-600 px-4 py-3 rounded-lg hover:bg-yellow-700 transition-colors" style="color: black !important;">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                        </svg>
                        Backup Job Board
                    </div>
                </button>

                <button wire:click="create_security_logs_backup" class="bg-red-600 px-4 py-3 rounded-lg hover:bg-red-700 transition-colors" style="color: black !important;">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Backup Security Logs
                    </div>
                </button>
            </div>
        </div>

        <!-- Backup List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold" style="color: black !important;">All Backups</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: black !important;">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: black !important;">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: black !important;">Files</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: black !important;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->getBackups() as $backup)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: black !important;">
                                    {{ $backup['date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: black !important;">
                                    {{ $backup['size'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: black !important;">
                                    {{ $backup['files'] }} files
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="downloadBackup('{{ $backup['name'] }}')" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        Download
                                    </button>
                                    <button wire:click="restoreFromBackup('{{ $backup['name'] }}')" 
                                            wire:confirm="⚠️ WARNING: This will overwrite ALL existing data with data from this backup. Are you absolutely sure you want to continue?"
                                            class="text-orange-600 hover:text-orange-900 mr-3">
                                        Restore
                                    </button>
                                    <button wire:click="deleteBackup('{{ $backup['name'] }}')" 
                                            wire:confirm="Are you sure you want to delete this backup?"
                                            class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center" style="color: black !important;">
                                    No backups found. Create your first backup using the actions above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cleanup Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4" style="color: black !important;">Maintenance</h3>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm" style="color: black !important;">Clean up backups older than 30 days to free up storage space.</p>
                </div>
                <button wire:click="cleanupOldBackups" 
                        wire:confirm="Are you sure you want to delete backups older than 30 days?"
                        class="bg-red-600 px-4 py-2 rounded-lg hover:bg-red-700 transition-colors" style="color: black !important;">
                    Cleanup Old Backups
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('backup-created', () => {
                // Refresh the page to show new backup
                window.location.reload();
            });
            
            Livewire.on('backup-deleted', () => {
                // Refresh the page to remove deleted backup
                window.location.reload();
            });
            
            Livewire.on('backups-cleaned', () => {
                // Refresh the page to show cleaned backups
                window.location.reload();
            });
            
            Livewire.on('backup-restored', () => {
                // Show success message and refresh
                alert('✅ Backup restored successfully!');
                window.location.reload();
            });
            
            Livewire.on('backup-restore-failed', (error) => {
                // Show detailed error message
                console.error('Restore failed:', error);
                alert('❌ Restore failed: ' + error);
            });
        });
    </script>
</x-filament-panels::page>