<template>
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Localization Management</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage English and Arabic translations</p>
                </div>
                <div class="flex items-center">
                    <button 
                        @click="showAddModal = true"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Translation
                    </button>
                    <button 
                        @click="showImportModal = true"
                        class="ml-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import
                    </button>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $page.props.flash.error }}</span>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <input 
                    v-model="searchQuery"
                    @input="search"
                    type="text" 
                    placeholder="Search translations..."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 dark:bg-gray-700 dark:text-white"
                />
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-fixed">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Key</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">English</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Arabic</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="(translation, index) in translations.data" :key="translation.full_key" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white truncate" :title="translation.file">
                                {{ translation.file }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 break-all">
                                {{ translation.full_key }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                <input 
                                    v-if="editingKey === translation.full_key + '_en'"
                                    v-model="editValue"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"
                                    @keyup.enter="saveTranslation(translation, 'en')"
                                    @keyup.esc="cancelEdit"
                                />
                                <span v-else class="cursor-pointer hover:text-primary block break-words" @click="editTranslation(translation, 'en')">
                                    {{ translation.en }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white" dir="rtl">
                                <input 
                                    v-if="editingKey === translation.full_key + '_ar'"
                                    v-model="editValue"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 text-right"
                                    dir="rtl"
                                    @keyup.enter="saveTranslation(translation, 'ar')"
                                    @keyup.esc="cancelEdit"
                                />
                                <span v-else class="block text-right cursor-pointer hover:text-primary break-words" @click="editTranslation(translation, 'ar')">{{ translation.ar || '—' }}</span>
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button 
                                    v-if="editingKey && (editingKey.startsWith(translation.full_key))"
                                    @click="saveTranslation(translation, editingKey.endsWith('_en') ? 'en' : 'ar')"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 mr-2"
                                >{{ $t('common.save') }}</button>
                                <button 
                                    v-if="editingKey && (editingKey.startsWith(translation.full_key))"
                                    @click="cancelEdit"
                                    class="text-gray-600 hover:text-gray-900 dark:text-gray-400"
                                >{{ $t('common.cancel') }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing {{ ((translations.current_page - 1) * translations.per_page) + 1 }} 
                            to {{ Math.min(translations.current_page * translations.per_page, translations.total) }} 
                            of {{ translations.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Link 
                                v-for="page in pages" 
                                :key="page"
                                :href="route('admin.localization.index', { page, search: filters.search })"
                                :class="[
                                    'px-3 py-1 rounded-lg text-sm',
                                    page === translations.current_page 
                                        ? 'bg-primary text-white' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                {{ page }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="showAddModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="addTranslation">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Add New Translation</h3>
                            <div class="space-y-4">
                                <!-- File selection removed as per requirement -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key (dot notation allowed)</label>
                                    <input v-model="addForm.key" type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600" placeholder="e.g. welcome_msg">
                                    <p v-if="props.errors?.key" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ props.errors.key }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">English Value</label>
                                    <input v-model="addForm.en_value" type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Arabic Value</label>
                                    <input v-model="addForm.ar_value" type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600 text-right" dir="rtl">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm" :disabled="addForm.processing">
                                Save
                            </button>
                            <button @click="showAddModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="showImportModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                     <form @submit.prevent="importTranslations">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Import Translations</h3>
                            <div class="space-y-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Upload an Excel or CSV file with the following headers: <strong>key, en, ar</strong>.
                                    <br>
                                    <a href="/templates/translations_template.csv" download class="text-primary hover:underline">Download Template</a>
                                </p>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File</label>
                                    <input 
                                        type="file" 
                                        @change="handleFileUpload" 
                                        accept=".csv,.xlsx"
                                        class="mt-1 block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-full file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-primary/10 file:text-primary
                                            hover:file:bg-primary/20
                                            dark:file:bg-gray-700 dark:file:text-gray-300"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm" :disabled="importForm.processing">
                                Import
                            </button>
                            <button @click="showImportModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    translations: Object,
    filters: Object,
    errors: Object
});

const searchQuery = ref(props.filters.search || '');
const editingKey = ref(null);
const editValue = ref('');
const showAddModal = ref(false);

const addForm = reactive({
    key: '',
    en_value: '',
    ar_value: '',
    processing: false
});

const pages = computed(() => {
    const total = props.translations.last_page;
    const current = props.translations.current_page;
    const delta = 2;
    const range = [];
    
    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i);
    }
    
    if (current - delta > 2) range.unshift('...');
    if (current + delta < total - 1) range.push('...');
    
    range.unshift(1);
    if (total > 1) range.push(total);
    
    return range.filter((v, i, a) => a.indexOf(v) === i);
});

const search = () => {
    router.get(route('admin.localization.index'), { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

const editTranslation = (translation, lang) => {
    editingKey.value = translation.full_key + '_' + lang;
    editValue.value = lang === 'en' ? translation.en : translation.ar;
};

const saveTranslation = (translation, lang) => {
    router.post(route('admin.localization.update'), {
        file: translation.file,
        key: translation.key,
        value: editValue.value,
        lang: lang
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editingKey.value = null;
            editValue.value = '';
        }
    });
};

const addTranslation = () => {
    addForm.processing = true;
    router.post(route('admin.localization.store'), {
        key: addForm.key,
        en_value: addForm.en_value,
        ar_value: addForm.ar_value
    }, {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.key = '';
            addForm.en_value = '';
            addForm.ar_value = '';
            addForm.processing = false;
        },
        onError: () => {
            addForm.processing = false;
        }
    });
};

const cancelEdit = () => {
    editingKey.value = null;
    editValue.value = '';
};

// Import Functionality
const showImportModal = ref(false);
const importForm = useForm({
    file: null,
    processing: false
});

const handleFileUpload = (event) => {
    importForm.file = event.target.files[0];
};

const importTranslations = () => {
    importForm.processing = true;
    importForm.post(route('admin.localization.import'), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
            importForm.processing = false;
        },
        onError: () => {
            importForm.processing = false;
        }
    });
};
</script>
