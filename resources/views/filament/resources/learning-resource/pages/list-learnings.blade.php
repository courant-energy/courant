{{-- @vite('resources/css/filament/app/theme.css') --}}
<x-filament-panels::page>

    <div class="mb-6">
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <x-filament::input.wrapper>
                    <x-filament::input wire:model="search" placeholder="Search learning resources..." type="text" />
                </x-filament::input.wrapper>
                @if ($search)
                    <button wire:click="clearSearch"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        title="Clear search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif
            </div>
            <x-filament::button wire:click="performSearch" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 inline-block" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd" />
                </svg>
                Search
            </x-filament::button>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">

        @forelse ($records as $record)
            <x-filament::card class="dark:border-gray-700">
                <div class="mb-4">
                    @if($record->image_url)
                        <img src="{{ $record->image_url }}" alt="{{ $record->name }}"
                            class="w-full h-32 object-cover rounded-lg">
                    @else
                        <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>
                <h2 class="text font-bold mb-2 dark:text-white">{{ $record->name }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-4">{{ $record->description }}</p>
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
            </x-filament::card>
        @empty
            <div class="col-span-full text-center text-gray-500 dark:text-gray-400 min-h-[200px]">
                <div class="">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-20 w-20 mx-auto mb-2 rounded-full p-4 bg-gray-100 dark:bg-gray-800 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </div>

                @if ($search)
                    No learning resources found matching "{{ $search }}".
                @else
                    No learning resources found.
                @endif
            </div>
        @endforelse

    </div>

</x-filament-panels::page>
