    <template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 gradient-mesh">
        <Toast 
            :message="toastMessage" 
            :title="toastTitle" 
            :type="toastType" 
            :trigger="toastTrigger" 
        />
        <!-- Sidebar -->
        <aside 
            class="fixed inset-y-0 z-50 glass-sidebar shadow-lifted transform transition-all duration-300 ease-in-out lg:translate-x-0" 
            :class="[
                currentLocale === 'ar' ? 'right-0' : 'left-0',
                isSidebarCollapsed ? 'w-20' : 'w-64',
                isSidebarOpen ? 'translate-x-0' : (currentLocale === 'ar' ? 'translate-x-full' : '-translate-x-full')
            ]"
        >
            <!-- Logo Section with Collapse Button -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100/50 dark:border-gray-700/50">
                <Logo v-if="!isSidebarCollapsed" />
                <button 
                    @click="isSidebarCollapsed = !isSidebarCollapsed"
                    class="hidden lg:flex p-2 text-gray-500 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all"
                    :class="isSidebarCollapsed ? 'mx-auto' : ''"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!isSidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="currentLocale === 'ar' ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="currentLocale === 'ar' ? 'M11 19l-7-7 7-7m8 14l-7-7 7-7' : 'M13 5l7 7-7 7M5 5l7 7-7 7'" />
                    </svg>
                </button>
            </div>
            
            <!-- Navigation -->
            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">
                <!-- Dashboard -->
                <Link 
                    :href="route('admin.dashboard')" 
                    :class="[
                        'group flex items-center rounded-lg transition-all duration-200',
                        isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                        $page.url.includes('/admin/dashboard') 
                            ? 'bg-primary/10 text-primary font-medium' 
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                    ]"
                >
                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-sm" v-if="!isSidebarCollapsed">Dashboard</span>
                </Link>

                <!-- Restaurants -->
                <Link 
                    :href="route('admin.restaurants.index')" 
                    :class="[
                        'group flex items-center rounded-lg transition-all duration-200',
                        isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                        $page.url.includes('/admin/restaurants') 
                            ? 'bg-primary/10 text-primary font-medium' 
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                    ]"
                >
                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-sm" v-if="!isSidebarCollapsed">Restaurants</span>
                </Link>

                <!-- Users -->
                <Link 
                    :href="route('admin.users.index')" 
                    :class="[
                        'group flex items-center rounded-lg transition-all duration-200',
                        isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                        $page.url.includes('/admin/users') 
                            ? 'bg-primary/10 text-primary font-medium' 
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                    ]"
                >
                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-sm" v-if="!isSidebarCollapsed">Users</span>
                </Link>

                <!-- Deleted Data -->
                <Link 
                    :href="route('admin.deleted-data.index')" 
                    :class="[
                        'group flex items-center rounded-lg transition-all duration-200',
                        isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                        $page.url.includes('/admin/deleted-data') 
                            ? 'bg-primary/10 text-primary font-medium' 
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                    ]"
                >
                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="text-sm" v-if="!isSidebarCollapsed">Deleted Data</span>
                </Link>

                <!-- Configurations Section -->
                <div>
                     <div 
                        @click="toggleMenu('configurations')"
                        class="px-3 mb-1 flex items-center justify-between cursor-pointer group" 
                        v-if="!isSidebarCollapsed"
                    >
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">Configurations</span>
                        <svg 
                            class="w-3 h-3 text-gray-400 transition-transform duration-200" 
                            :class="openMenus['configurations'] ? 'rotate-180' : ''"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div v-else class="h-px bg-gray-200 dark:bg-gray-700 mx-3 my-2"></div>

                    <div v-show="isSidebarCollapsed || openMenus['configurations']" class="space-y-0.5">
                        <!-- Delivery Integrations -->
                        <Link 
                            :href="route('admin.integrations.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/integrations') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Delivery Integrations</span>
                        </Link>

                        <!-- Delivery Providers -->
                        <Link 
                            :href="route('admin.delivery-providers.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/delivery-providers') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Delivery Providers</span>
                        </Link>

                        <!-- Subscription Plans -->
                        <Link 
                            :href="route('admin.plans.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/plans') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Subscription Plans</span>
                        </Link>

                        <!-- Restaurant Subscriptions -->
                        <Link 
                            :href="route('admin.subscriptions.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/subscriptions') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Restaurant Subscriptions</span>
                        </Link>

                        <!-- Roles & Permissions -->
                        <Link 
                            :href="route('admin.permissions.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/permissions') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Roles & Permissions</span>
                        </Link>

                        <!-- System Emails -->
                        <Link 
                            :href="route('admin.email.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/email-templates') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">System Emails</span>
                        </Link>

                        <!-- System SMS -->
                        <Link 
                            :href="route('admin.sms.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/sms-templates') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">System SMS</span>
                        </Link>

                        <!-- Landing Page -->
                        <Link 
                            :href="route('admin.landing.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/landing-page') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Landing Page</span>
                        </Link>

                        <!-- Localization -->
                        <Link 
                            :href="route('admin.localization.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/admin/localization') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Localization</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100/50 dark:border-gray-700/50">
                <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-gradient-to-r from-primary/5 to-primary/10">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $t('common.pro_plan') }}</p>
                        <p class="text-xs text-gray-500">{{ $t('common.active') }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="min-h-screen flex flex-col transition-all duration-300" :class="[
            currentLocale === 'ar' ? (isSidebarCollapsed ? 'lg:mr-20' : 'lg:mr-64') : (isSidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64')
        ]">
            <!-- Header -->
            <header class="glass sticky top-0 z-40 h-16 flex items-center justify-between px-6 lg:px-8 border-b border-gray-100/50 dark:border-gray-700/50">
                <button @click="isSidebarOpen = !isSidebarOpen" class="lg:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Header Title Slot -->
                <div class="ml-4">
                     <h2 class="text-xl font-bold text-gray-800 leading-tight">
                        <slot name="header" />
                    </h2>
                </div>

                <div class="flex items-center ml-auto gap-3" :class="currentLocale === 'ar' ? 'space-x-reverse space-x-3' : 'space-x-3'">
                    <!-- Restaurant Switcher (Super Admin Only) -->
                    <div class="relative group" v-if="allRestaurants && allRestaurants.length > 0">
                        <button
                            @click="isRestaurantMenuOpen = !isRestaurantMenuOpen"
                            class="flex items-center space-x-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all shadow-sm"
                        >
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ selectedRestaurant?.name || 'Select Restaurant' }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <!-- Dropdown -->
                        <div v-if="isRestaurantMenuOpen" class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 border-b border-gray-50">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Switch Context</p>
                            </div>
                            <button
                                v-for="restaurant in allRestaurants"
                                :key="restaurant.id"
                                @click="switchRestaurant(restaurant)"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-primary/5 hover:text-primary transition-colors flex items-center justify-between group"
                            >
                                <span>{{ restaurant.name }}</span>
                                <svg v-if="selectedRestaurant?.id === restaurant.id" class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </div>
                    <!-- Language Toggle -->
                    <button 
                        @click="toggleLanguage" 
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300"
                    >
                        <span class="uppercase">{{ locale }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.546-3.131 1.457-4.341" />
                        </svg>
                    </button>

                    <!-- Divider -->
                    <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>

                    <!-- User Menu -->
                    <div class="relative">
                        <button 
                            @click="userMenuOpen = !userMenuOpen" 
                            class="flex items-center gap-3 p-1.5 pr-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                        >
                            <img 
                                class="h-8 w-8 rounded-lg object-cover ring-2 ring-primary/20" 
                                :src="userAvatarUrl" 
                                alt="User avatar" 
                            />
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ userName }}</p>
                                <p class="text-xs text-gray-500">Admin</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div 
                                v-show="userMenuOpen" 
                                @click.away="userMenuOpen = false" 
                                class="absolute right-0 mt-2 w-56 glass-card rounded-xl shadow-lifted py-2 z-50"
                            >
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ userName }}</p>
                                    <p class="text-xs text-gray-500">{{ userEmail }}</p>
                                </div>
                                <div class="py-1">
                                    <Link :href="route('admin.profile.edit')" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $t('auth.profile') }}
                                    </Link>

                                    <Link :href="route('restaurants.index')" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        Multi-Store Overview
                                    </Link>
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-1">
                                    <Link 
                                        :href="route('logout')" 
                                        method="post" 
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ $t('auth.logout') }}
                                    </Link>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 lg:p-8 animate-fade-in">
                <slot />
            </main>
        </div>

        <!-- Mobile Overlay -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="isSidebarOpen" 
                @click="isSidebarOpen = false"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden"
            ></div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Logo from '@/Components/Logo.vue';
import Toast from '@/Components/Toast.vue';
import { router } from '@inertiajs/vue3';

const page = usePage();
const route = (window as any).route;
const { locale } = useI18n();

// State
const isSidebarCollapsed = ref(false);
const isSidebarOpen = ref(false);
const isRestaurantMenuOpen = ref(false);
const userMenuOpen = ref(false);

const getInitialMenuState = () => {
    if (typeof window !== 'undefined' && window.localStorage) {
        const saved = localStorage.getItem('sidebarMenuState');
        if (saved) return JSON.parse(saved);
    }
    return {
        'operations': true,
        'management': false,
        'growth': false,
        'configurations': true
    };
};

const openMenus = ref<Record<string, boolean>>(getInitialMenuState());
const selectedRestaurant = ref<any>((page.props as any).current_restaurant || null);

// Props
const allRestaurants = (page.props as any).all_restaurants || [];

const toggleMenu = (key: string) => {
    openMenus.value[key] = !openMenus.value[key];
    if (typeof window !== 'undefined' && window.localStorage) {
        localStorage.setItem('sidebarMenuState', JSON.stringify(openMenus.value));
    }
};

const userName = computed(() => (page.props.auth as any)?.user?.name || 'User');
const userEmail = computed(() => (page.props.auth as any)?.user?.email || 'user@example.com');
const userAvatarUrl = computed(() => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}&background=4F46E5&color=fff&bold=true`;
});

// Toast state
const toastMessage = ref('');
const toastTitle = ref('');
const toastType = ref('info');
const toastTrigger = ref(0);

// Switch Restaurant
const switchRestaurant = (restaurant: any) => {
    selectedRestaurant.value = restaurant;
    isRestaurantMenuOpen.value = false;
    
    // Show success toast
    toastMessage.value = `Switched context to ${restaurant.name}`;
    toastTitle.value = 'Success';
    toastType.value = 'success';
    toastTrigger.value++;
    
    router.get(route('admin.dashboard'), { restaurant_id: restaurant.id }, {
        preserveState: false, // We want to reload stats
        preserveScroll: true
    });
};

// Watch for flash messages
watch(() => page.props.flash, (flash: any) => {
    if (flash?.message || flash?.success) {
        toastMessage.value = flash.message || flash.success;
        toastTitle.value = 'Success';
        toastType.value = 'success';
        toastTrigger.value++;
    } else if (flash?.error) {
        toastMessage.value = flash.error;
        toastTitle.value = 'Error';
        toastType.value = 'error';
        toastTrigger.value++;
    }
}, { deep: true });

// Get current locale from URL
const currentLocale = computed(() => {
    if (typeof window === 'undefined') return 'en';
    const urlPath = window.location.pathname;
    const match = urlPath.match(/^\/(ar|en)\//);
    return match ? match[1] : 'en';
});

// Switch to specific language
// Switch to specific language
const toggleLanguage = () => {
    // languageMenuOpen.value = false; // No longer needed
    
    const newLocale = currentLocale.value === 'en' ? 'ar' : 'en';

    if (newLocale === currentLocale.value) return;
    
    // Update Vue i18n locale
    locale.value = newLocale;
    
    // Update HTML dir attribute for RTL support
    document.documentElement.setAttribute('dir', newLocale === 'ar' ? 'rtl' : 'ltr');
    
    // Get current route name and params
    const currentUrl = window.location.pathname;
    const newUrl = currentUrl.replace(/^\/(ar|en)\//, `/${newLocale}/`);
    
    // Redirect to new locale
    window.location.href = newUrl;
};

// Set initial direction based on locale
onMounted(() => {
    const dir = currentLocale.value === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.setAttribute('dir', dir);
    locale.value = currentLocale.value;
    
    if ((page.props.flash as any)?.success) {
        toastMessage.value = (page.props.flash as any).success;
        toastTitle.value = 'Success';
        toastType.value = 'success';
        toastTrigger.value++;
    }
    if ((page.props.flash as any)?.error) {
        toastMessage.value = (page.props.flash as any).error;
        toastTitle.value = 'Error';
        toastType.value = 'error';
        toastTrigger.value++;
    }
});
</script>

