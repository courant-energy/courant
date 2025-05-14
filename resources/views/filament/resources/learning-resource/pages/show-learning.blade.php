@vite('resources/css/filament/app/theme.css')
<x-filament-panels::page>
    <x-filament::card class="dark:border-gray-700">
        <!-- Header with title and metadata -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex pb-2 items-center text-gray-500 dark:text-gray-400 text-sm space-x-4">
                <x-filament::badge>
                    {{ ucfirst($record->type) }}
                </x-filament::badge>
                <span>{{ $record->created_at->format('F d, Y') }}</span>
                <x-filament::badge :color="$record->status === 'published' ? 'success' : 'warning'">
                    {{ ucfirst($record->status) }}
                </x-filament::badge>
            </div>
        </div>
        
        <!-- Cover image if available -->
        @if($record->cover_image)
        <div class="w-full aspect-square bg-gray-100 dark:bg-gray-800 overflow-hidden">
            <img src="{{ $record->image_url }}" alt="{{ $record->name }}" class="w-full h-full object-cover">
        </div>
        @endif
        
        <!-- Content section -->
        <div class="p-6">
            <div class="prose dark:prose-invert max-w-none">
                {!! $record->content !!}
            </div>
        </div>
        
        <!-- Footer with additional information -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span>Last updated: {{ $record->updated_at->format('F d, Y') }}</span>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament-panels::page>