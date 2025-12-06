<template>
    <div class="min-h-screen bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-100/40 via-gray-50 to-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Error Notification - Top Right -->
        <div v-if="hasErrors" class="fixed top-4 right-4 z-50 max-w-md animate-slide-in">
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg shadow-2xl p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-bold text-red-800">Authentication Error</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p v-if="form.errors.email" class="font-medium">{{ form.errors.email }}</p>
                            <p v-if="form.errors.password" class="font-medium mt-1">{{ form.errors.password }}</p>
                            <div v-if="Object.keys(form.errors).length > 0" class="mt-2 text-xs text-red-600 bg-red-100 p-2 rounded font-mono">
                                <p class="font-semibold mb-1">Debug Info:</p>
                                <p v-for="(error, key) in form.errors" :key="key">
                                    {{ key }}: {{ error }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <button @click="clearErrors" class="ml-4 flex-shrink-0">
                        <svg class="h-5 w-5 text-red-600 hover:text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-md w-full">
            <div class="text-center mb-10 flex flex-col items-center justify-center">
                <div class="flex justify-center w-full mb-6">
                    <Logo class="h-20 w-20" iconClass="w-20 h-25" :showText="true" />
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Welcome Back</h1>
                <p class="mt-3 text-lg text-gray-600">Sign in to access your restaurant dashboard</p>
            </div>

            <form method="POST" action="/en/login" class="bg-white/80 backdrop-blur-xl shadow-2xl rounded-3xl p-8 md:p-10 border border-white/50 transition-all duration-300">
                <input type="hidden" name="_token" :value="csrfToken">
                
                <div v-if="form.errors.email" class="mb-6 p-4 bg-red-50/80 backdrop-blur-sm border border-red-200 rounded-xl animate-shake">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-red-700 font-medium">{{ form.errors.email }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input
                            type="email"
                            name="email"
                            v-model="form.email"
                            required
                            class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-white/80 backdrop-blur-sm"
                            placeholder="you@example.com"
                        />
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input
                            type="password"
                            name="password"
                            v-model="form.password"
                            required
                            class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-white/80 backdrop-blur-sm"
                            placeholder="••••••••"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center cursor-pointer group">
                        <input
                            type="checkbox"
                            name="remember"
                            v-model="form.remember"
                            class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary/20 focus:ring-2 transition-all"
                        />
                        <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Remember me</span>
                    </label>
                    <a href="#" class="text-sm font-semibold text-primary hover:text-primary-hover transition-colors">Forgot password?</a>
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-primary to-primary-hover text-white font-semibold py-3.5 px-6 rounded-xl hover:shadow-lg hover:shadow-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/20 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                >
                    <span>Sign In</span>
                </button>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account? 
                        <a href="http://127.0.0.1:8000/en/onboard" class="font-semibold text-primary hover:text-primary-hover transition-colors">Get started</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Logo from '@/Components/Logo.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0;
});

const clearErrors = () => {
    form.clearErrors();
};

const csrfToken = computed(() => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
});
</script>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
    20%, 40%, 60%, 80% { transform: translateX(4px); }
}

@keyframes slide-in {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-shake {
    animation: shake 0.5s ease-in-out;
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>
