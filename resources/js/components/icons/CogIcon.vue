<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: string;
}>();
</script>

<template>
    <!-- <svg width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" :class="cn('fill-current', props.class)" >
     <path d="M5 21V14M5 10V3M12 21V12M12 8V3M19 21V16M19 12V3M2 14H8M9 8H15M16 16H22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> -->

    <svg version="1.0" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
	 width="800px" height="800px" viewBox="0 0 64 64" enable-background="new 0 0 64 64" :class="cn('fill-current', props.class)" xml:space="preserve">
    <path d="M60.002,20.026h-7.331l3.517-6.096c1.099-1.899,0.446-4.322-1.449-5.417L40.997,0.58
        c-1.896-1.094-4.318-0.445-5.416,1.454l-3.576,6.19l-3.576-6.19c-1.098-1.899-3.521-2.548-5.42-1.454L9.272,8.514
        c-1.899,1.094-2.548,3.517-1.45,5.417l3.518,6.096H4.008c-2.208,0-3.998,1.79-3.998,4.002v16c0,2.204,1.79,3.994,3.998,3.994h7.332
        l-3.518,6.096c-1.098,1.899-0.449,4.322,1.45,5.417l13.737,7.934c1.899,1.094,4.322,0.445,5.42-1.454l3.576-6.19l3.576,6.19
        c1.098,1.899,3.521,2.548,5.42,1.454l13.737-7.934c1.896-1.095,2.548-3.518,1.449-5.417l-3.517-6.096h7.331
        c2.208,0,3.998-1.79,3.998-3.994v-16C64,21.816,62.21,20.026,60.002,20.026z M31.985,40.067c-4.42,0-8.004-3.584-8.004-8.004
        s3.584-8.004,8.004-8.004s8.004,3.584,8.004,8.004S36.405,40.067,31.985,40.067z" fill="currentColor"/>
    </svg>
</template>
