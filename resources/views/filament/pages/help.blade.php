<x-filament-panels::page>

    {{ $this->form }}

    <div class="flex justify-end">
        <x-filament::button wire:click="submit" class="mt-4" color="primary">
            Submit
        </x-filament::button>
    </div>

</x-filament-panels::page>
