<div 
  class="grid grid-cols-1 md:grid-cols-3 divide-y divide-x divide-gray-200 bg-white"
  x-data="{ 
    cards: [
      {
        title: 'Real-time Monitoring', 
        description: 'Track energy consumption patterns in real-time and make informed decisions.',
        icon: `<svg class='w-10 h-10 md:w-6 md:h-6 text-amber-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 10V3L4 14h7v7l9-11h-7z'></path></svg>`
      },
      {
        title: 'Cost Optimization',
        description: 'Reduce energy bills by up to 30% with our intelligent optimization algorithms.',
        icon: 
            `<svg xmlns='http://www.w3.org/2000/svg' class='w-10 h-10 md:w-6 md:h-6 text-amber-500' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>
            <path stroke-linecap='round' stroke-linejoin='round' d='M12 3a9 9 0 1 0 9 9' />
            <path stroke-linecap='round' stroke-linejoin='round' d='M12 12l4-4' />
            <circle cx='12' cy='12' r='1' fill='currentColor' />
            </svg>`
      },
      {
        title: 'Sustainability Impact',
        description: 'Track and report on your carbon footprint reduction and environmental impact.',
        icon: 
            `<svg xmlns='http://www.w3.org/2000/svg' class='w-10 h-10 md:w-6 md:h-6 text-amber-500' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'>
            <path stroke-linecap='round' stroke-linejoin='round' d='M5 13l4 4L19 7' />
            </svg>`

      },
    ]
  }"
>
  <template x-for="card in cards" :key="card.title">
    <div 
      class="feature-card flex items-center space-x-4 p-10 border-0 md:border-t md:border-b border-gray-200"
    >
      <div class="w-20 h-20 md:w-12 md:h-12 aspect-square rounded-sm flex items-center justify-center bg-gray-100" x-html="card.icon"></div>
      <div class="feature-card-text">
        <h3 class="text-xl feature-card-title font-semibold text-gray-900" x-text="card.title"></h3>
        <p class="feature-card-description text-base text-gray-500 opacity-1 md:opacity-0" x-text="card.description"></p>
      </div>
    </div>
  </template>
</div>
