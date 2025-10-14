<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📦 Backup Management
        </x-slot>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Backup Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Backup Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Backups:</span>
                        <span class="font-semibold">{{ $backupStats['total'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Size:</span>
                        <span class="font-semibold">{{ $backupStats['total_size'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Oldest Backup:</span>
                        <span class="font-semibold">{{ $backupStats['oldest'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Newest Backup:</span>
                        <span class="font-semibold">{{ $backupStats['newest'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Database Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Statistics</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Records:</span>
                        <span class="font-semibold">{{ number_format($databaseStats['total_records']) }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mt-3">
                        <strong>Top Tables:</strong>
                    </div>
                    @foreach(array_slice($databaseStats['tables'], 0, 5, true) as $table => $count)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $table)) }}:</span>
                            <span class="font-medium">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('filament.admin.backup.create') }}" class="inline">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            📦 Create Full Backup
                        </button>
                    </form>
                    
                    <a href="{{ route('filament.admin.backup.list') }}" class="block w-full text-center bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                        📋 View All Backups
                    </a>
                    
                    <form method="POST" action="{{ route('filament.admin.backup.cleanup') }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to delete backups older than 30 days?')" 
                                class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                            🗑️ Cleanup Old Backups
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Backups -->
        @if(count($recentBackups) > 0)
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Backups</h3>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Files</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentBackups as $backup)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $backup['date'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $backup['size'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
    </x-filament::section>
</x-filament-widgets::widget>
