@extends('layouts.app')

@section('title', 'Backup Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">📦 Backup Management</h1>
        <p class="text-gray-600">Manage and download your website backups</p>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-4">
            <form method="POST" action="{{ route('filament.admin.backup.create') }}" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    📦 Create Full Backup
                </button>
            </form>
            
            <form method="POST" action="{{ route('filament.admin.backup.cleanup') }}" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Are you sure you want to delete all backups except the last 5?')" 
                        class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors">
                    🗑️ Cleanup Old Backups
                </button>
            </form>
        </div>
    </div>

    <!-- Backups List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Available Backups</h2>
        </div>
        
        @if(count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Files</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Summary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($backups as $backup)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $backup['date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $backup['size'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $backup['files'] }} files
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if($backup['summary'])
                                        <div class="text-xs">
                                            @if(isset($backup['summary']['tables_backed_up']))
                                                @foreach($backup['summary']['tables_backed_up'] as $table)
                                                    <span class="inline-block bg-gray-100 rounded px-2 py-1 mr-1 mb-1">
                                                        {{ ucfirst(str_replace('_', ' ', $table)) }}
                                                    </span>
                                                @endforeach
                                            @else
                                                @foreach($backup['summary'] as $table => $count)
                                                    <span class="inline-block bg-gray-100 rounded px-2 py-1 mr-1 mb-1">
                                                        {{ ucfirst(str_replace('_', ' ', $table)) }}: {{ is_array($count) ? json_encode($count) : $count }}
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">No summary available</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('filament.admin.backup.download', $backup['directory']) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            📥 Download
                                        </a>
                                        <form method="POST" action="{{ route('filament.admin.backup.delete', $backup['directory']) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('Are you sure you want to delete this backup?')" 
                                                    class="text-red-600 hover:text-red-900">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <div class="text-gray-400 text-6xl mb-4">📦</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No backups found</h3>
                <p class="text-gray-500 mb-4">Create your first backup to get started.</p>
                <form method="POST" action="{{ route('filament.admin.backup.create') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Create Backup
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Instructions -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">💡 Backup Instructions</h3>
        <ul class="text-blue-800 space-y-1">
            <li>• <strong>Full Backup:</strong> Creates a complete backup of all your data</li>
            <li>• <strong>Download:</strong> Downloads the entire backup as a ZIP file</li>
            <li>• <strong>Cleanup:</strong> Removes backups older than 30 days to save space</li>
            <li>• <strong>Delete:</strong> Permanently removes individual backups</li>
        </ul>
    </div>
</div>

@if(session('success'))
    <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('error') }}
    </div>
@endif

<script>
// Auto-hide success/error messages after 5 seconds
setTimeout(function() {
    const messages = document.querySelectorAll('.fixed.top-4.right-4');
    messages.forEach(message => message.remove());
}, 5000);
</script>
@endsection
