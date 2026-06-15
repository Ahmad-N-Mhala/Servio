<template>
    <component :is="layoutComponent">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        {{ isEditing ? $t('user_manual.modify_title') : $t('nav.user_manual') }}
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        {{ isEditing ? $t('user_manual.edit_description') : $t('user_manual.view_description') }}
                    </p>
                </div>
                <!-- Inline Edit Actions -->
                <div v-if="isSuperAdmin || hasPermission('manage_user_manual')" class="flex items-center gap-3">
                    <button 
                        @click="toggleEditMode" 
                        class="px-4 py-2 text-sm font-medium border rounded-xl transition-all"
                        :class="isEditing 
                            ? 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200' 
                            : 'bg-white dark:bg-gray-800 border-primary text-primary hover:bg-primary/5'"
                    >
                        {{ isEditing ? $t('user_manual.cancel_edit') : $t('user_manual.edit_content') }}
                    </button>
                    <button 
                        v-if="isEditing" 
                        @click="saveChanges" 
                        :disabled="form.processing"
                        class="px-5 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-sm transition-all flex items-center gap-2 disabled:opacity-50"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? $t('user_manual.saving') : $t('user_manual.save_changes') }}
                    </button>
                </div>
            </div>

            <!-- Content Area Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Navigation Sidebar & Search -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Search Input -->
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="$t('user_manual.search_placeholder')"
                            class="block w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm"
                        >
                    </div>
 
                    <!-- Tabs Menu -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-2 shadow-sm space-y-1">
                        <button
                            v-for="section in form.sections"
                            :key="section.id"
                            @click="activeTab = section.id"
                            class="w-full text-start px-4 py-3 rounded-xl text-sm font-medium transition-all flex items-center justify-between"
                            :class="activeTab === section.id 
                                ? 'bg-primary/10 text-primary' 
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        >
                            <span>{{ currentLocale === 'ar' ? section.title.ar : section.title.en }}</span>
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'" />
                            </svg>
                        </button>
                        <button
                            @click="activeTab = 'faqs'"
                            class="w-full text-start px-4 py-3 rounded-xl text-sm font-medium transition-all flex items-center justify-between"
                            :class="activeTab === 'faqs' 
                                ? 'bg-primary/10 text-primary' 
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        >
                            <span>{{ $t('user_manual.faqs_title') }}</span>
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'" />
                            </svg>
                        </button>
                    </div>
                </div>
 
                <!-- Main Display Pane -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Language switch banner (in edit mode) -->
                    <div v-if="isEditing" class="bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-900 rounded-xl p-4 flex justify-between items-center gap-4">
                        <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-300 text-sm">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>{{ $t('user_manual.edit_warning') }}</span>
                        </div>
                        <div class="flex items-center bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-0.5 shadow-sm">
                            <button 
                                @click="editingLanguage = 'en'"
                                class="px-3 py-1 rounded text-xs font-semibold"
                                :class="editingLanguage === 'en' ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700'"
                            >
                                English
                            </button>
                            <button 
                                @click="editingLanguage = 'ar'"
                                class="px-3 py-1 rounded text-xs font-semibold"
                                :class="editingLanguage === 'ar' ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700'"
                            >
                                العربية
                            </button>
                        </div>
                    </div>

                    <!-- View Manual Process Section -->
                    <div v-if="activeTab !== 'faqs'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 sm:p-8 shadow-sm space-y-6">
                        <!-- Heading & Title -->
                        <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                            <div v-if="isEditing">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ $t('user_manual.section_title_label') }} ({{ editingLanguage === 'en' ? 'English' : 'العربية' }})</label>
                                <input 
                                    v-if="editingLanguage === 'en'"
                                    v-model="selectedSection.title.en"
                                    type="text"
                                    class="w-full text-2xl font-bold bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 focus:ring-primary focus:border-primary"
                                >
                                <input 
                                    v-else
                                    v-model="selectedSection.title.ar"
                                    type="text"
                                    dir="rtl"
                                    class="w-full text-2xl font-bold bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 focus:ring-primary focus:border-primary"
                                >
                            </div>
                            <h2 v-else class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ currentLocale === 'ar' ? selectedSection.title.ar : selectedSection.title.en }}
                            </h2>
                        </div>
 
                        <!-- Description -->
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $t('user_manual.overview_description_label') }}</h3>
                            <div v-if="isEditing">
                                <textarea 
                                    v-if="editingLanguage === 'en'"
                                    v-model="selectedSection.description.en"
                                    rows="3"
                                    class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 focus:ring-primary focus:border-primary text-sm leading-relaxed"
                                ></textarea>
                                <textarea 
                                    v-else
                                    v-model="selectedSection.description.ar"
                                    rows="3"
                                    dir="rtl"
                                    class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 focus:ring-primary focus:border-primary text-sm leading-relaxed"
                                ></textarea>
                            </div>
                            <p v-else class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                                {{ currentLocale === 'ar' ? selectedSection.description.ar : selectedSection.description.en }}
                            </p>
                        </div>
 
                        <!-- Process Steps -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-6">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $t('user_manual.steps_instructions_label') }}</h3>
                                <button 
                                    v-if="isEditing" 
                                    @click="addStep(selectedSection)" 
                                    type="button" 
                                    class="text-xs text-primary hover:text-primary-hover font-semibold flex items-center gap-1"
                                >
                                    {{ $t('user_manual.add_step') }}
                                </button>
                            </div>
 
                            <!-- Empty Steps State -->
                            <div v-if="selectedSection.steps.length === 0" class="text-center py-6 text-gray-400 text-sm">
                                {{ $t('user_manual.no_steps') }}
                            </div>
 
                            <!-- Step Items List -->
                            <ol class="space-y-4">
                                <li 
                                    v-for="(step, idx) in selectedSection.steps" 
                                    :key="idx"
                                    class="flex gap-4 p-4 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100/50 dark:border-gray-700/50 relative group"
                                >
                                    <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-primary/10 text-primary font-bold text-xs">
                                        {{ idx + 1 }}
                                    </span>
                                    <div class="flex-1 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                        <div v-if="isEditing" class="flex gap-2">
                                            <input 
                                                v-if="editingLanguage === 'en'"
                                                v-model="step.en"
                                                type="text"
                                                class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1 focus:ring-primary focus:border-primary text-sm"
                                            >
                                            <input 
                                                v-else
                                                v-model="step.ar"
                                                type="text"
                                                dir="rtl"
                                                class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1 focus:ring-primary focus:border-primary text-sm"
                                            >
                                            <button 
                                                @click="removeStep(selectedSection, idx)" 
                                                class="text-red-500 hover:text-red-750 px-2"
                                                type="button"
                                                :title="$t('user_manual.delete_step')"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                        <span v-else>{{ currentLocale === 'ar' ? step.ar : step.en }}</span>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
 
                    <!-- FAQs Section -->
                    <div v-else class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('user_manual.faqs_title') }}</h2>
                            <button 
                                v-if="isEditing" 
                                @click="addFaq" 
                                type="button" 
                                class="text-xs text-primary hover:text-primary-hover font-semibold flex items-center gap-1"
                            >
                                {{ $t('user_manual.add_faq') }}
                            </button>
                        </div>
 
                        <!-- FAQ Listing -->
                        <div class="space-y-4">
                            <div 
                                v-for="(faq, idx) in filteredFaqs" 
                                :key="idx" 
                                class="p-5 rounded-2xl border border-gray-100 dark:border-gray-700/80 bg-gray-50/20 dark:bg-gray-900/10 space-y-3 relative group"
                            >
                                <!-- Super Admin Remove Button -->
                                <button 
                                    v-if="isEditing" 
                                    @click="removeFaq(idx)" 
                                    type="button" 
                                    class="absolute top-4 right-4 text-red-500 hover:text-red-750 transition-colors"
                                    :title="$t('user_manual.delete_faq')"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
 
                                <!-- Question -->
                                <div class="space-y-1">
                                    <div v-if="isEditing">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $t('user_manual.question_label') }} ({{ editingLanguage === 'en' ? 'English' : 'العربية' }})</label>
                                        <input 
                                            v-if="editingLanguage === 'en'"
                                            v-model="faq.question.en"
                                            type="text"
                                            class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 focus:ring-primary focus:border-primary text-sm font-semibold text-gray-900 dark:text-white"
                                        >
                                        <input 
                                            v-else
                                            v-model="faq.question.ar"
                                            type="text"
                                            dir="rtl"
                                            class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 focus:ring-primary focus:border-primary text-sm font-semibold text-gray-900 dark:text-white"
                                        >
                                    </div>
                                    <h3 v-else class="font-bold text-slate-800 dark:text-white flex gap-2 items-start text-base">
                                        <span class="text-primary font-mono">Q.</span>
                                        <span>{{ currentLocale === 'ar' ? faq.question.ar : faq.question.en }}</span>
                                    </h3>
                                </div>
 
                                <!-- Answer -->
                                <div class="space-y-1">
                                    <div v-if="isEditing">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $t('user_manual.answer_label') }} ({{ editingLanguage === 'en' ? 'English' : 'العربية' }})</label>
                                        <textarea 
                                            v-if="editingLanguage === 'en'"
                                            v-model="faq.answer.en"
                                            rows="2"
                                            class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 focus:ring-primary focus:border-primary text-sm text-gray-600 dark:text-gray-300 leading-relaxed"
                                        ></textarea>
                                        <textarea 
                                            v-else
                                            v-model="faq.answer.ar"
                                            rows="2"
                                            dir="rtl"
                                            class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 focus:ring-primary focus:border-primary text-sm text-gray-600 dark:text-gray-300 leading-relaxed"
                                        ></textarea>
                                    </div>
                                    <div v-else class="pl-6 text-sm text-gray-600 dark:text-gray-300 leading-relaxed flex gap-2 items-start">
                                        <span class="text-slate-400 font-mono">A.</span>
                                        <p>{{ currentLocale === 'ar' ? faq.answer.ar : faq.answer.en }}</p>
                                    </div>
                                </div>
                            </div>
 
                            <!-- Empty FAQs Search / State -->
                            <div v-if="filteredFaqs.length === 0" class="text-center py-12 text-gray-400 text-sm">
                                {{ searchQuery ? $t('user_manual.no_matching_faqs') : $t('user_manual.no_faqs') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/Composables/usePermissions';

interface Translation {
    en: string;
    ar: string;
}

interface Step {
    en: string;
    ar: string;
}

interface Section {
    id: string;
    title: Translation;
    description: Translation;
    steps: Step[];
}

interface Faq {
    question: Translation;
    answer: Translation;
}

interface ManualContent {
    sections: Section[];
    faqs: Faq[];
}

const props = defineProps<{
    manualContent: ManualContent;
}>();

const page = usePage();
const { hasPermission } = usePermissions();
const currentLocale = computed(() => page.props.locale as string);
const isRtl = computed(() => page.props.isRtl as boolean);

const layoutComponent = computed(() => {
    return page.url.includes('/admin') ? AdminLayout : MainLayout;
});

const saveRouteName = computed(() => {
    return page.url.includes('/admin') ? 'admin.user-manual.update' : 'user-manual.update';
});

// Super Admin authorization checks
const isSuperAdmin = computed(() => {
    const user = page.props.auth?.user as any;
    if (!user) return false;
    return user.is_super_admin === true || (user.roles && user.roles.includes('super_admin'));
});

const isEditing = ref(false);
const editingLanguage = ref<'en' | 'ar'>('en');
const activeTab = ref(props.manualContent.sections[0]?.id || 'pos');
const searchQuery = ref('');

// Use Inertia useForm to track local changes and handle submission
const form = useForm({
    sections: JSON.parse(JSON.stringify(props.manualContent.sections)) as Section[],
    faqs: JSON.parse(JSON.stringify(props.manualContent.faqs)) as Faq[],
});

// Watch for external prop updates (reloads)
watch(() => props.manualContent, (newVal) => {
    form.sections = JSON.parse(JSON.stringify(newVal.sections));
    form.faqs = JSON.parse(JSON.stringify(newVal.faqs));
}, { deep: true });

// Toggle edit controls
const toggleEditMode = () => {
    if (isEditing.value) {
        // Revert form state back to original prop content on cancellation
        form.sections = JSON.parse(JSON.stringify(props.manualContent.sections));
        form.faqs = JSON.parse(JSON.stringify(props.manualContent.faqs));
        isEditing.value = false;
    } else {
        isEditing.value = true;
    }
};

// Locate the section that corresponds to the active tab
const selectedSection = computed(() => {
    const sec = form.sections.find(s => s.id === activeTab.value);
    return sec || form.sections[0];
});

// Filter FAQs based on the search query
const filteredFaqs = computed(() => {
    if (!searchQuery.value) return form.faqs;
    const query = searchQuery.value.toLowerCase().trim();

    return form.faqs.filter(faq => {
        return faq.question.en.toLowerCase().includes(query) ||
               faq.question.ar.toLowerCase().includes(query) ||
               faq.answer.en.toLowerCase().includes(query) ||
               faq.answer.ar.toLowerCase().includes(query);
    });
});

// Modify steps list
const addStep = (section: Section) => {
    section.steps.push({
        en: 'New instruction step details...',
        ar: 'تفاصيل خطوة إرشادية جديدة...'
    });
};

const removeStep = (section: Section, index: number) => {
    section.steps.splice(index, 1);
};

// Modify FAQs list
const addFaq = () => {
    form.faqs.push({
        question: {
            en: 'New FAQ Question?',
            ar: 'سؤال شائع جديد؟'
        },
        answer: {
            en: 'Answer content explaining the solution...',
            ar: 'محتوى الإجابة لتوضيح الحل...'
        }
    });
};

const removeFaq = (index: number) => {
    form.faqs.splice(index, 1);
};

// Save updated manual configuration back to DB
const saveChanges = () => {
    form.post(route(saveRouteName.value), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        }
    });
};
</script>

<style scoped>
/* Glassmorphism custom layouts */
.gradient-mesh {
    background-size: 40px 40px;
    background-image: linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                      linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
}
</style>
