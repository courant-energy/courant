@vite('resources/css/filament/app/theme.css')
<x-filament-panels::page>

    <div class="grid md:grid-cols-2 gap-4">


        @forelse ($records as $record)
            <div class="bg-white shadow rounded-lg p-4 col-span-1">
                <div class="mb-4">
                    <img src="{{ $record->image_url }}" alt="{{ $record->name }}"
                        class="w-full h-32 object-cover rounded-lg">
                </div>
                <h2 class="text font-bold mb-2">{{ $record->name }}</h2>
                <p class="text-gray-500 mb-4">{{ $record->description }}</p>
                <div class="mb-4 flex">
                    @switch($record->type)
                        @case('article')
                            <x-filament::badge color="primary">Article</x-filament::badge>
                        @break

                        @case('tutorial')
                            <x-filament::badge color="secondary">Tutorial</x-filament::badge>
                        @break

                        @case('guide')
                            <x-filament::badge color="success">Guide</x-filament::badge>
                        @break

                        @case('tip')
                            <x-filament::badge color="warning">Quick Tip</x-filament::badge>
                        @break

                        @case('infographic')
                            <x-filament::badge color="info">Infographic</x-filament::badge>
                        @break

                        @case('video')
                            <x-filament::badge color="danger">Video</x-filament::badge>
                        @break

                        @default
                            <x-filament::badge color="gray">{{ Str::title($record->type) }}</x-filament::badge>
                    @endswitch
                </div>
                <x-filament::button tag="a" href="{{ $this->showRoute($record->slug) }}">
                    Learn More </x-filament::button>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 min-h-[200px]">
                <div class="">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-20 mx-auto mb-2 rounded-full p-4 bg-gray-100 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </div>

                No learning resources found.
            </div>
        @endforelse

    </div>

</x-filament-panels::page>
