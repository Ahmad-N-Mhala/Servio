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
            <nav class="mt-4 px-2 space-y-4">
                <!-- General Section -->
                <div>
                    <div class="px-3 mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider" v-if="!isSidebarCollapsed">
                        General
                    </div>
                     <Link 
                        :href="route('dashboard')" 
                        :class="[
                            'group flex items-center rounded-lg transition-all duration-200',
                            isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                            $page.url.includes('/dashboard') 
                                ? 'bg-primary/10 text-primary font-medium' 
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                        ]"
                        :title="isSidebarCollapsed ? $t('nav.dashboard') : ''"
                    >
                        <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.dashboard') }}</span>
                    </Link>
                </div>

                <!-- Operations Section -->
                <div>
                     <div 
                        @click="toggleMenu('operations')"
                        class="px-3 mb-1 flex items-center justify-between cursor-pointer group" 
                        v-if="!isSidebarCollapsed"
                    >
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">Operations</span>
                        <svg 
                            class="w-3 h-3 text-gray-400 transition-transform duration-200" 
                            :class="openMenus['operations'] ? 'rotate-180' : ''"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div v-else class="h-px bg-gray-200 dark:bg-gray-700 mx-3 my-2"></div>

                    <div v-show="isSidebarCollapsed || openMenus['operations']" class="space-y-0.5">
                        <!-- Sub-Group: Orders -->
                        <div class="space-y-0.5">
                             <button 
                                @click="toggleMenu('operations.orders')"
                                :class="[
                                    'w-full group flex items-center rounded-lg transition-all duration-200',
                                    isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5 justify-between',
                                    ($page.url.includes('/orders') || $page.url.includes('/kitchen')) ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span class="text-sm" v-if="!isSidebarCollapsed">Orders</span>
                                </div>
                                <svg 
                                    v-if="!isSidebarCollapsed"
                                    class="w-2.5 h-2.5 text-gray-400 transition-transform duration-200" 
                                    :class="openMenus['operations.orders'] ? 'rotate-180' : ''"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Sub-Sub Items: Orders -->
                             <div v-show="openMenus['operations.orders'] && !isSidebarCollapsed" class="pl-9 space-y-0.5">
                                <Link 
                                    :href="route('orders.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/orders') && !$page.url.includes('create')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    All Orders
                                </Link>
                                <Link 
                                    :href="route('orders.create')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/orders/create')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Order
                                </Link>
                                 <Link 
                                    :href="route('kitchen.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/kitchen')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    Kitchen View
                                </Link>
                            </div>
                        </div>

                        <!-- POS -->
                        <Link 
                            :href="route('pos.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/pos') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 36v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.pos') }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Management Section -->
                <div>
                     <div 
                        @click="toggleMenu('management')"
                        class="px-3 mb-1 flex items-center justify-between cursor-pointer group" 
                        v-if="!isSidebarCollapsed"
                    >
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">{{ $t('nav.management') }}</span>
                        <svg 
                            class="w-3 h-3 text-gray-400 transition-transform duration-200" 
                            :class="openMenus['management'] ? 'rotate-180' : ''"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div v-else class="h-px bg-gray-200 dark:bg-gray-700 mx-3 my-2"></div>

                    <div v-show="isSidebarCollapsed || openMenus['management']" class="space-y-0.5">
                         <!-- Sub-Group: Restaurant -->
                        <div class="space-y-0.5">
                             <button 
                                @click="toggleMenu('management.restaurant')"
                                :class="[
                                    'w-full group flex items-center rounded-lg transition-all duration-200',
                                    isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5 justify-between',
                                    ($page.url.includes('/menu') || $page.url.includes('/tables')) ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="text-sm" v-if="!isSidebarCollapsed">Restaurant</span>
                                </div>
                                <svg 
                                    v-if="!isSidebarCollapsed"
                                    class="w-2.5 h-2.5 text-gray-400 transition-transform duration-200" 
                                    :class="openMenus['management.restaurant'] ? 'rotate-180' : ''"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                             <div v-show="openMenus['management.restaurant'] && !isSidebarCollapsed" class="pl-9 space-y-0.5">
                                <Link 
                                    :href="route('menu.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/menu')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    {{ $t('nav.menu') }}
                                </Link>
                                <Link 
                                    :href="route('tables.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/tables')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    {{ $t('nav.tables') }}
                                </Link>
                            </div>
                        </div>

                        <!-- Staff -->
                        <Link 
                            :href="route('staff.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/staff') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.staff') }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Growth / Marketing Section -->
                <div>
                     <div 
                        @click="toggleMenu('growth')"
                        class="px-3 mb-1 flex items-center justify-between cursor-pointer group" 
                        v-if="!isSidebarCollapsed"
                    >
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">Growth</span>
                        <svg 
                            class="w-3 h-3 text-gray-400 transition-transform duration-200" 
                            :class="openMenus['growth'] ? 'rotate-180' : ''"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div v-else class="h-px bg-gray-200 dark:bg-gray-700 mx-3 my-2"></div>

                    <div v-show="isSidebarCollapsed || openMenus['growth']" class="space-y-0.5">
                        
                         <!-- Sub-Group: Loyalty -->
                        <div class="space-y-0.5">
                             <button 
                                @click="toggleMenu('growth.loyalty')"
                                :class="[
                                    'w-full group flex items-center rounded-lg transition-all duration-200',
                                    isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5 justify-between',
                                    $page.url.includes('/loyalty') ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.loyalty') }}</span>
                                </div>
                                <svg 
                                    v-if="!isSidebarCollapsed"
                                    class="w-2.5 h-2.5 text-gray-400 transition-transform duration-200" 
                                    :class="openMenus['growth.loyalty'] ? 'rotate-180' : ''"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                             <div v-show="openMenus['growth.loyalty'] && !isSidebarCollapsed" class="pl-9 space-y-0.5">
                                <Link 
                                    :href="route('loyalty.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/loyalty') && !$page.url.includes('earning-methods')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Overview
                                </Link>
                                <Link 
                                    :href="route('loyalty.earning-methods.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/earning-methods')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $t('nav.earning_methods') }}
                                </Link>
                            </div>
                        </div>

                        <!-- Delivery Integrations -->
                        <Link 
                            :href="route('integrations.delivery.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/integrations/delivery') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Delivery Integrations</span>
                        </Link>

                        <!-- Communication -->
                        <Link 
                            :href="route('communication.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/communication') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">Communication</span>
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

                <div class="flex items-center ml-auto gap-3" :class="currentLocale === 'ar' ? 'space-x-reverse space-x-3' : 'space-x-3'">
                    <!-- Restaurant Switcher (for users with multiple restaurants) -->
                    <div class="relative group" v-if="userRestaurants && userRestaurants.length > 1">
                        <button
                            @click="isRestaurantMenuOpen = !isRestaurantMenuOpen"
                            class="flex items-center space-x-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all shadow-sm"
                        >
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ currentRestaurantName }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <!-- Dropdown -->
                        <div v-if="isRestaurantMenuOpen" class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 border-b border-gray-50">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Switch Restaurant</p>
                            </div>
                            <button
                                v-for="restaurant in userRestaurants"
                                :key="restaurant.id"
                                @click="switchRestaurant(restaurant)"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-primary/5 hover:text-primary transition-colors flex items-center justify-between group"
                            >
                                <span>{{ restaurant.name }}</span>
                                <svg v-if="currentRestaurantId === restaurant.id" class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Language Switcher Dropdown -->
                    <div class="relative">
                        <button 
                            @click="languageMenuOpen = !languageMenuOpen"
                            class="flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 border border-gray-200 dark:border-gray-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                            </svg>
                            <span class="text-sm font-medium">{{ currentLocale === 'ar' ? 'العربية' : 'English' }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                v-if="languageMenuOpen"
                                class="absolute mt-2 w-48 glass-card rounded-xl shadow-lifted py-2 z-50"
                                :class="currentLocale === 'ar' ? 'left-0' : 'right-0'"
                            >
                                <button 
                                    @click="switchLanguage('en')"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    :class="currentLocale === 'en' ? 'text-primary font-semibold bg-primary/5' : 'text-gray-700 dark:text-gray-300'"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="flex-1 text-left">English</span>
                                    <span v-if="currentLocale === 'en'" class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded">Active</span>
                                </button>
                                <button 
                                    @click="switchLanguage('ar')"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    :class="currentLocale === 'ar' ? 'text-primary font-semibold bg-primary/5' : 'text-gray-700 dark:text-gray-300'"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="flex-1 text-right" dir="rtl">العربية</span>
                                    <span v-if="currentLocale === 'ar'" class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded">نشط</span>
                                </button>
                            </div>
                        </Transition>
                    </div>

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
                                    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $t('auth.profile') }}
                                    </a>
                                    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $t('auth.settings') }}
                                    </a>
                                    <a :href="route('restaurants.index')" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        Multi-Store Overview
                                    </a>
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
import { ref, computed, Transition, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Logo from '@/Components/Logo.vue';
import Toast from '@/Components/Toast.vue';

const isSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);
const userMenuOpen = ref(false);
const languageMenuOpen = ref(false);
const isRestaurantMenuOpen = ref(false);
const getInitialMenuState = () => {
    const defaults = {
        'management': true,
        'management.restaurant': false,
        'operations': true,
        'operations.orders': false,
        'growth': true,
        'growth.loyalty': false
    };

    if (typeof window !== 'undefined' && window.localStorage) {
        const stored = localStorage.getItem('sidebarMenuState');
        if (stored) {
            try {
                return { ...defaults, ...JSON.parse(stored) };
            } catch (e) {
                console.warn('Failed to load sidebar state', e);
            }
        }
    }
    return defaults;
};

const openMenus = ref<Record<string, boolean>>(getInitialMenuState());
const page = usePage();
const route = (window as any).route;
const { locale } = useI18n();

const toggleMenu = (key: string) => {
    openMenus.value[key] = !openMenus.value[key];
    if (typeof window !== 'undefined' && window.localStorage) {
        localStorage.setItem('sidebarMenuState', JSON.stringify(openMenus.value));
    }
};

const userName = computed(() => (page.props.auth as any)?.user?.name || 'User');
const userEmail = computed(() => (page.props.auth as any)?.user?.email || 'user@example.com');
const userAvatarUrl = computed(() => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}&background=FF6B35&color=fff&bold=true`;
});

// Restaurant Switcher
const userRestaurants = computed(() => (page.props as any).user_restaurants || []);
const currentRestaurantId = computed(() => (page.props as any).active_restaurant_id || null);
const currentRestaurantName = computed(() => {
    const restaurant = userRestaurants.value.find((r: any) => r.id === currentRestaurantId.value);
    return restaurant?.name || 'Select Restaurant';
});

const switchRestaurant = (restaurant: any) => {
    isRestaurantMenuOpen.value = false;
    
    // Show success toast
    toastMessage.value = `Switched to ${restaurant.name}`;
    toastTitle.value = 'Success';
    toastType.value = 'success';
    toastTrigger.value++;
    
    // Update session and reload
    fetch(route('restaurants.switch', { restaurant: restaurant.id }), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
        }
    }).then(() => {
        window.location.reload();
    });
};

// Toast state
const toastMessage = ref('');
const toastTitle = ref('');
const toastType = ref('info');
const toastTrigger = ref(0);

// Watch for flash messages
watch(() => page.props.flash, (flash: any) => {
    if (flash?.message) {
        toastMessage.value = flash.message;
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
    const urlPath = window.location.pathname;
    const match = urlPath.match(/^\/(ar|en)\//);
    return match ? match[1] : 'en';
});

// Switch to specific language
const switchLanguage = (newLocale: string) => {
    languageMenuOpen.value = false;
    
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
});
</script>

