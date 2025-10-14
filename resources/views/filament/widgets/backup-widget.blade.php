<div class="fi-wi-widget">
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
        <div class="fi-section-header-actions flex items-center gap-x-3">
            <div class="fi-section-header flex items-center gap-x-3">
                <h3 class="fi-section-title text-base font-semibold leading-6 text-black !important">
                    📦 Backup Management
                </h3>
            </div>
        </div>
        
        <div class="fi-section-content p-6" style="color: black !important;">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Backup Stats -->
            <div class="bg-white rounded-lg shadow p-6" style="color: black !important;">
                <h3 class="text-lg font-semibold mb-4" style="color: black !important;">Backup Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span style="color: black !important;">Total Backups:</span>
                        <span class="font-semibold" style="color: black !important;">{{ $backupStats['total'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color: black !important;">Total Size:</span>
                        <span class="font-semibold" style="color: black !important;">{{ $backupStats['total_size'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color: black !important;">Oldest Backup:</span>
                        <span class="font-semibold" style="color: black !important;">{{ $backupStats['oldest'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color: black !important;">Newest Backup:</span>
                        <span class="font-semibold" style="color: black !important;">{{ $backupStats['newest'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Database Stats -->
            <div class="bg-white rounded-lg shadow p-6" style="color: black !important;">
                <h3 class="text-lg font-semibold mb-4" style="color: black !important;">Database Statistics</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span style="color: black !important;">Total Records:</span>
                        <span class="font-semibold" style="color: black !important;">{{ number_format($databaseStats['total_records']) }}</span>
                    </div>
                    <div class="text-sm mt-3" style="color: black !important;">
                        <strong>Top Tables:</strong>
                    </div>
                    @foreach(array_slice($databaseStats['tables'], 0, 5, true) as $table => $count)
                        <div class="flex justify-between text-sm">
                            <span style="color: black !important;">{{ ucfirst(str_replace('_', ' ', $table)) }}:</span>
                            <span class="font-medium" style="color: black !important;">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6" style="color: black !important;">
                <h3 class="text-lg font-semibold mb-4" style="color: black !important;">Quick Actions</h3>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('filament.admin.backup.create') }}" class="inline">
                        @csrf
                        <input type="hidden" name="type" value="all">
                        <input type="hidden" name="format" value="json">
                        <button type="submit" class="w-full bg-blue-600 px-4 py-2 rounded-md hover:bg-blue-700 transition-colors" style="color: black !important;">
                            📦 Create Full Backup
                        </button>
                    </form>
                    
                    <a href="{{ route('filament.admin.pages.backup-management') }}" class="block w-full text-center bg-gray-600 px-4 py-2 rounded-md hover:bg-gray-700 transition-colors" style="color: black !important;">
                        📋 View All Backups
                    </a>
                    
                    <form method="POST" action="{{ route('filament.admin.backup.cleanup') }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to delete backups older than 30 days?')" 
                                class="w-full bg-red-600 px-4 py-2 rounded-md hover:bg-red-700 transition-colors" style="color: black !important;">
                            🗑️ Cleanup Old Backups
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Backups -->
        @if(count($recentBackups) > 0)
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-4" style="color: black !important;">Recent Backups</h3>
                <div class="bg-white rounded-lg shadow overflow-hidden">
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
                            @foreach($recentBackups as $backup)
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
                                        <a href="{{ route('filament.admin.backup.download', $backup['name']) }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Download</a>
                                        <form method="POST" action="{{ route('filament.admin.backup.delete', $backup['name']) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this backup?')" 
                                                    class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>
