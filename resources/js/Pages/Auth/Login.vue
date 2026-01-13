<template>
    <div class="min-h-screen bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-100/40 via-gray-50 to-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        
        <Toast 
            :message="toastMessage" 
            :title="toastTitle" 
            :type="toastType" 
            :trigger="toastTrigger" 
        />

        <!-- Language Switcher -->
        <div class="absolute top-6 right-6 z-10">
            <button 
                @click="toggleLanguage" 
                class="bg-white/50 backdrop-blur-md p-2.5 rounded-xl hover:bg-white/80 transition-all shadow-sm border border-white/50 flex items-center gap-2 text-sm font-bold text-gray-700 hover:text-primary group"
            >
                <span class="uppercase font-extrabold tracking-wider">{{ locale }}</span>
                <span class="w-px h-4 bg-gray-300 group-hover:bg-primary/30 transition-colors"></span>
                <div class="bg-white rounded-full p-1 shadow-sm group-hover:shadow group-hover:scale-110 transition-all duration-300">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.546-3.131 1.457-4.341" />
                    </svg>
                </div>
            </button>
        </div>

        <div class="max-w-md w-full">
            <div class="text-center mb-10 flex flex-col items-center justify-center">
                <div class="flex justify-center w-full mb-6">
                    <Logo class="h-20 w-20" iconClass="w-20 h-25" :showText="true" />
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">{{ $t('auth.welcome_back') }}</h1>
                <p class="mt-3 text-lg text-gray-600">{{ $t('auth.sign_in_subtitle') }}</p>
            </div>

            <form @submit.prevent="submit" class="bg-white/80 backdrop-blur-xl shadow-2xl rounded-3xl p-8 md:p-10 border border-white/50 transition-all duration-300">
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">{{ $t('auth.email_address') }}</label>
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
                            :placeholder="$t('auth.email_placeholder')"
                        />
                    </div>
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1 ml-1 animate-slide-in">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">{{ $t('auth.password_label') }}</label>
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
                            :placeholder="$t('auth.password_placeholder')"
                        />
                    </div>
                    <div v-if="form.errors.password" class="text-red-500 text-sm mt-1 ml-1 animate-slide-in">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="flex items-center justify-end mb-6">
                    <Link :href="route('password.request')" class="text-sm font-semibold text-primary hover:text-primary-hover transition-colors">{{ $t('auth.forgot_password') }}</Link>
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-primary to-primary-hover text-white font-semibold py-3.5 px-6 rounded-xl hover:shadow-lg hover:shadow-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/20 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">{{ $t('auth.signing_in') }}</span>
                    <span v-else>{{ $t('auth.sign_in') }}</span>
                </button>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        {{ $t('auth.no_account') }} 
                        <a :href="'/' + $i18n.locale + '/servio/onboard'" class="font-semibold text-primary hover:text-primary-hover transition-colors">{{ $t('auth.get_started') }}</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Logo from '@/Components/Logo.vue';
import Toast from '@/Components/Toast.vue';

const { t, locale } = useI18n();

const toggleLanguage = () => {
    const newLocale = locale.value === 'en' ? 'ar' : 'en';
    
    // Quick URL patch for LaravelLocalization
    const currentPath = window.location.pathname; 
    const segments = currentPath.split('/'); 
    if (segments[1] && (segments[1] === 'en' || segments[1] === 'ar')) {
        segments[1] = newLocale;
        window.location.href = segments.join('/');
    } else {
            // If no locale in URL (default), append or redirect
            // This depends on prefix strategy. Assuming prefix always exists for non-default or configured so.
            // If default is /en hidden, then /ar works.
            window.location.href = `/${newLocale}`;
    }
};

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
    form.clearErrors();
    let hasErrors = false;

    // Client-side validation
    if (!form.email) {
        form.errors.email = t('auth.email_required');
        hasErrors = true;
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(form.email)) {
            form.errors.email = t('auth.email_invalid');
            hasErrors = true;
        }
    }

    if (!form.password) {
        form.errors.password = t('auth.password_required');
        hasErrors = true;
    }

    if (hasErrors) {
        toastType.value = 'error';
        toastTitle.value = t('auth.validation_error');
        toastMessage.value = t('auth.check_required_fields');
        toastTrigger.value++;
        return;
    }

    // Get the current URL path to extract locale
    const currentPath = window.location.pathname;
    // Extract locale from path (e.g., /en/servio/login -> en)
    const match = currentPath.match(/^\/([a-z]{2})\//);
    const locale = match ? match[1] : 'en';
    // FIX: Include /servio prefix in the login URL
    const loginUrl = `/${locale}/servio/login`;
    
    form.post(loginUrl, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handle handled by redirect
        },
        onError: (errors: any) => {
            console.error('Login failed', errors);
            
            // Set message and type first
            toastType.value = 'error';
            toastTitle.value = t('auth.login_failed');
            
            if (errors.email) {
                toastMessage.value = errors.email;
            } else if (errors.password) {
                toastMessage.value = errors.password;
            } else {
                toastMessage.value = t('auth.wrong_credentials');
            }

            // Then trigger the toast
            // Use setTimeout to ensure reactivity propagation if needed, 
            // though usually Vue handles this batching correctly. 
            // We just ensure 'message' is set in the state before 'trigger' changes.
            toastTrigger.value++;
            
            // Also reset password
            form.reset('password');
        },
        onFinish: () => {
             // onFinish is called after success or error
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
