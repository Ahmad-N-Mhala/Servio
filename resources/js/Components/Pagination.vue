<template>
    <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 sm:px-6">
        <!-- Mobile view -->
        <div class="flex flex-1 justify-between sm:hidden">
            <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="relative inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed border border-gray-300"
            >
                Previous
            </button>
            <span class="text-sm text-gray-700 self-center">
                Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="relative inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed border border-gray-300"
            >
                Next
            </button>
        </div>

        <!-- Desktop view -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-semibold">{{ from }}</span> to 
                    <span class="font-semibold">{{ to }}</span> of 
                    <span class="font-semibold">{{ total }}</span> results
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-lg shadow-sm" aria-label="Pagination">
                    <!-- Previous Button -->
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="relative inline-flex items-center rounded-l-lg px-3 py-2 text-gray-500 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed border border-gray-300"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Page Numbers -->
                    <template v-for="page in visiblePages" :key="page">
                        <span
                            v-if="page === '...'"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300"
                        >
                            ...
                        </span>
                        <button
                            v-else
                            @click="goToPage(page as number)"
                            :class="[
                                'relative inline-flex items-center px-4 py-2 text-sm font-medium border border-gray-300 transition-colors',
                                currentPage === page
                                    ? 'z-10 bg-primary text-white border-primary'
                                    : 'text-gray-700 bg-white hover:bg-gray-50'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </template>

                    <!-- Next Button -->
                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="relative inline-flex items-center rounded-r-lg px-3 py-2 text-gray-500 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed border border-gray-300"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    path?: string;
}

interface PaginationLinks {
    first?: string;
    last?: string;
    prev?: string;
    next?: string;
}

const props = defineProps<{
    meta?: PaginationMeta;
    links?: PaginationLinks;
}>();

const currentPage = computed(() => props.meta?.current_page || 1);
const totalPages = computed(() => props.meta?.last_page || 1);
const total = computed(() => props.meta?.total || 0);
const from = computed(() => props.meta?.from || 0);
const to = computed(() => props.meta?.to || 0);

const visiblePages = computed(() => {
    const pages: (number | string)[] = [];
    const current = currentPage.value;
    const last = totalPages.value;

    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1);
        
        if (current > 3) {
            pages.push('...');
        }

        const start = Math.max(2, current - 1);
        const end = Math.min(last - 1, current + 1);

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        if (current < last - 2) {
            pages.push('...');
        }

        pages.push(last);
    }

    return pages;
});

const goToPage = (page: number) => {
    if (page < 1 || page > totalPages.value) return;
    
    const url = new URL(window.location.href);
    url.searchParams.set('page', page.toString());
    router.visit(url.toString(), { preserveState: true });
};
</script>
