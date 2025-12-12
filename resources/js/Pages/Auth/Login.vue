<template>
    <div class="min-h-screen bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-100/40 via-gray-50 to-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        
        <Toast 
            :message="toastMessage" 
            :title="toastTitle" 
            :type="toastType" 
            :trigger="toastTrigger" 
        />

        <div class="max-w-md w-full">
            <div class="text-center mb-10 flex flex-col items-center justify-center">
                <div class="flex justify-center w-full mb-6">
                    <Logo class="h-20 w-20" iconClass="w-20 h-25" :showText="true" />
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Welcome Back</h1>
                <p class="mt-3 text-lg text-gray-600">Sign in to access your restaurant dashboard</p>
            </div>

            <form @submit.prevent="submit" class="bg-white/80 backdrop-blur-xl shadow-2xl rounded-3xl p-8 md:p-10 border border-white/50 transition-all duration-300">
                
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
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-200': form.errors.email }"
                            placeholder="you@example.com"
                        />
                    </div>
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1 ml-1 animate-slide-in">
                        {{ form.errors.email }}
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
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-200': form.errors.password }"
                            placeholder="••••••••"
                        />
                    </div>
                    <div v-if="form.errors.password" class="text-red-500 text-sm mt-1 ml-1 animate-slide-in">
                        {{ form.errors.password }}
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
                    <Link :href="route('password.request')" class="text-sm font-semibold text-primary hover:text-primary-hover transition-colors">Forgot password?</Link>
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-primary to-primary-hover text-white font-semibold py-3.5 px-6 rounded-xl hover:shadow-lg hover:shadow-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/20 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Signing in...</span>
                    <span v-else>Sign In</span>
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
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Logo from '@/Components/Logo.vue';
import Toast from '@/Components/Toast.vue';

const route = (window as any).route;

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const toastMessage = ref('');
const toastTitle = ref('');
const toastType = ref('info');
const toastTrigger = ref(0);

const submit = () => {
    // Get the current URL path to extract locale
    const currentPath = window.location.pathname;
    // Extract locale from path (e.g., /en/login -> en)
    const match = currentPath.match(/^\/([a-z]{2})\//);
    const locale = match ? match[1] : 'en';
    const loginUrl = `/${locale}/login`;
    
    form.post(loginUrl, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handle handled by redirect
        },
        onError: (errors: any) => {
            console.error('Login failed', errors);
            toastTrigger.value++;
            toastType.value = 'error';
            toastTitle.value = 'Login Failed';
            
            if (errors.email) {
                toastMessage.value = errors.email;
            } else if (errors.password) {
                toastMessage.value = errors.password;
            } else {
                toastMessage.value = 'Please check your credentials and try again.';
            }

            // If we have detailed errors, we can show them
            if (Object.keys(errors).length > 0) {
                 const errorList = Object.entries(errors).map(([key, msg]) => `${key}: ${msg}`).join(', ');
                 // If it's just email/password validation, the above is fine. 
                 // If generic error, show more info.
                 if (!errors.email && !errors.password) {
                     toastMessage.value = errorList;
                 }
            }
        },
        onFinish: () => {
            form.reset('password');
        }
    });
};
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
