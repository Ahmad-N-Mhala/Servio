<template>
    <div class="relative group overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800" :class="heightClass">
        <!-- Images -->
        <div 
            v-if="images && images.length > 0"
            class="h-full w-full relative"
        >
            <transition-group name="fade">
                <div 
                    v-for="(img, index) in images" 
                    :key="`${img}-${index}`"
                    v-show="currentIndex === index"
                    class="absolute inset-0 w-full h-full"
                >
                    <img 
                        :src="getImageUrl(img)" 
                        class="w-full h-full transition-transform duration-500 group-hover:scale-110"
                        :class="imageClass"
                        alt="Item image"
                    />
                </div>
            </transition-group>

            <!-- Navigation Buttons (only if > 1 image) -->
            <template v-if="images.length > 1">
                <button 
                    type="button"
                    @click.stop="prev"
                    class="absolute left-2 top-1/2 -translate-y-1/2 p-1.5 rounded-full bg-black/30 backdrop-blur-sm sm:opacity-0 sm:group-hover:opacity-100 opacity-100 hover:bg-black/50 text-white transition-all transform active:scale-95 z-10"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button 
                    type="button"
                    @click.stop="next"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-full bg-black/30 backdrop-blur-sm sm:opacity-0 sm:group-hover:opacity-100 opacity-100 hover:bg-black/50 text-white transition-all transform active:scale-95 z-10"
                >
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>

                <!-- Dots -->
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5">
                    <button
                        type="button"
                        v-for="(_, idx) in images"
                        :key="idx"
                        @click.stop="currentIndex = idx"
                        class="w-1.5 h-1.5 rounded-full transition-all shadow-sm"
                        :class="currentIndex === idx ? 'bg-white w-3' : 'bg-white/50 hover:bg-white/80'"
                    ></button>
                </div>
            </template>
        </div>

        <!-- Fallback -->
        <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
            <svg class="w-1/3 h-1/3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = withDefaults(defineProps<{
    images: string[];
    heightClass?: string;
    imageClass?: string;
}>(), {
    images: () => [],
    heightClass: 'h-48',
    imageClass: 'object-cover'
});

const currentIndex = ref(0);

// Reset index when images change
watch(() => props.images, () => {
    currentIndex.value = 0;
});

const next = () => {
    currentIndex.value = (currentIndex.value + 1) % props.images.length;
};

const prev = () => {
    currentIndex.value = currentIndex.value === 0 ? props.images.length - 1 : currentIndex.value - 1;
};

const getImageUrl = (img: string) => {
    if (!img) return '';
    if (img.startsWith('http') || img.startsWith('blob:') || img.startsWith('data:')) return img;
    if (img.startsWith('/storage')) return img;
    return '/storage/' + img;
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
