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
                <!-- Add New Restaurant Card - Can Add -->
                <div 
                    v-if="canAddRestaurant"
                    class="bg-white overflow-hidden shadow rounded-lg border-2 border-dashed border-gray-300 hover:border-primary hover:bg-gray-50 transition-colors cursor-pointer group flex flex-col items-center justify-center p-6 h-64"
                    @click="handleAddRestaurant"
                >
                    <div class="p-3 rounded-full bg-gray-100 group-hover:bg-primary/10 transition-colors">
                        <svg class="w-8 h-8 text-gray-400 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Add New Restaurant</h3>
                    <p class="mt-1 text-sm text-gray-500 text-center">Expand your business by adding another location.</p>
                </div>

                <!-- Add New Restaurant Card - Upgrade Required -->
                <div 
                    v-else
                    class="relative bg-gradient-to-br from-amber-50 via-orange-50 to-red-50 overflow-hidden shadow-xl rounded-lg border-2 border-orange-400 hover:border-orange-500 hover:shadow-2xl transition-all duration-300 cursor-pointer group flex flex-col items-center justify-center p-6 h-64"
                    @click="router.get(route('plans.index'))"
                >
                    <!-- Decorative background pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <circle cx="10" cy="10" r="1" fill="currentColor" class="text-orange-500"/>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#grid)"/>
                        </svg>
                    </div>

                    <!-- Premium badge -->
                    <div class="absolute top-3 right-3 bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                        UPGRADE
                    </div>

                    <div class="relative z-10 flex flex-col items-center">
                        <!-- Animated icon container -->
                        <div class="p-4 rounded-full bg-gradient-to-br from-orange-100 to-red-100 group-hover:from-orange-200 group-hover:to-red-200 transition-all duration-300 shadow-lg group-hover:shadow-xl group-hover:scale-110">
                            <svg class="w-10 h-10 text-orange-600 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>

                        <h3 class="mt-5 text-xl font-bold text-gray-900 text-center">
                            🚀 Upgrade to Add More
                        </h3>
                        
                        <div class="mt-3 bg-white/80 backdrop-blur-sm rounded-lg px-4 py-3 shadow-md">
                            <p class="text-sm text-gray-700 text-center leading-relaxed">
                                Your <span class="font-bold text-orange-600">{{ currentPlan?.name || 'current' }}</span> plan allows<br/>
                                <span class="text-2xl font-extrabold text-orange-600">{{ currentPlan?.max_restaurants || 1 }}</span> 
                                <span class="text-gray-600">restaurant{{ (currentPlan?.max_restaurants || 1) > 1 ? 's' : '' }}</span>
                            </p>
                        </div>

                        <div class="mt-4 flex items-center gap-2 bg-gradient-to-r from-orange-500 to-red-500 text-white px-5 py-2.5 rounded-full shadow-lg group-hover:shadow-xl group-hover:scale-105 transition-all duration-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-bold">View Upgrade Options</span>
                        </div>
                    </div>
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
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

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

interface CurrentPlan {
    name: string;
    max_restaurants: number;
}

const props = defineProps<{
    restaurants: Restaurant[];
    canAddRestaurant: boolean;
    currentPlan: CurrentPlan | null;
}>();

const switchRestaurant = (restaurant: Restaurant) => {
    router.post(route('restaurants.switch', restaurant.id));
};

const handleAddRestaurant = () => {
    // For now, just navigate to plans page since we don't have create functionality yet
    // TODO: Implement restaurant creation
    router.get(route('plans.index'));
};
</script>
