<x-filament-panels::page>

    <div>
        {{ $this->form }}
        <div class="flex justify-end mt-6">
            <x-filament::button wire:click="register">
                Register
            </x-filament::button>
        </div>
    </div>

</x-filament-panels::page>
