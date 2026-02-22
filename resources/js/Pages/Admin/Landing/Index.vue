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
                            <!-- Hero Section -->
                            <div class="md:col-span-2 border-b pb-6 mb-6">
                                <h4 class="text-lg font-bold text-gray-900 mb-4">Hero Section</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Input 
                                            v-model="settingsForm.settings.hero_title.en"
                                            label="Hero Title (English)"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <Input 
                                            v-model="settingsForm.settings.hero_title.ar"
                                            label="Hero Title (Arabic)"
                                            dir="rtl"
                                            required
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Hero Subtitle (English)</label>
                                        <textarea 
                                            v-model="settingsForm.settings.hero_subtitle.en"
                                            rows="2"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                        ></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Hero Subtitle (Arabic)</label>
                                        <textarea 
                                            v-model="settingsForm.settings.hero_subtitle.ar"
                                            rows="2"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-right"
                                            dir="rtl"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

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

                            <!-- Dashboard Section -->
                            <div class="border-t pt-6 mt-6">
                                <h4 class="text-lg font-bold text-gray-900 mb-4">Dashboard Section</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <Input v-model="settingsForm.settings.dashboard_title.en" label="Section Title (English)" />
                                    </div>
                                    <div>
                                        <Input v-model="settingsForm.settings.dashboard_title.ar" label="Section Title (Arabic)" dir="rtl" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <Input v-model="settingsForm.settings.dashboard_desc.en" label="Description (English)" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <Input v-model="settingsForm.settings.dashboard_desc.ar" label="Description (Arabic)" dir="rtl" />
                                    </div>
                                </div>
                                
                                <h5 class="font-bold text-sm text-gray-700 mb-2">Key Points (Bullets)</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Input v-model="settingsForm.settings.dashboard_point_1.en" label="Point 1 (EN)" />
                                    <Input v-model="settingsForm.settings.dashboard_point_1.ar" label="Point 1 (AR)" dir="rtl" />
                                    
                                    <Input v-model="settingsForm.settings.dashboard_point_2.en" label="Point 2 (EN)" />
                                    <Input v-model="settingsForm.settings.dashboard_point_2.ar" label="Point 2 (AR)" dir="rtl" />
                                    
                                    <Input v-model="settingsForm.settings.dashboard_point_3.en" label="Point 3 (EN)" />
                                    <Input v-model="settingsForm.settings.dashboard_point_3.ar" label="Point 3 (AR)" dir="rtl" />
                                    
                                    <Input v-model="settingsForm.settings.dashboard_point_4.en" label="Point 4 (EN)" />
                                    <Input v-model="settingsForm.settings.dashboard_point_4.ar" label="Point 4 (AR)" dir="rtl" />
                                </div>

                                <h5 class="font-bold text-sm text-gray-700 mt-6 mb-2">Detailed Widget Explanations</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg">
                                    <!-- Widget 1 -->
                                    <div class="col-span-full font-semibold text-gray-800 border-b pb-2">Chart 1: Revenue</div>
                                    <Input v-model="settingsForm.settings.dash_widget_1_title.en" label="Title (EN)" />
                                    <Input v-model="settingsForm.settings.dash_widget_1_title.ar" label="Title (AR)" dir="rtl" />
                                    <div class="md:col-span-2">
                                        <textarea v-model="settingsForm.settings.dash_widget_1_desc.en" rows="4" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Description (EN)"></textarea>
                                        <textarea v-model="settingsForm.settings.dash_widget_1_desc.ar" rows="4" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Description (AR)" dir="rtl"></textarea>
                                    </div>

                                    <!-- Widget 2 -->
                                    <div class="col-span-full font-semibold text-gray-800 border-b pb-2 mt-4">Chart 2: Orders</div>
                                    <Input v-model="settingsForm.settings.dash_widget_2_title.en" label="Title (EN)" />
                                    <Input v-model="settingsForm.settings.dash_widget_2_title.ar" label="Title (AR)" dir="rtl" />
                                    <div class="md:col-span-2">
                                        <textarea v-model="settingsForm.settings.dash_widget_2_desc.en" rows="4" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Description (EN)"></textarea>
                                        <textarea v-model="settingsForm.settings.dash_widget_2_desc.ar" rows="4" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Description (AR)" dir="rtl"></textarea>
                                    </div>

                                    <!-- Widget 3 -->
                                    <div class="col-span-full font-semibold text-gray-800 border-b pb-2 mt-4">Chart 3: Products</div>
                                    <Input v-model="settingsForm.settings.dash_widget_3_title.en" label="Title (EN)" />
                                    <Input v-model="settingsForm.settings.dash_widget_3_title.ar" label="Title (AR)" dir="rtl" />
                                    <div class="md:col-span-2">
                                        <textarea v-model="settingsForm.settings.dash_widget_3_desc.en" rows="4" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Description (EN)"></textarea>
                                        <textarea v-model="settingsForm.settings.dash_widget_3_desc.ar" rows="4" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Description (AR)" dir="rtl"></textarea>
                                    </div>

                                    <!-- Widget 4 -->
                                    <div class="col-span-full font-semibold text-gray-800 border-b pb-2 mt-4">Chart 4: Payments</div>
                                    <Input v-model="settingsForm.settings.dash_widget_4_title.en" label="Title (EN)" />
                                    <Input v-model="settingsForm.settings.dash_widget_4_title.ar" label="Title (AR)" dir="rtl" />
                                    <div class="md:col-span-2">
                                        <textarea v-model="settingsForm.settings.dash_widget_4_desc.en" rows="4" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Description (EN)"></textarea>
                                        <textarea v-model="settingsForm.settings.dash_widget_4_desc.ar" rows="4" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Description (AR)" dir="rtl"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Features Section Config -->
                            <div class="border-t pt-6 mt-6">
                                <h4 class="text-lg font-bold text-gray-900 mb-4">Features Section Content</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <Input v-model="settingsForm.settings.features_title.en" label="Main Features Title (English)" />
                                </div>
                                <div>
                                    <Input v-model="settingsForm.settings.features_title.ar" label="Main Features Title (Arabic)" dir="rtl" />
                                </div>
                                <div class="md:col-span-2">
                                     <Input v-model="settingsForm.settings.features_desc.en" label="Main Features Subtitle (English)" />
                                </div>
                                 <div class="md:col-span-2">
                                     <Input v-model="settingsForm.settings.features_desc.ar" label="Main Features Subtitle (Arabic)" dir="rtl" />
                                </div>
                            </div>
                            
                            <!-- Feature 1: POS -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h5 class="font-bold mb-3">Feature 1: Smart POS</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Input v-model="settingsForm.settings.feature_pos_title.en" label="Title (EN)" />
                                    <Input v-model="settingsForm.settings.feature_pos_title.ar" label="Title (AR)" dir="rtl" />
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (EN)</label>
                                        <textarea v-model="settingsForm.settings.feature_pos_desc.en" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (AR)</label>
                                        <textarea v-model="settingsForm.settings.feature_pos_desc.ar" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-right" dir="rtl"></textarea>
                                    </div>
                                    <!-- Image Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Feature Images</label>
                                        
                                        <!-- Existing Images List -->
                                        <div v-if="settingsForm.settings.feature_pos_images && settingsForm.settings.feature_pos_images.length > 0" class="flex flex-wrap gap-4 mb-3">
                                            <div v-for="(img, idx) in settingsForm.settings.feature_pos_images" :key="idx" class="relative group w-24 h-24 rounded overflow-hidden bg-gray-200 border">
                                                <img :src="img" class="w-full h-full object-cover">
                                                <button type="button" @click="removeImage('feature_pos_images', idx)" class="absolute top-0 right-0 bg-red-500 text-white p-1 rounded-bl opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- New Files Input -->
                                        <input type="file" multiple @change="(e) => handleFileChange(e, 'feature_pos_images_new')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" accept="image/*" />
                                        
                                        <!-- Preview New Files -->
                                         <div v-if="settingsForm.settings.feature_pos_images_new && settingsForm.settings.feature_pos_images_new.length > 0" class="flex flex-wrap gap-2 mt-2">
                                            <div v-for="(file, idx) in settingsForm.settings.feature_pos_images_new" :key="idx" class="relative group w-16 h-16 rounded overflow-hidden bg-gray-100 border">
                                                <img :src="getObjectUrl(file)" class="w-full h-full object-cover opacity-70">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Feature 2: KDS -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h5 class="font-bold mb-3">Feature 2: KDS</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Input v-model="settingsForm.settings.feature_kds_title.en" label="Title (EN)" />
                                    <Input v-model="settingsForm.settings.feature_kds_title.ar" label="Title (AR)" dir="rtl" />
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (EN)</label>
                                        <textarea v-model="settingsForm.settings.feature_kds_desc.en" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (AR)</label>
                                        <textarea v-model="settingsForm.settings.feature_kds_desc.ar" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-right" dir="rtl"></textarea>
                                    </div>
                                    <!-- Image Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Feature Images</label>
                                         <!-- Existing -->
                                        <div v-if="settingsForm.settings.feature_kds_images && settingsForm.settings.feature_kds_images.length > 0" class="flex flex-wrap gap-4 mb-3">
                                            <div v-for="(img, idx) in settingsForm.settings.feature_kds_images" :key="idx" class="relative group w-24 h-24 rounded overflow-hidden bg-gray-200 border">
                                                <img :src="img" class="w-full h-full object-cover">
                                                <button type="button" @click="removeImage('feature_kds_images', idx)" class="absolute top-0 right-0 bg-red-500 text-white p-1 rounded-bl opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <input type="file" multiple @change="(e) => handleFileChange(e, 'feature_kds_images_new')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" accept="image/*" />

                                         <div v-if="settingsForm.settings.feature_kds_images_new && settingsForm.settings.feature_kds_images_new.length > 0" class="flex flex-wrap gap-2 mt-2">
                                            <div v-for="(file, idx) in settingsForm.settings.feature_kds_images_new" :key="idx" class="relative group w-16 h-16 rounded overflow-hidden bg-gray-100 border">
                                                <img :src="getObjectUrl(file)" class="w-full h-full object-cover opacity-70">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                             <!-- Feature 3: Inventory -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h5 class="font-bold mb-3">Feature 3: Inventory</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Input v-model="settingsForm.settings.feature_inventory_title.en" label="Title (EN)" />
                                    <Input v-model="settingsForm.settings.feature_inventory_title.ar" label="Title (AR)" dir="rtl" />
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (EN)</label>
                                        <textarea v-model="settingsForm.settings.feature_inventory_desc.en" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (AR)</label>
                                        <textarea v-model="settingsForm.settings.feature_inventory_desc.ar" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-right" dir="rtl"></textarea>
                                    </div>
                                    
                                    <!-- Image Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Feature Images</label>
                                        <div v-if="settingsForm.settings.feature_inventory_images && settingsForm.settings.feature_inventory_images.length > 0" class="flex flex-wrap gap-4 mb-3">
                                            <div v-for="(img, idx) in settingsForm.settings.feature_inventory_images" :key="idx" class="relative group w-24 h-24 rounded overflow-hidden bg-gray-200 border">
                                                <img :src="img" class="w-full h-full object-cover">
                                                <button type="button" @click="removeImage('feature_inventory_images', idx)" class="absolute top-0 right-0 bg-red-500 text-white p-1 rounded-bl opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <input type="file" multiple @change="(e) => handleFileChange(e, 'feature_inventory_images_new')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" accept="image/*" />
                                         <div v-if="settingsForm.settings.feature_inventory_images_new && settingsForm.settings.feature_inventory_images_new.length > 0" class="flex flex-wrap gap-2 mt-2">
                                            <div v-for="(file, idx) in settingsForm.settings.feature_inventory_images_new" :key="idx" class="relative group w-16 h-16 rounded overflow-hidden bg-gray-100 border">
                                                <img :src="getObjectUrl(file)" class="w-full h-full object-cover opacity-70">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bullets -->
                                     <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                                        <div class="md:col-span-2 font-semibold text-gray-700">Bullet Points</div>
                                        <Input v-model="settingsForm.settings.inventory_bullet_1.en" label="Bullet 1 (EN)" />
                                        <Input v-model="settingsForm.settings.inventory_bullet_1.ar" label="Bullet 1 (AR)" dir="rtl" />
                                        <Input v-model="settingsForm.settings.inventory_bullet_2.en" label="Bullet 2 (EN)" />
                                        <Input v-model="settingsForm.settings.inventory_bullet_2.ar" label="Bullet 2 (AR)" dir="rtl" />
                                    </div>
                                </div>
                            </div>

                            <!-- Feature 4: Loyalty -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h5 class="font-bold mb-3">Feature 4: Loyalty</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Input v-model="settingsForm.settings.feature_loyalty_title.en" label="Title (EN)" />
                                    <Input v-model="settingsForm.settings.feature_loyalty_title.ar" label="Title (AR)" dir="rtl" />
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (EN)</label>
                                        <textarea v-model="settingsForm.settings.feature_loyalty_desc.en" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (AR)</label>
                                        <textarea v-model="settingsForm.settings.feature_loyalty_desc.ar" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-right" dir="rtl"></textarea>
                                    </div>

                                    <!-- Image Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Feature Images</label>
                                        <div v-if="settingsForm.settings.feature_loyalty_images && settingsForm.settings.feature_loyalty_images.length > 0" class="flex flex-wrap gap-4 mb-3">
                                            <div v-for="(img, idx) in settingsForm.settings.feature_loyalty_images" :key="idx" class="relative group w-24 h-24 rounded overflow-hidden bg-gray-200 border">
                                                <img :src="img" class="w-full h-full object-cover">
                                                <button type="button" @click="removeImage('feature_loyalty_images', idx)" class="absolute top-0 right-0 bg-red-500 text-white p-1 rounded-bl opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <input type="file" multiple @change="(e) => handleFileChange(e, 'feature_loyalty_images_new')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" accept="image/*" />
                                         <div v-if="settingsForm.settings.feature_loyalty_images_new && settingsForm.settings.feature_loyalty_images_new.length > 0" class="flex flex-wrap gap-2 mt-2">
                                            <div v-for="(file, idx) in settingsForm.settings.feature_loyalty_images_new" :key="idx" class="relative group w-16 h-16 rounded overflow-hidden bg-gray-100 border">
                                                <img :src="getObjectUrl(file)" class="w-full h-full object-cover opacity-70">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bullets -->
                                     <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                                        <div class="md:col-span-2 font-semibold text-gray-700">Bullet Points</div>
                                        <Input v-model="settingsForm.settings.loyalty_bullet_1.en" label="Bullet 1 (EN)" />
                                        <Input v-model="settingsForm.settings.loyalty_bullet_1.ar" label="Bullet 1 (AR)" dir="rtl" />
                                        <Input v-model="settingsForm.settings.loyalty_bullet_2.en" label="Bullet 2 (EN)" />
                                        <Input v-model="settingsForm.settings.loyalty_bullet_2.ar" label="Bullet 2 (AR)" dir="rtl" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- How It Works Config -->
                        <div class="border-t pt-6 mt-6">
                            <h4 class="text-lg font-bold text-gray-900 mb-4">How It Works Section</h4>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <Input v-model="settingsForm.settings.how_it_works_title.en" label="Section Title (English)" />
                                </div>
                                <div>
                                    <Input v-model="settingsForm.settings.how_it_works_title.ar" label="Section Title (Arabic)" dir="rtl" />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Step 1 -->
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h6 class="font-bold mb-2">Step 1</h6>
                                    <Input v-model="settingsForm.settings.step_1_title.en" label="Title (EN)" class="mb-2" />
                                    <Input v-model="settingsForm.settings.step_1_title.ar" label="Title (AR)" dir="rtl" class="mb-2" />
                                    <textarea v-model="settingsForm.settings.step_1_desc.en" rows="2" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Desc (EN)"></textarea>
                                    <textarea v-model="settingsForm.settings.step_1_desc.ar" rows="2" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Desc (AR)" dir="rtl"></textarea>
                                </div>
                                <!-- Step 2 -->
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h6 class="font-bold mb-2">Step 2</h6>
                                    <Input v-model="settingsForm.settings.step_2_title.en" label="Title (EN)" class="mb-2" />
                                    <Input v-model="settingsForm.settings.step_2_title.ar" label="Title (AR)" dir="rtl" class="mb-2" />
                                    <textarea v-model="settingsForm.settings.step_2_desc.en" rows="2" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Desc (EN)"></textarea>
                                    <textarea v-model="settingsForm.settings.step_2_desc.ar" rows="2" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Desc (AR)" dir="rtl"></textarea>
                                </div>
                                <!-- Step 3 -->
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h6 class="font-bold mb-2">Step 3</h6>
                                    <Input v-model="settingsForm.settings.step_3_title.en" label="Title (EN)" class="mb-2" />
                                    <Input v-model="settingsForm.settings.step_3_title.ar" label="Title (AR)" dir="rtl" class="mb-2" />
                                    <textarea v-model="settingsForm.settings.step_3_desc.en" rows="2" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Desc (EN)"></textarea>
                                    <textarea v-model="settingsForm.settings.step_3_desc.ar" rows="2" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Desc (AR)" dir="rtl"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Collection Section -->
                        <div class="border-t pt-6 mt-6">
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Feedback Collection Section</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <Input v-model="settingsForm.settings.feedback_title.en" label="Section Title (English)" />
                                </div>
                                <div>
                                    <Input v-model="settingsForm.settings.feedback_title.ar" label="Section Title (Arabic)" dir="rtl" />
                                </div>
                                <div class="md:col-span-2">
                                    <Input v-model="settingsForm.settings.feedback_desc.en" label="Section Description (English)" />
                                </div>
                                <div class="md:col-span-2">
                                    <Input v-model="settingsForm.settings.feedback_desc.ar" label="Section Description (Arabic)" dir="rtl" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Feature 1: Collection -->
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h6 class="font-bold mb-2">Feature 1: Collection</h6>
                                    <Input v-model="settingsForm.settings.feedback_feature_1_title.en" label="Title (EN)" class="mb-2" />
                                    <Input v-model="settingsForm.settings.feedback_feature_1_title.ar" label="Title (AR)" dir="rtl" class="mb-2" />
                                    <textarea v-model="settingsForm.settings.feedback_feature_1_desc.en" rows="2" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Desc (EN)"></textarea>
                                    <textarea v-model="settingsForm.settings.feedback_feature_1_desc.ar" rows="2" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Desc (AR)" dir="rtl"></textarea>
                                </div>
                                
                                <!-- Feature 2: Google Maps -->
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h6 class="font-bold mb-2">Feature 2: Google Maps</h6>
                                    <Input v-model="settingsForm.settings.feedback_feature_2_title.en" label="Title (EN)" class="mb-2" />
                                    <Input v-model="settingsForm.settings.feedback_feature_2_title.ar" label="Title (AR)" dir="rtl" class="mb-2" />
                                    <textarea v-model="settingsForm.settings.feedback_feature_2_desc.en" rows="2" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Desc (EN)"></textarea>
                                    <textarea v-model="settingsForm.settings.feedback_feature_2_desc.ar" rows="2" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Desc (AR)" dir="rtl"></textarea>
                                </div>

                                <!-- Feature 3: Insights -->
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h6 class="font-bold mb-2">Feature 3: Insights</h6>
                                    <Input v-model="settingsForm.settings.feedback_feature_3_title.en" label="Title (EN)" class="mb-2" />
                                    <Input v-model="settingsForm.settings.feedback_feature_3_title.ar" label="Title (AR)" dir="rtl" class="mb-2" />
                                    <textarea v-model="settingsForm.settings.feedback_feature_3_desc.en" rows="2" class="w-full rounded border-gray-300 text-sm mb-2" placeholder="Desc (EN)"></textarea>
                                    <textarea v-model="settingsForm.settings.feedback_feature_3_desc.ar" rows="2" class="w-full rounded border-gray-300 text-sm text-right" placeholder="Desc (AR)" dir="rtl"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Our Services Section -->
                        <div class="border-t pt-8 mt-8">
                            <h4 class="text-xl font-bold mb-4">Our Services Section</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Input v-model="settingsForm.settings.services_title.en" label="Main Section Title (EN)" />
                                </div>
                                <div>
                                    <Input v-model="settingsForm.settings.services_title.ar" label="Main Section Title (AR)" dir="rtl" />
                                </div>

                                <!-- Software Services -->
                                <div class="col-span-1 md:col-span-2 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                    <h5 class="font-bold mb-4 text-gray-800">Software Solutions Card</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <Input v-model="settingsForm.settings.software_services_title.en" label="Title (EN)" />
                                        <Input v-model="settingsForm.settings.software_services_title.ar" label="Title (AR)" dir="rtl" />
                                        <div class="col-span-1 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (EN)</label>
                                            <textarea v-model="settingsForm.settings.software_services_desc.en" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                                        </div>
                                        <div class="col-span-1 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1 text-right">Description (AR)</label>
                                            <textarea v-model="settingsForm.settings.software_services_desc.ar" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm text-right" dir="rtl"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hardware Services -->
                                <div class="col-span-1 md:col-span-2 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                    <h5 class="font-bold mb-4 text-gray-800">Hardware Installation Card</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <Input v-model="settingsForm.settings.hardware_services_title.en" label="Title (EN)" />
                                        <Input v-model="settingsForm.settings.hardware_services_title.ar" label="Title (AR)" dir="rtl" />
                                        <div class="col-span-1 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (EN)</label>
                                            <textarea v-model="settingsForm.settings.hardware_services_desc.en" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                                        </div>
                                        <div class="col-span-1 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1 text-right">Description (AR)</label>
                                            <textarea v-model="settingsForm.settings.hardware_services_desc.ar" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm text-right" dir="rtl"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t mt-6">
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
        hero_title: {
            en: props.landingSettings.hero_title?.en || '',
            ar: props.landingSettings.hero_title?.ar || ''
        },
        hero_subtitle: {
            en: props.landingSettings.hero_subtitle?.en || '',
            ar: props.landingSettings.hero_subtitle?.ar || ''
        },
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
        stats_visible: props.landingSettings.stats_visible !== undefined ? Boolean(props.landingSettings.stats_visible) : true,

        // Dashboard Section
        dashboard_title: {
            en: props.landingSettings.dashboard_title?.en || '',
            ar: props.landingSettings.dashboard_title?.ar || ''
        },
        dashboard_desc: {
            en: props.landingSettings.dashboard_desc?.en || '',
            ar: props.landingSettings.dashboard_desc?.ar || ''
        },
        dashboard_point_1: {
            en: props.landingSettings.dashboard_point_1?.en || '',
            ar: props.landingSettings.dashboard_point_1?.ar || ''
        },
        dashboard_point_2: {
            en: props.landingSettings.dashboard_point_2?.en || '',
            ar: props.landingSettings.dashboard_point_2?.ar || ''
        },
        dashboard_point_3: {
            en: props.landingSettings.dashboard_point_3?.en || '',
            ar: props.landingSettings.dashboard_point_3?.ar || ''
        },
        dashboard_point_4: {
            en: props.landingSettings.dashboard_point_4?.en || '',
            ar: props.landingSettings.dashboard_point_4?.ar || ''
        },
        dash_widget_1_title: { en: props.landingSettings.dash_widget_1_title?.en || '', ar: props.landingSettings.dash_widget_1_title?.ar || '' },
        dash_widget_1_desc: { en: props.landingSettings.dash_widget_1_desc?.en || '', ar: props.landingSettings.dash_widget_1_desc?.ar || '' },
        
        dash_widget_2_title: { en: props.landingSettings.dash_widget_2_title?.en || '', ar: props.landingSettings.dash_widget_2_title?.ar || '' },
        dash_widget_2_desc: { en: props.landingSettings.dash_widget_2_desc?.en || '', ar: props.landingSettings.dash_widget_2_desc?.ar || '' },
        
        dash_widget_3_title: { en: props.landingSettings.dash_widget_3_title?.en || '', ar: props.landingSettings.dash_widget_3_title?.ar || '' },
        dash_widget_3_desc: { en: props.landingSettings.dash_widget_3_desc?.en || '', ar: props.landingSettings.dash_widget_3_desc?.ar || '' },
        
        dash_widget_4_title: { en: props.landingSettings.dash_widget_4_title?.en || '', ar: props.landingSettings.dash_widget_4_title?.ar || '' },
        dash_widget_4_desc: { en: props.landingSettings.dash_widget_4_desc?.en || '', ar: props.landingSettings.dash_widget_4_desc?.ar || '' },

        // Features Section
        features_title: {
            en: props.landingSettings.features_title?.en || '',
            ar: props.landingSettings.features_title?.ar || ''
        },
        features_desc: {
            en: props.landingSettings.features_desc?.en || '',
            ar: props.landingSettings.features_desc?.ar || ''
        },
        
        // Feature 1: POS
        feature_pos_title: {
            en: props.landingSettings.feature_pos_title?.en || '',
            ar: props.landingSettings.feature_pos_title?.ar || ''
        },
        feature_pos_desc: {
            en: props.landingSettings.feature_pos_desc?.en || '',
            ar: props.landingSettings.feature_pos_desc?.ar || ''
        },
        feature_pos_image: null,

        // Feature 2: KDS
        feature_kds_title: {
            en: props.landingSettings.feature_kds_title?.en || '',
            ar: props.landingSettings.feature_kds_title?.ar || ''
        },
        feature_kds_desc: {
            en: props.landingSettings.feature_kds_desc?.en || '',
            ar: props.landingSettings.feature_kds_desc?.ar || ''
        },
        feature_kds_image: null,

        // Feature 3: Inventory
        feature_inventory_title: {
            en: props.landingSettings.feature_inventory_title?.en || '',
            ar: props.landingSettings.feature_inventory_title?.ar || ''
        },
        feature_inventory_desc: {
            en: props.landingSettings.feature_inventory_desc?.en || '',
            ar: props.landingSettings.feature_inventory_desc?.ar || ''
        },

        // Feature 4: Loyalty
        feature_loyalty_title: {
            en: props.landingSettings.feature_loyalty_title?.en || '',
            ar: props.landingSettings.feature_loyalty_title?.ar || ''
        },
        feature_loyalty_desc: {
            en: props.landingSettings.feature_loyalty_desc?.en || '',
            ar: props.landingSettings.feature_loyalty_desc?.ar || ''
        },

        // How It Works
        how_it_works_title: {
            en: props.landingSettings.how_it_works_title?.en || 'How It Works',
            ar: props.landingSettings.how_it_works_title?.ar || 'كيف يعمل النظام'
        },
        step_1_title: {
            en: props.landingSettings.step_1_title?.en || 'Create Account',
            ar: props.landingSettings.step_1_title?.ar || 'أنشئ حساباً'
        },
        step_1_desc: {
            en: props.landingSettings.step_1_desc?.en || 'Sign up in seconds and setup your restaurant profile with ease.',
            ar: props.landingSettings.step_1_desc?.ar || 'سجل في ثوانٍ وقم بإعداد ملف تعريف مطعمك بسهولة.'
        },
        step_2_title: {
            en: props.landingSettings.step_2_title?.en || 'Build Menu',
            ar: props.landingSettings.step_2_title?.ar || 'ابنِ القائمة'
        },
        step_2_desc: {
            en: props.landingSettings.step_2_desc?.en || 'Add your items, variants, and categories.',
            ar: props.landingSettings.step_2_desc?.ar || 'قم بإضافة الأصناف، والإضافات، والأقسام الخاصة بك.'
        },
        step_3_title: {
            en: props.landingSettings.step_3_title?.en || 'Start Selling',
            ar: props.landingSettings.step_3_title?.ar || 'ابدأ البيع'
        },
        step_3_desc: {
            en: props.landingSettings.step_3_desc?.en || 'Download the app or use web POS to start taking orders immediately.',
            ar: props.landingSettings.step_3_desc?.ar || 'نزل التطبيق أو استخدم نقطة البيع عبر الويب لبدء العمل فوراً.'
        },

        // Feedback Collection Section
        feedback_title: {
            en: props.landingSettings.feedback_title?.en || 'Collect Feedback & Boost Your Google Reviews',
            ar: props.landingSettings.feedback_title?.ar || 'جمع الآراء وتعزيز تقييماتك على جوجل'
        },
        feedback_desc: {
            en: props.landingSettings.feedback_desc?.en || 'Gather valuable customer feedback seamlessly and automatically promote positive experiences to Google Maps.',
            ar: props.landingSettings.feedback_desc?.ar || 'اجمع آراء العملاء القيمة بسهولة واعرض التجارب الإيجابية تلقائياً على خرائط جوجل.'
        },
        feedback_feature_1_title: {
            en: props.landingSettings.feedback_feature_1_title?.en || 'Easy Feedback Collection',
            ar: props.landingSettings.feedback_feature_1_title?.ar || 'جمع الآراء بسهولة'
        },
        feedback_feature_1_desc: {
            en: props.landingSettings.feedback_feature_1_desc?.en || 'Capture customer ratings and reviews after every order with a simple, user-friendly interface.',
            ar: props.landingSettings.feedback_feature_1_desc?.ar || 'احصل على تقييمات ومراجعات العملاء بعد كل طلب من خلال واجهة بسيطة وسهلة الاستخدام.'
        },
        feedback_feature_2_title: {
            en: props.landingSettings.feedback_feature_2_title?.en || 'Automatic Google Maps Boost',
            ar: props.landingSettings.feedback_feature_2_title?.ar || 'تعزيز تلقائي على خرائط جوجل'
        },
        feedback_feature_2_desc: {
            en: props.landingSettings.feedback_feature_2_desc?.en || '4-5 star reviews are automatically directed to Google Maps, helping you build a stellar online reputation effortlessly.',
            ar: props.landingSettings.feedback_feature_2_desc?.ar || 'التقييمات 4-5 نجوم توجه تلقائياً إلى خرائط جوجل، مما يساعدك على بناء سمعة رائعة عبر الإنترنت بسهولة.'
        },
        feedback_feature_3_title: {
            en: props.landingSettings.feedback_feature_3_title?.en || 'Actionable Insights',
            ar: props.landingSettings.feedback_feature_3_title?.ar || 'رؤى قابلة للتنفيذ'
        },
        feedback_feature_3_desc: {
            en: props.landingSettings.feedback_feature_3_desc?.en || 'Lower ratings provide valuable feedback to improve your service, while high ratings boost your visibility.',
            ar: props.landingSettings.feedback_feature_3_desc?.ar || 'التقييمات المنخفضة توفر ملاحظات قيمة لتحسين خدمتك، بينما التقييمات العالية تعزز ظهورك.'
        },

        // Our Services Section
        services_title: {
            en: props.landingSettings.services_title?.en || 'Our Services',
            ar: props.landingSettings.services_title?.ar || 'خدماتنا'
        },
        software_services_title: {
            en: props.landingSettings.software_services_title?.en || 'Software Solutions',
            ar: props.landingSettings.software_services_title?.ar || 'حلول البرمجيات'
        },
        software_services_desc: {
            en: props.landingSettings.software_services_desc?.en || 'Our POS software solutions provide comprehensive system management tools, advanced reporting features, seamless integrations, and reliable ongoing support services.',
            ar: props.landingSettings.software_services_desc?.ar || 'توفر حلول برمجيات نقاط البيع لدينا أدوات إدارة نظام شاملة، وميزات تقارير متقدمة، وتكاملات سلسة، وخدمات دعم مستمرة وموثوقة.'
        },
        hardware_services_title: {
            en: props.landingSettings.hardware_services_title?.en || 'Hardware Installation',
            ar: props.landingSettings.hardware_services_title?.ar || 'تركيب الأجهزة'
        },
        hardware_services_desc: {
            en: props.landingSettings.hardware_services_desc?.en || 'We install and configure POS machines, bill printers, tablets, cash drawers, barcode scanners, and all required hardware to ensure a complete and ready-to-use setup.',
            ar: props.landingSettings.hardware_services_desc?.ar || 'نقوم بتركيب وتكوين أجهزة نقاط البيع، طابعات الفواتير، الأجهزة اللوحية، أدراج النقود، ماسحات الباركود، وجميع الأجهزة المطلوبة لضمان إعداد كامل وجاهز للاستخدام.'
        },

        // Images
        feature_pos_images: props.landingSettings.feature_pos_images || (props.landingSettings.feature_pos_image ? [props.landingSettings.feature_pos_image] : []),
        feature_pos_images_new: [],
        
        feature_kds_images: props.landingSettings.feature_kds_images || (props.landingSettings.feature_kds_image ? [props.landingSettings.feature_kds_image] : []),
        feature_kds_images_new: [],

        feature_inventory_images: props.landingSettings.feature_inventory_images || (props.landingSettings.feature_inventory_image ? [props.landingSettings.feature_inventory_image] : []),
        feature_inventory_images_new: [],
        inventory_bullet_1: {
             en: props.landingSettings.inventory_bullet_1?.en || '',
             ar: props.landingSettings.inventory_bullet_1?.ar || ''
        },
        inventory_bullet_2: {
             en: props.landingSettings.inventory_bullet_2?.en || '',
             ar: props.landingSettings.inventory_bullet_2?.ar || ''
        },

        feature_loyalty_images: props.landingSettings.feature_loyalty_images || (props.landingSettings.feature_loyalty_image ? [props.landingSettings.feature_loyalty_image] : []),
        feature_loyalty_images_new: [],
        loyalty_bullet_1: {
             en: props.landingSettings.loyalty_bullet_1?.en || '',
             ar: props.landingSettings.loyalty_bullet_1?.ar || ''
        },
        loyalty_bullet_2: {
             en: props.landingSettings.loyalty_bullet_2?.en || '',
             ar: props.landingSettings.loyalty_bullet_2?.ar || ''
        },
    }
});

const handleFileChange = (event: any, key: string) => {
    if (event.target.files) {
        (settingsForm.settings as any)[key] = Array.from(event.target.files);
    }
};

const getObjectUrl = (file: File) => {
    return URL.createObjectURL(file);
};

const removeImage = (key: string, index: number) => {
    const settings = settingsForm.settings as any;
    if (Array.isArray(settings[key])) {
        settings[key].splice(index, 1);
    }
};

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
