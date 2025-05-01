@vite('resources/css/filament/app/theme.css')
<x-filament-panels::page>
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Header with title and metadata -->
        <div class="mb-6 border-b border-gray-200">
            <div class="flex pb-2 items-center text-gray-500 text-sm space-x-4">
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
        <div class="w-full aspect-video bg-gray-100 overflow-hidden">
            <img src="{{ $record->image_url }}" alt="{{ $record->name }}" class="w-full h-full object-cover">
        </div>
        @endif
        
        <!-- Content section -->
        <div class="p-6">
            <div class="prose max-w-none">
                {!! $record->content !!}
            </div>
        </div>
        
        <!-- Footer with additional information -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <span>Last updated: {{ $record->updated_at->format('F d, Y') }}</span>
                </div>
              
            </div>
        </div>
    </div>
</x-filament-panels::page>