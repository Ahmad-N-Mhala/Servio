<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-8 text-center">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Welcome to RestaurFy!</h1>
            <p class="text-gray-600 mb-6">Your account has been created successfully.</p>
            <p class="text-sm text-gray-500 mb-6">
                Access your dashboard at: <strong>{{ domain }}</strong>
            </p>
            <a
                :href="`http://${domain}/dashboard`"
                class="inline-block bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition"
            >
                Go to Dashboard
            </a>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    subdomain: string;
    domain: string;
}>();
</script>

