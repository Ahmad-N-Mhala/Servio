<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Your Restaurants
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Select a restaurant to manage
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-4xl px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Add New Restaurant Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg border-2 border-dashed border-gray-300 hover:border-primary hover:bg-gray-50 transition-colors cursor-pointer group flex flex-col items-center justify-center p-6 h-64">
                    <div class="p-3 rounded-full bg-gray-100 group-hover:bg-primary/10 transition-colors">
                        <svg class="w-8 h-8 text-gray-400 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Add New Restaurant</h3>
                    <p class="mt-1 text-sm text-gray-500 text-center">Expand your business by adding another location.</p>
                </div>

                <!-- Existing Restaurants -->
                <div 
                    v-for="restaurant in restaurants" 
                    :key="restaurant.id"
                    class="bg-white overflow-hidden shadow rounded-lg cursor-pointer hover:shadow-lg transition-shadow"
                    @click="switchRestaurant(restaurant)"
                >
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl">
                                    {{ restaurant.name.substring(0, 2).toUpperCase() }}
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-medium text-gray-900">{{ restaurant.name }}</h3>
                                <div class="flex items-center mt-1">
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-xs font-medium', 
                                        restaurant.role === 'owner' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'
                                    ]">
                                        {{ restaurant.role }}
                                    </span>
                                    <span class="mx-2 text-gray-300">|</span>
                                    <span class="text-sm text-gray-500">{{ restaurant.domain }}</span>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                        <div class="text-sm">
                            <span class="text-gray-500">Plan: </span>
                            <span class="font-medium text-gray-900 capitalize">{{ restaurant.plan }}</span>
                        </div>
                        <div v-if="restaurant.is_active" class="flex items-center text-green-600 text-sm font-medium">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                            Active
                        </div>
                        <div v-else class="flex items-center text-red-600 text-sm font-medium">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span>
                            Inactive
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Restaurant {
    id: number;
    name: string;
    slug: string;
    logo?: string;
    role: string;
    is_active: boolean;
    plan: string;
    domain: string;
}

const props = defineProps<{
    restaurants: Restaurant[];
}>();

import { router } from '@inertiajs/vue3';

const switchRestaurant = (restaurant: Restaurant) => {
    router.post(route('restaurants.switch', restaurant.id));
};
</script>
