<x-filament-panels::page>
    @if (auth()->user()->hasUnconfiguredSocket)
        <x-filament::section icon="heroicon-o-bell-alert" icon-color="warning" collapsible>
            <x-slot name="heading">
                Action Required: Connect Your Appliances!
            </x-slot>
            <div>
                <div class="">
                    <p class="text-gray-700 dark:text-gray-300">
                        To unlock all the smart features, you need to tell the system about your appliances and which
                        socket
                        they are plugged into.
                    </p>

                    <br class="my-2">

                    <span class="dark:text-gray-200">Here's what to do:</span>

                    <ul class="mt-3 space-y-3">
                        <li class="flex items-start">
                            <span class="mr-2 text-xl">➕</span>
                            <div>
                                <span class="font-medium text-primary-600 dark:text-primary-400">Create Appliance Profile:</span>
                                <span class="text-gray-700 dark:text-gray-300">Define the type and settings for each appliance you want to
                                    control.</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-2 text-xl">🔌</span>
                            <div>
                                <span class="font-medium text-primary-600 dark:text-primary-400">Assign to Socket:</span>
                                <span class="text-gray-700 dark:text-gray-300">Link your created appliance profiles to the corresponding
                                    smart sockets.</span>
                            </div>
                        </li>
                    </ul>
                </div>




                <div class="space-x-2">
                    <x-filament::button class="mt-4" color="gray" icon="heroicon-o-light-bulb" tag="a"
                        href="{{ route('filament.app.resources.sockets.index') }}">
                        Configure Socket
                    </x-filament::button>

                    <x-filament::button class="mt-4" icon="heroicon-o-identification" tag="a"
                        href="{{ route('filament.app.resources.profiles.index') }}">
                        Create Appliance Profile
                    </x-filament::button>

                </div>
            </div>
        </x-filament::section>
    @endif

    @if (!auth()->user()->iotDevices()->exists())
        <x-filament::section icon="heroicon-o-information-circle" icon-color="gray" collapsible>
            <x-slot name="heading">
                Optimize Your Energy Usage.
            </x-slot>

            <div class="dark:text-gray-300">
                Link your IoT Device to gain insights and improve your operational efficiency.
            </div>

            <div class="space-x-2">
                <x-filament::button class="mt-4" icon="heroicon-o-plus" tag="a"
                    href="{{ route('filament.app.resources.iot-devices.index') }}">
                    Connect Devices Now
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
