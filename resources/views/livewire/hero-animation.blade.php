<!-- Load Lottie Player -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<div 
  class="relative w-full max-w-7xl h-[500px] flex items-center justify-center overflow-hidden mockups-container"
  x-data="{
    cards: [
      { aspect: 'aspect-[1/1]', image: '/images/SquareImg.webp' },
      { aspect: 'aspect-[16/10]', showLogo: true },
      { aspect: 'aspect-[9/19.5]', image: '/images/MobileMockup.webp' },
      { aspect: 'aspect-[1/1.414]', image: '/images/mockup3.png' },
    ]
  }"
>
  <div class="mockups-track absolute inset-0 flex items-left justify-left md:items-center md:justify-center gap-2 px-4 pointer-events-none scale-[0.7] md:scale-[1.25] max-w-full overflow-visible">
    
    <template x-for="(card, index) in cards" :key="index">
      <div 
        :class="`h-full ${card.aspect} p-3 bg-gray-200 rounded-sm shadow-inner border border-gray-400/50 flex items-center justify-center overflow-hidden flex-shrink-0`"
      >
        <!-- Show Lottie if specified -->
        <template x-if="card.showLogo">
          <div class="w-full h-full flex items-center justify-center" wire:ignore>
            <lottie-player 
              src='{{ asset("logo-ani.json") }}' 
              background="transparent"  
              speed="1"
              style="width: 300px; height: 300px;"  
              autoplay
            ></lottie-player>
          </div>
        </template>

        <!-- Show Image if specified -->
        <template x-if="card.image && !card.showLogo">
          <img :src="card.image" alt="Mockup Image" class="object-cover w-full h-full rounded-sm" />
        </template>
      </div>
    </template>
    
  </div>
</div>
