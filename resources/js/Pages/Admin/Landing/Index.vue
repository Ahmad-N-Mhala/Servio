<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('common.landing_page_settings') || 'Landing Page Settings' }}
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Manage Landing Content</h3>
                    <p class="mt-1 text-sm text-gray-600">Customize the about us section, contact details, and modules shown on the landing page.</p>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        <button 
                            @click="activeTab = 'settings'" 
                            :class="[activeTab === 'settings' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']"
                        >
                            General Settings
                        </button>
                        <button 
                            @click="activeTab = 'modules'" 
                            :class="[activeTab === 'modules' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']"
                        >
                            Modules
                        </button>
                        <button 
                            @click="activeTab = 'screenshots'" 
                            :class="[activeTab === 'screenshots' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']"
                        >
                            Screenshots
                        </button>
                    </nav>
                </div>

                <!-- Settings Tab -->
                <div v-show="activeTab === 'settings'" class="bg-white rounded-xl shadow-sm p-6">
                    <form @submit.prevent="saveSettings" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contact Email -->
                            <div class="md:col-span-2">
                                <Input 
                                    v-model="settingsForm.settings.contact_email"
                                    label="Registration Contact Email"
                                    type="email"
                                    placeholder="admin@example.com"
                                    required
                                />
                                <p class="text-sm text-gray-500 mt-1">This email will receive notifications when someone registers interest.</p>
                            </div>

                            <!-- About Us Title -->
                            <div>
                                <Input 
                                    v-model="settingsForm.settings.about_us_title.en"
                                    label="About Us Title (English)"
                                    required
                                />
                            </div>
                            <div>
                                <Input 
                                    v-model="settingsForm.settings.about_us_title.ar"
                                    label="About Us Title (Arabic)"
                                    dir="rtl"
                                    required
                                />
                            </div>

                            <!-- About Us Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">About Us Description (English)</label>
                                <textarea 
                                    v-model="settingsForm.settings.about_us_description.en"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                ></textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">About Us Description (Arabic)</label>
                                <textarea 
                                    v-model="settingsForm.settings.about_us_description.ar"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-right"
                                    dir="rtl"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Statistics Section -->
                         <div class="border-t pt-6 mt-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-bold text-gray-900">Statistics Headers</h4>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="settingsForm.settings.stats_visible" class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                                    <span class="ml-2 text-sm text-gray-700">Show Statistics Section</span>
                                </label>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" :class="{ 'opacity-50 pointer-events-none': !settingsForm.settings.stats_visible }">
                                <div>
                                    <Input 
                                        v-model="settingsForm.settings.stats_restaurants"
                                        label="Restaurants Count (e.g. 500+)"
                                        required
                                    />
                                </div>
                                <div>
                                    <Input 
                                        v-model="settingsForm.settings.stats_orders"
                                        label="Orders Count (e.g. 1M+)"
                                        required
                                    />
                                </div>
                                <div>
                                    <Input 
                                        v-model="settingsForm.settings.stats_uptime"
                                        label="Uptime (e.g. 99.9%)"
                                        required
                                    />
                                </div>
                            </div>
                         </div>

                        <div class="flex justify-end pt-4 border-t">
                            <Button type="submit" :loading="settingsForm.processing">Save Settings</Button>
                        </div>
                    </form>
                </div>

                <!-- Modules Tab -->
                <div v-show="activeTab === 'modules'" class="space-y-6">
                    <div class="flex justify-end">
                         <Button @click="openCreateModuleModal">Add New Module</Button>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="module in modules" :key="module.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="module.icon" class="text-2xl">{{ module.icon }}</div> <!-- Assuming emoji or simple icon for now -->
                                        <div v-else class="text-gray-400">-</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ module.title?.en }}</div>
                                        <div class="text-sm text-gray-500">{{ module.title?.ar }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="module.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        >
                                            {{ module.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ module.sort_order }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openEditModuleModal(module)" class="text-blue-600 hover:text-blue-900 mr-4">Edit</button>
                                        <button @click="deleteModule(module)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="modules.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">No modules found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


        <!-- Screenshots Tab -->
        <div v-show="activeTab === 'screenshots'" class="bg-white rounded-xl shadow-sm p-6">
            <div class="mb-6">
                 <h4 class="text-lg font-bold text-gray-900 mb-4">Upload Screenshot</h4>
                 <form @submit.prevent="submitScreenshot" class="flex gap-4 items-end">
                    <div class="flex-grow">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <input type="file" @change="(e: Event) => screenshotForm.image = (e.target as HTMLInputElement).files?.[0] || null" class="block w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-full file:border-0
                          file:text-sm file:font-semibold
                          file:bg-primary/10 file:text-primary
                          hover:file:bg-primary/20
                        " accept="image/*" required />
                    </div>
                     <div class="w-32">
                         <Input v-model="screenshotForm.sort_order" label="Sort Order" type="number" />
                     </div>
                     <Button type="submit" :loading="screenshotForm.processing">Upload</Button>
                 </form>
            </div>

             <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <div v-for="shot in screenshots" :key="shot.id" class="relative group rounded-lg overflow-hidden border border-gray-200">
                    <img :src="shot.image_path" class="w-full h-48 object-cover" />
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button @click="deleteScreenshot(shot)" class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-black/70 text-white text-xs p-1 text-center">
                        Order: {{ shot.sort_order }}
                    </div>
                </div>
                 <div v-if="screenshots.length === 0" class="col-span-full text-center py-12 text-gray-500">
                    No screenshots uploaded yet.
                </div>
             </div>
        </div>

    </div>
</div>

        <!-- Module Modal -->
        <Modal :show="showModuleModal" @close="showModuleModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ editingModuleId ? 'Edit Module' : 'Add Module' }}</h3>
                <form @submit.prevent="submitModule" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <Input v-model="moduleForm.title.en" label="Title (English)" required :error="moduleForm.errors['title.en']" />
                        <Input v-model="moduleForm.title.ar" label="Title (Arabic)" dir="rtl" required :error="moduleForm.errors['title.ar']" />
                    </div>
                    
                    <div>
                        <Input v-model="moduleForm.icon" label="Icon (Emoji or Class)" placeholder="e.g. 🚀" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                            <textarea v-model="moduleForm.description.en" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm" required></textarea>
                            <p v-if="moduleForm.errors['description.en']" class="text-red-600 text-sm mt-1">{{ moduleForm.errors['description.en'] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Arabic)</label>
                            <textarea v-model="moduleForm.description.ar" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm text-right" dir="rtl" required></textarea>
                            <p v-if="moduleForm.errors['description.ar']" class="text-red-600 text-sm mt-1">{{ moduleForm.errors['description.ar'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <Input v-model="moduleForm.sort_order" label="Sort Order" type="number" />
                        <div class="flex items-center mt-6">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" v-model="moduleForm.is_active" class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                                <span class="ml-2 text-sm text-gray-700">Is Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t">
                        <Button type="button" variant="secondary" @click="showModuleModal = false" class="mr-2">Cancel</Button>
                        <Button type="submit" :loading="moduleForm.processing">Save</Button>
                    </div>
                </form>
            </div>
        </Modal>

    </AdminLayout>
</template>



<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps<{
    modules: any[];
    screenshots: any[];
    landingSettings: Record<string, any>;
}>();

const activeTab = ref('settings');
const showModuleModal = ref(false);
const editingModuleId = ref<number | null>(null);
const route = (window as any).route;

// Settings Form
const settingsForm = useForm({
    settings: {
        contact_email: props.landingSettings.contact_email || '',
        about_us_title: {
            en: props.landingSettings.about_us_title?.en || '',
            ar: props.landingSettings.about_us_title?.ar || ''
        },
        about_us_description: {
            en: props.landingSettings.about_us_description?.en || '',
            ar: props.landingSettings.about_us_description?.ar || ''
        },
        stats_restaurants: props.landingSettings.stats_restaurants || '500+',
        stats_orders: props.landingSettings.stats_orders || '1M+',
        stats_uptime: props.landingSettings.stats_uptime || '99.9%',
        stats_visible: props.landingSettings.stats_visible !== undefined ? Boolean(props.landingSettings.stats_visible) : true
    }
});

const saveSettings = () => {
    settingsForm.post(route('admin.landing.settings.update'), {
        preserveScroll: true
    });
};

// Module Form
const moduleForm = useForm({
    title: { en: '', ar: '' },
    description: { en: '', ar: '' },
    icon: '',
    sort_order: 0,
    is_active: true
});

const openCreateModuleModal = () => {
    editingModuleId.value = null;
    moduleForm.reset();
    showModuleModal.value = true;
};

const openEditModuleModal = (module: any) => {
    editingModuleId.value = module.id;
    moduleForm.title = { ...module.title };
    moduleForm.description = { ...module.description };
    moduleForm.icon = module.icon;
    moduleForm.sort_order = module.sort_order;
    moduleForm.is_active = Boolean(module.is_active);
    showModuleModal.value = true;
};

const submitModule = () => {
    if (editingModuleId.value) {
        moduleForm.put(route('admin.landing.modules.update', editingModuleId.value), {
            onSuccess: () => showModuleModal.value = false
        });
    } else {
        moduleForm.post(route('admin.landing.modules.store'), {
            onSuccess: () => showModuleModal.value = false
        });
    }
};

const deleteModule = (module: any) => {
    if (confirm('Are you sure you want to delete this module?')) {
        router.delete(route('admin.landing.modules.destroy', module.id));
    }
};

// Screenshot Form
const screenshotForm = useForm({
    image: null as File | null,
    sort_order: 0
});

const submitScreenshot = () => {
    screenshotForm.post(route('admin.landing.screenshots.store'), {
        onSuccess: () => {
            screenshotForm.reset();
            // Optional: Show toast
        }
    });
};

const deleteScreenshot = (shot: any) => {
    if (confirm('Delete this screenshot?')) {
        router.delete(route('admin.landing.screenshots.destroy', shot.id));
    }
};
</script>
