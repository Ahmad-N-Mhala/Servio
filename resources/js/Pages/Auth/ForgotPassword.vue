<template>
    <div class="min-h-screen bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-100/40 via-gray-50 to-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        
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
                    <Logo class="h-20 w-auto justify-center" iconClass="w-20 h-20" :showText="true" />
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">{{ $t('auth.forgot_password_title') }}</h1>
                <p class="mt-3 text-lg text-gray-600">{{ $t('auth.forgot_password_subtitle') }}</p>
            </div>

            <form @submit.prevent="submit" class="bg-white/80 backdrop-blur-xl shadow-2xl rounded-3xl p-8 md:p-10 border border-white/50 transition-all duration-300">
                
                <div v-if="status" class="mb-6 p-4 bg-green-50/80 backdrop-blur-sm border border-green-200 rounded-xl text-green-700 font-medium text-sm text-center">
                    {{ status }}
                </div>

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
                    <Input
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        :label="$t('auth.email_address')"
                        :placeholder="$t('auth.enter_email_placeholder')"
                        :error="form.errors.email"
                        inputClass="bg-white/80 backdrop-blur-sm"
                    >
                        <template #prefix>
                            <svg class="w-5 h-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </template>
                    </Input>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-gradient-to-r from-primary to-primary-hover text-white font-semibold py-3.5 px-6 rounded-xl hover:shadow-lg hover:shadow-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/20 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none flex justify-center items-center"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ $t('auth.send_reset_link') }}</span>
                </button>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        {{ $t('auth.remember_password') }} 
                        <Link :href="route('login')" class="font-semibold text-primary hover:text-primary-hover transition-colors">{{ $t('auth.back_to_login') }}</Link>
                    </p>
                </div>
            </form>
        </div>

        <!-- Contact Support Footer -->
        <div class="absolute bottom-6 w-full text-center text-sm text-gray-500/80">
            <p class="font-medium mb-2">{{ $t('landing.contact_support') }}</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
                <a href="mailto:support@kenildock.com" class="flex items-center gap-2 hover:text-primary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span dir="ltr">support@kenildock.com</span>
                </a>
                <a href="tel:+9715049460976" class="flex items-center gap-2 hover:text-primary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span dir="ltr">+971 50 494 60976</span>
                </a>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Logo from '@/Components/Logo.vue';
import Input from '@/Components/Input.vue';

const { locale } = useI18n();

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

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
    20%, 40%, 60%, 80% { transform: translateX(4px); }
}

.animate-shake {
    animation: shake 0.5s ease-in-out;
}
</style>
