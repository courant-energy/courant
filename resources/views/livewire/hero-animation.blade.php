<!-- Load Lottie Player -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<div
class="relative w-full max-w-7xl h-[400px] md:h-[500px] flex items-center justify-center overflow-hidden mockups-container"
x-data="{
 cards: [
 { aspect: 'aspect-[1/1]', image: '/images/SquareImg.webp' },
 { aspect: 'aspect-[16/10]', images: ['/images/MainMockup1.webp','/images/MainMockup2.webp','/images/MainMockup3.webp'], showLogo: true },
 { aspect: 'aspect-[9/19.5]', image: '/images/MobileMockup.webp' },
 { aspect: 'aspect-[1/1]', image: '/images/DesktopMockup.webp' },
 ]
 }"
>
<div class="mockups-track absolute inset-0 flex items-left justify-left md:items-center md:justify-center gap-2 px-4 pointer-events-none scale-[0.7] md:scale-[1.25] max-w-full overflow-visible">
<template x-for="(card, index) in cards" :key="index">
<div
:class="`h-full ${card.aspect} p-3 bg-gray-200 rounded-sm shadow-inner border border-gray-400/50 flex items-center justify-center overflow-hidden flex-shrink-0 relative ${index === 0 ? 'hidden md:flex' : ''}`"
>
<!-- Show multiple images stacked if specified -->
<template x-if="card.images">
<div class="absolute inset-3 overflow-hidden bg-black">
<template x-for="(img, imgIndex) in card.images" :key="imgIndex">
<img 
:src="img" 
alt="Mockup Image" 
class="absolute w-full h-full main-mockups" 
:x-ref="`mockup-${index}-${imgIndex}`"
:style="`
transform: translateZ(${imgIndex * 100}px) scale(0.7); 
z-index: ${imgIndex + 1};
top: ${imgIndex * 20}px;
left: ${imgIndex * 100}px;
`"
/>
</template>
<!-- Gradient overlay above image -->
<div class="absolute bottom-0 left-0 w-full h-[500px] bg-gradient-to-t from-black to-transparent z-10"></div>
</div>
</template>

<!-- Show Lottie if specified (will appear in front) -->
<template x-if="card.showLogo">
<div class="relative z-50 w-full h-full flex items-center justify-center" wire:ignore>
<lottie-player
src='{{ asset("logo-ani.json") }}'
background="transparent"
speed="1"
style="width: 300px; height: 300px;"
autoplay
></lottie-player>
</div>
</template>

<!-- Show single Image if specified -->
<template x-if="card.image && !card.showLogo && !card.images">
<img :src="card.image" alt="Mockup Image" class="object-cover w-full h-full rounded-sm" />
</template>
</div>
</template>
</div>
</div>