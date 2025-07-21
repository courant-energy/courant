<div>
    <div 
        class="flex-col md:grid md:grid-cols-2 md:grid-rows-2 -mx-9"
        x-data="{ 
            cards: [
            {
                number: '30%', 
                subtext: 'Average Energy Savings',
                img: 'images/SquareImg.webp'
            },
            {
                number: '10k+', 
                subtext: 'Active Users',
                img: 'images/DashboardUsersWhite.webp'
            },
            {
                number: '24/7', 
                subtext: 'Monitoring',
                img: ''
            },
            {
                number: '100%', 
                subtext: 'Renewable Focus',
                img: ''
            },
            ]
        }"
    >
    <template x-for="(card, index) in cards" :key="card.number">
        <div
            class="relative flex flex-col items-start pt-10 overflow-hidden max-h-[500px] md:max-h-[700px]"
            :class="{
                'border-b md:border-r border-gray-200': index === 0,
                'border-b border-gray-200': index === 1,
                'border-b md:border-b-0 md:border-r border-gray-200': index === 2,
                '': index === 3
            }"
        >
            <div
                class="ml-5"

            >
                <h3 class="block text-6xl font-bold mb-3 count-number" :data-target="card.number"></h3>
                <p class="text-base z-10 mb-8" x-text="card.subtext"></p>
            </div>
            <!-- Image wrapper directly below <p> -->
            <div class="relative w-full aspect-[1/1] overflow-hidden">
                <img 
                    class="absolute top-0 left-0 w-full h-auto z-0 mission-img"
                    :src="card.img"
                />
                <!-- Gradient overlay above image -->
                <div class="absolute bottom-0 left-0 w-full h-[400px] bg-gradient-to-t from-white to-transparent z-10"></div>
            </div>
        </div>
    </template>
    
    
    </div>
</div>
