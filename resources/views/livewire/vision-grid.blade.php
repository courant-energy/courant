

<div 
  class="grid grid-cols-1 md:grid-cols-3 divide-0 md:divide-x divide-gray-200 bg-white"
  x-data="{ 
    cards: [
        {
                title: 'Global Impact', 
                description: 'Creating solutions that scale across communities and continents.',
                icon: `<svg class='w-5 h-5 text-gray-500' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'></path>
                        </svg>`
        },
        {
                title: 'Innovation First', 
                description: 'Pioneering technologies that make energy management intuitive.',
                icon: `<svg class='w-5 h-5 text-gray-500' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'></path>
                        </svg>`
        },
        {
                title: 'Sustainable Future', 
                description: 'Building tomorrow\'s infrastructure for carbon-neutral living.',
                icon: `<svg class='w-5 h-5 text-gray-500' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h.5A2.5 2.5 0 0020 5.5v-1.5'></path>
                        </svg>`
        },
        ]
  }"
>
<template x-for="card in cards" :key="card.title">
    <div class="flex-col px-0 py-6 md:p-10 border-0 md:border-t md:border-b border-gray-200">
      <div class="">
        <div class="flex space-x-2">
            <div class="aspect-square flex items-center justify-center text-amber-500" x-html="card.icon"></div>
            <h3 class="text-left text-xl font-semibold text-gray-900" x-text="card.title"></h3>
        </div>
        <p class="text-left w-full md:w-2/3 text-base text-gray-500" x-text="card.description"></p>
      </div>
    </div>
</template>
</div>