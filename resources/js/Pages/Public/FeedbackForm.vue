<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4 selection:bg-primary-100 selection:text-primary-700">
        
        <!-- Main Card -->
        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden ring-1 ring-black/5 transform transition-all duration-300 hover:shadow-3xl relative z-10">
            
            <!-- Header with Customizable Theme and Background Image -->
            <div 
                class="p-8 text-center relative overflow-hidden transition-colors duration-500"
                :style="{ 
                    backgroundColor: design.theme_color || '#1e3a8a', 
                    color: design.text_color || '#ffffff',
                    backgroundImage: design.background_image ? `url(${design.background_image})` : 'none',
                    backgroundSize: 'cover',
                    backgroundPosition: 'center'
                }"
            >
                <!-- Overlay for better text readability when background image is present -->
                <div v-if="design.background_image" class="absolute inset-0 bg-black/40"></div>
                
                <!-- Background Decoration (if no custom BG image) -->
                <div v-if="!design.background_image" class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                     <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M0 100 C 20 0 50 0 100 100 Z" fill="currentColor" />
                    </svg>
                </div>
                
                <div class="relative z-10">
                    <div class="mx-auto w-24 h-24 bg-white rounded-full shadow-xl flex items-center justify-center mb-4 overflow-hidden border-4 border-white/40 transform transition-transform hover:scale-105 duration-300">
                         <img v-if="design.header_logo || restaurant.logo" :src="design.header_logo || restaurant.logo" class="w-full h-full object-cover">
                         <span v-else class="text-4xl font-black text-gray-300 uppercase tracking-tighter">{{ restaurant.name.charAt(0) }}</span>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight mb-1 shadow-black/10 drop-shadow-md">{{ design.page_title || restaurant.name }}</h1>
                    <p class="font-medium text-sm tracking-wide uppercase opacity-90">{{ design.welcome_message || 'We value your opinion' }}</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-8 pb-10">
                <transition 
                    mode="out-in"
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform translate-y-4 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-4 opacity-0"
                >
                    <!-- Form State -->
                    <div v-if="!submitted" key="form">
                        <h2 class="text-xl font-bold text-gray-800 text-center mb-8 font-heading">{{ design.rating_label || 'How was your experience?' }}</h2>

                        <!-- Interactive Star Rating -->
                        <div class="flex justify-center gap-3 mb-10 group">
                            <button 
                                v-for="star in 5" 
                                :key="star"
                                type="button"
                                @click="handleRating(star)"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="transition-all duration-200 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 rounded-full p-1"
                                :class="{ 'scale-110': (hoverRating || form.rating) >= star }"
                            >
                                <svg 
                                    class="w-12 h-12 transition-all duration-200 drop-shadow-sm filter"
                                    :class="(hoverRating || form.rating) >= star ? 'text-amber-400 fill-amber-400 scale-110' : 'text-gray-200 fill-gray-50'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round" 
                                    stroke-linejoin="round"
                                >
                                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.563.046.8.773.35 1.139l-4.14 3.393a.562.562 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.92 19.95a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.14-3.393a.562.562 0 01.35-1.139l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Comment Field with Animation -->
                        <div class="mb-8 relative group/input">
                            <label for="comment" class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Comments (Optional)</label>
                            <textarea 
                                id="comment"
                                v-model="form.comment"
                                rows="3"
                                class="w-full rounded-2xl border-gray-200 bg-gray-50 shadow-inner focus:bg-white focus:border-primary-500 focus:ring-primary-500 transition-all duration-300 p-4 resize-none text-gray-700 placeholder-gray-400/70"
                                placeholder="What did you enjoy the most?"
                            ></textarea>
                            <div class="absolute inset-0 rounded-2xl border border-transparent pointer-events-none group-hover/input:border-gray-300 transition-colors"></div>
                        </div>

                        <!-- Action Button -->
                        <button 
                            @click="submit" 
                            :disabled="form.rating === 0 || form.processing"
                             class="w-full py-4 px-6 text-white font-bold rounded-xl shadow-lg transition-all transform active:scale-95 text-lg flex items-center justify-center gap-2 group/btn"
                            :style="{ backgroundColor: design.theme_color || '#1e3a8a' }"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-else>Submit Feedback</span>
                            <svg v-if="!form.processing" class="w-5 h-5 opacity-70 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                        
                        <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
                            <p v-if="form.rating === 0" class="text-center text-sm font-medium text-gray-400 mt-4 bg-gray-50 py-2 rounded-lg mx-auto w-max px-4">Tap a star to rate</p>
                        </transition>
                    </div>

                    <!-- Success State -->
                    <div v-else key="success" class="text-center py-8">
                         <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-green-200/50 shadow-lg animate-[bounce_1s_infinite]">
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Thank You!</h2>
                        <p class="text-gray-500 mb-8 px-8 leading-relaxed">Your feedback helps us create better experiences for everyone.</p>

                        <!-- Redirecting UI -->
                        <transition enter-active-class="transition duration-500 delay-100 ease-out" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                            <div v-if="redirecting" class="bg-blue-50 p-6 rounded-2xl mb-4 border border-blue-100 mx-4 border-l-4 border-l-blue-500 text-left relative overflow-hidden">
                                
                                <div class="relative z-10">
                                    <h3 class="text-blue-900 font-bold mb-1">One last step!</h3>
                                    <p class="text-blue-700 text-sm mb-4">We are redirecting you to Google Maps. Please share your experience there.</p>
                                    
                                    <div v-if="form.comment" class="bg-white p-3 rounded-lg border border-blue-200 mb-3 flex items-center justify-between shadow-sm">
                                        <p class="text-gray-600 text-sm italic truncate mr-2">"{{ form.comment }}"</p>
                                        <button @click="copyComment" class="text-blue-600 text-xs font-bold hover:underline whitespace-nowrap">
                                            {{ copied ? 'Copied!' : 'Copy' }}
                                        </button>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs text-blue-500 font-medium">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Redirecting...
                                    </div>
                                </div>
                            </div>
                        </transition>
                        
                        <transition enter-active-class="transition duration-500 delay-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100">
                             <a 
                                v-if="redirectUrl"
                                :href="redirectUrl" 
                                target="_blank"
                                class="inline-flex items-center text-primary-600 hover:text-primary-800 font-bold mt-4 group"
                            >
                                <span>Open Google Maps</span>
                                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </transition>
                    </div>
                </transition>
            </div>
        </div>
        
        <!-- Footer Info -->
        <p class="mt-8 text-xs text-center text-gray-400">Powered by Servio</p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps<{
    restaurant: {
        id: number;
        name: string;
        logo: string | null;
        slug: string;
    };
    settings?: Record<string, any>;
    order_id?: string;
    customer_id?: string;
}>();

const design = computed(() => props.settings?.feedback_design || {});

const hoverRating = ref(0);
const submitted = ref(false);
const redirecting = ref(false);
const redirectUrl = ref<string|null>(null);

const form = useForm({
    rating: 0,
    comment: '',
    order_id: props.order_id,
    customer_id: props.customer_id
});

const copied = ref(false);

const handleRating = (r: number) => {
    form.rating = r;
};

const copyComment = () => {
    if (!form.comment) return;
    navigator.clipboard.writeText(form.comment).then(() => {
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    });
};

const submit = () => {
    if (form.rating === 0) return;

    form.processing = true;

    axios.post(route('public.feedback.store', props.restaurant.slug), {
        ...form.data(),
        order_id: props.order_id,
        customer_id: props.customer_id
    }).then(response => {
        submitted.value = true;
        form.processing = false;
        
        if (response.data.should_redirect && response.data.redirect_url) {
            redirectUrl.value = response.data.redirect_url;
            redirecting.value = true;
            
            setTimeout(() => {
                window.location.href = response.data.redirect_url;
            }, 3000);
        }
    }).catch(error => {
        form.processing = false;
        alert('Something went wrong. Please try again.');
        console.error(error);
    });
};

const route = (window as any).route;
</script>

<style scoped>
@keyframes progress {
  0% { width: 0%; margin-left: 0; }
  50% { width: 50%; margin-left: 25%; }
  100% { width: 100%; margin-left: 100%; }
}

@keyframes bounce {
  0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8, 0, 1, 1); }
  50% { transform: translateY(0); animation-timing-function: cubic-bezier(0, 0, 0.2, 1); }
}
</style>
