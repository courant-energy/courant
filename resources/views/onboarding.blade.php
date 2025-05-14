<div x-data="{ activeTab: 'welcome' }">
    <x-filament::tabs label="Onboarding" class="flex flex-wrap">
        <x-filament::tabs.item label="Welcome" alpine-active="activeTab === 'welcome'" x-on:click="activeTab = 'welcome'" 
            class="mb-2 mr-2">
            Welcome
        </x-filament::tabs.item>

        <x-filament::tabs.item label="1. Link IoT Device" alpine-active="activeTab === 'link-device'"
            x-on:click="activeTab = 'link-device'" class="mb-2 mr-2">
            Link IoT Device
        </x-filament::tabs.item>

        <x-filament::tabs.item label="2. Configure Sockets" alpine-active="activeTab === 'configure-sockets'"
            x-on:click="activeTab = 'configure-sockets'" class="mb-2 mr-2">
            Configure Sockets
        </x-filament::tabs.item>

        <x-filament::tabs.item label="Explore" alpine-active="activeTab === 'explore'"
            x-on:click="activeTab = 'explore'" class="mb-2 mr-2">
            Explore
        </x-filament::tabs.item>

        <x-filament::tabs.item label="Need Help?" alpine-active="activeTab === 'help'" 
            x-on:click="activeTab = 'help'" class="mb-2">
            Need Help?
        </x-filament::tabs.item>
    </x-filament::tabs>
    <div x-show="activeTab === 'welcome'" class="px-4 py-4">
        <h1 class="text-2xl font-bold mb-4 dark:text-white">Welcome to COURANT!</h1>
        <p class="text-gray-700 dark:text-gray-300">Powering your efficiency with intelligent power management. Let's get started!</p>
    </div>

    <div x-show="activeTab === 'link-device'" class="px-4 py-4">
        <h2 class="text-xl font-bold mb-2 dark:text-white">Connect Your Equipment</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">Start collecting real-time data for analysis.</p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300">
            <li>Go to <strong class="font-semibold">IoT Devices</strong>.</li>
            <li>Follow the device-specific instructions.</li>
        </ul>
        <x-filament::link href="{{ route('filament.app.resources.iot-devices.index') }}" >
            Connect Device
        </x-filament::link>
    </div>

    <div x-show="activeTab === 'configure-sockets'" class="px-4 py-4">
        <h2 class="text-xl font-bold mb-2 dark:text-white">Organize Your Power Consumption</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">Assign devices to specific areas or machines.</p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300">
            <li>Go to <strong class="font-semibold">Sockets</strong>.</li>
            <li>Create sockets and link your devices.</li>
        </ul>
        <x-filament::link href="{{ route('filament.app.resources.sockets.index') }}" >
            Configure Sockets
        </x-filament::link>
    </div>

    <div x-show="activeTab === 'explore'" class="px-4 py-4">
        <h2 class="text-xl font-bold mb-2 dark:text-white">Your Dashboard Overview</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">Key information at a glance:</p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300">
            <li>Connected Devices</li>
            <li>Energy Consumption</li>
            <li>Recent Alerts</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-300">Continue to <strong class="font-semibold">Power Monitoring</strong> and <strong
                class="font-semibold">Reports</strong> for deeper insights.</p>
    </div>

    <div x-show="activeTab === 'help'" class="px-4 py-4">
        <h2 class="text-xl font-bold mb-2 dark:text-white">We're Here to Support You</h2>
        <p class="text-gray-700 dark:text-gray-300">Visit our <x-filament::link href="{{ route('filament.app.pages.help') }}" size="xs">Help
                Center</x-filament::link> for FAQs and guides.
        </p>
    </div>
</div>
