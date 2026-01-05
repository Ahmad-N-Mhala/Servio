<script setup lang="ts">
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close', 'select']);

const searchQuery = ref('');
const images = ref<any[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

// Curated restaurant-related terms for default suggestions
const defaultTerms = ['restaurant', 'food', 'coffee', 'cafe', 'dining', 'pizza', 'burger', 'sushi'];

const searchUnsplash = async (query: string) => {
    loading.value = true;
    error.value = null;
    
    // access key from environment variable
    const accessKey = import.meta.env.VITE_UNSPLASH_ACCESS_KEY;

    if (!accessKey) {
        error.value = 'Unsplash Access Key is missing. Please add VITE_UNSPLASH_ACCESS_KEY to your .env file.';
        loading.value = false;
        return;
    }

    try {
        const response = await fetch(`https://api.unsplash.com/search/photos?query=${encodeURIComponent(query)}&per_page=12&orientation=landscape`, {
            headers: {
                'Authorization': `Client-ID ${accessKey}`
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch images');
        }

        const data = await response.json();
        images.value = data.results;
    } catch (err) {
        error.value = 'Error fetching images. Please try again.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

// Load default suggestions when modal opens
watch(() => props.show, (newVal) => {
    if (newVal && images.value.length === 0) {
        searchUnsplash('restaurant interior');
    }
});

const selectImage = (image: any) => {
    emit('select', image);
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')" title="Select Image from Unsplash" size="4xl">
        <div class="space-y-6 min-h-[500px] flex flex-col">
            <!-- Search Bar -->
            <div class="flex gap-4">
                <div class="flex-1">
                    <Input 
                        v-model="searchQuery" 
                        placeholder="Search for photos (e.g. coffee, interior, sushi...)" 
                        @keyup.enter="searchUnsplash(searchQuery)"
                    />
                </div>
                <Button @click="searchUnsplash(searchQuery)" :loading="loading">Search</Button>
            </div>

            <!-- Suggestions Tags -->
            <div class="flex flex-wrap gap-2">
                <button 
                    v-for="term in defaultTerms" 
                    :key="term"
                    @click="searchQuery = term; searchUnsplash(term)"
                    class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-full text-xs text-gray-700 transition-colors"
                >
                    {{ term }}
                </button>
            </div>

            <!-- Error State -->
            <div v-if="error" class="p-4 bg-red-50 text-red-600 rounded-xl text-sm">
                {{ error }}
            </div>

            <!-- Loading Skeleton -->
            <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="i in 8" :key="i" class="aspect-[4/3] bg-gray-200 rounded-xl animate-pulse"></div>
            </div>

            <!-- Results Grid -->
            <div v-else-if="images.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 overflow-y-auto max-h-[60vh] pr-2">
                <div 
                    v-for="image in images" 
                    :key="image.id" 
                    class="group relative aspect-[4/3] rounded-xl overflow-hidden cursor-pointer shadow-sm hover:shadow-md transition-all"
                    @click="selectImage(image)"
                >
                    <img 
                        :src="image.urls.small" 
                        :alt="image.alt_description" 
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                    >
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                         <span class="opacity-0 group-hover:opacity-100 bg-white/90 text-gray-900 px-3 py-1.5 rounded-full text-xs font-bold transform translate-y-2 group-hover:translate-y-0 transition-all">
                            Use Image
                        </span>
                    </div>
                    <!-- Photographer Credit -->
                    <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/60 to-transparent text-[10px] text-white opacity-0 group-hover:opacity-100 transition-opacity">
                        by {{ image.user.name }}
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!loading && !error" class="flex-1 flex flex-col items-center justify-center text-gray-400 py-12">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p>Search for images to get started</p>
            </div>
            
            <div class="text-center text-xs text-gray-400 pt-4 border-t">
                Photos provided by <a href="https://unsplash.com/?utm_source=Servio&utm_medium=referral" target="_blank" class="underline hover:text-gray-600">Unsplash</a>
            </div>
        </div>
    </Modal>
</template>
