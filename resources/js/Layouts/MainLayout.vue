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
            class="fixed inset-y-0 z-50 glass-sidebar shadow-lifted transform transition-all duration-300 ease-in-out lg:translate-x-0 flex flex-col" 
            :class="[
                currentLocale === 'ar' ? 'right-0' : 'left-0',
                isSidebarCollapsed ? 'w-20' : 'w-64',
                isSidebarOpen ? 'translate-x-0' : (currentLocale === 'ar' ? 'translate-x-full' : '-translate-x-full')
            ]"
        >
            <!-- Logo Section with Collapse Button -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100/50 dark:border-gray-700/50">
                <Logo v-if="!isSidebarCollapsed" />
                <div class="flex items-center gap-2">
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
                    <!-- Mobile Close Button -->
                    <button 
                        @click="isSidebarOpen = false"
                        class="lg:hidden p-2 text-gray-500 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Navigation (Scrollable) -->
            <nav class="flex-1 mt-4 px-2 space-y-4 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent hover:scrollbar-thumb-gray-400 pb-20">
                <!-- Dashboard -->
                <div>
                    <div class="px-3 mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider" v-if="!isSidebarCollapsed">
                        {{ $t('nav.general') }}
                    </div>
                    <Link 
                        v-if="hasPermission('view_dashboard')"
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
                <div v-if="hasAnyPermission(['view_orders', 'view_kitchen', 'view_inventory', 'view_waste', 'view_pos'])">
                     <div 
                        @click="toggleMenu('operations')"
                        class="px-3 mb-1 flex items-center justify-between cursor-pointer group" 
                        v-if="!isSidebarCollapsed"
                    >
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">{{ $t('nav.operations') }}</span>
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
                        <div class="space-y-0.5" v-if="hasAnyPermission(['view_orders', 'view_kitchen', 'view_inventory', 'view_waste'])">
                            <button 
                                v-if="hasAnyPermission(['view_orders', 'create_order', 'view_kitchen'])"
                                @click="toggleMenu('operations.orders')"
                                :class="[
                                    'w-full group flex items-center rounded-lg transition-all duration-200',
                                    isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5 justify-between',
                                    ($page.url.includes('/orders') || $page.url.includes('/kitchen')) ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2-2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.orders') }}</span>
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
                                    v-if="hasPermission('view_orders')"
                                    :href="route('orders.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/orders') && !$page.url.includes('create')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    {{ $t('nav.all_orders') }}
                                </Link>
                                <Link 
                                    v-if="hasPermission('create_order')"
                                    :href="route('orders.create')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/orders/create')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ $t('nav.new_order') }}
                                </Link>
                                 <Link 
                                    v-if="hasPermission('view_kitchen') && hasFeature('kds')"
                                    :href="route('kitchen.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/kitchen')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    {{ $t('nav.kitchen_view') }}
                                </Link>
                            </div>
                                <Link 
                                    v-if="hasPermission('view_inventory') && hasFeature('inventory')"
                                    :href="route('inventory.index')"
                                    :class="[
                                        'flex items-center rounded-lg transition-all duration-200',
                                        isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2',
                                        $page.url.includes('/inventory') ? 'text-primary font-medium bg-primary/5' : 'text-gray-500 hover:text-primary hover:bg-primary/5'
                                    ]"
                                    :title="isSidebarCollapsed ? 'Inventory' : ''"
                                >
                                    <svg class="w-4 h-4" :class="!isSidebarCollapsed ? 'mr-2' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <span v-if="!isSidebarCollapsed">{{ $t('nav.inventory') }}</span>
                                </Link>
                                <Link 
                                    v-if="hasPermission('view_waste') && hasFeature('inventory')"
                                    :href="route('waste.index')"
                                    :class="[
                                        'flex items-center rounded-lg transition-all duration-200',
                                        isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2',
                                        $page.url.includes('/waste') ? 'text-primary font-medium bg-primary/5' : 'text-gray-500 hover:text-primary hover:bg-primary/5'
                                    ]"
                                    :title="isSidebarCollapsed ? 'Waste Management' : ''"
                                >
                                    <svg class="w-4 h-4" :class="!isSidebarCollapsed ? 'mr-2' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span v-if="!isSidebarCollapsed">{{ $t('nav.waste_management') }}</span>
                                </Link>
                        </div>

                        <!-- POS -->
                        <Link 
                            v-if="hasPermission('view_pos') && hasFeature('pos')"
                            :href="route('pos.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/pos') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="isSidebarCollapsed ? $t('nav.pos') : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.pos') }}</span>
                        </Link>
                        
                        <!-- Order Delivery (Waiter) -->
                        <Link 
                            v-if="hasPermission('deliver_orders')"
                            :href="route('service.delivery')" 
                            :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/service/delivery') 
                                    ? 'bg-green-100 text-green-700 font-medium dark:bg-green-900/30 dark:text-green-400' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="isSidebarCollapsed ? 'Order Delivery' : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.order_delivery') }}</span>
                        </Link>

                        <!-- Customer Status Screen -->
                        <Link 
                            v-if="hasPermission('view_order_status_screen') && hasFeature('kds')"
                            :href="route('orders.status-screen')" 
                            :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/orders/status/screen') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="isSidebarCollapsed ? 'Status Screen' : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.status_screen') }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Management Section -->
                <div v-if="hasAnyPermission(['view_menu', 'view_tables', 'view_staff'])">
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
                        <div class="space-y-0.5" v-if="hasAnyPermission(['view_menu', 'view_tables'])">
                             <button 
                                @click="toggleMenu('management.restaurant')"
                                :class="[
                                    'w-full group flex items-center rounded-lg transition-all duration-200',
                                    isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5 justify-between',
                                    ($page.url.includes('/menu') || $page.url.includes('/tables')) ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                ]"
                                :title="isSidebarCollapsed ? 'Restaurant' : ''"
                            >
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.restaurant') }}</span>
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
                                    v-if="hasPermission('view_menu')"
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
                                    v-if="hasPermission('view_tables') && hasFeature('qr_ordering')"
                                    :href="route('tables.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/tables')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    {{ $t('nav.tables') }}
                                </Link>
                                <Link 
                                    v-if="hasPermission('customize_receipt_template')"
                                    :href="route('settings.receipt-template')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/settings/receipt-template')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $t('nav.receipt_template') }}
                                </Link>
                            </div>
                        </div>

                        <!-- Staff -->
                        <Link 
                            v-if="hasPermission('view_staff')"
                            :href="route('staff.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/staff') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="isSidebarCollapsed ? $t('nav.staff') : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.staff') }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Growth / Marketing Section -->
                <div v-if="hasAnyPermission(['view_customers', 'view_loyalty', 'view_delivery_settings', 'view_communication', 'view_sales_reports', 'view_expense_reports'])">
                     <div 
                        @click="toggleMenu('growth')"
                        class="px-3 mb-1 flex items-center justify-between cursor-pointer group" 
                        v-if="!isSidebarCollapsed"
                    >
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">{{ $t('nav.growth') }}</span>
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
                        <!-- Customers -->
                        <Link 
                            v-if="hasPermission('view_customers')"
                            :href="route('customers.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/customers') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="isSidebarCollapsed ? 'Customers' : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.customers') }}</span>
                        </Link>
                        
                         <!-- Sub-Group: Loyalty -->
                        <div class="space-y-0.5" v-if="hasPermission('view_loyalty') && hasFeature('loyalty')">
                             <button 
                                @click="toggleMenu('growth.loyalty')"
                                :class="[
                                    'w-full group flex items-center rounded-lg transition-all duration-200',
                                    isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5 justify-between',
                                    $page.url.includes('/loyalty') ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                ]"
                                :title="isSidebarCollapsed ? $t('nav.loyalty') : ''"
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
                                    v-if="hasPermission('view_loyalty')"
                                    :href="route('loyalty.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/loyalty') && !$page.url.includes('earning-methods')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    {{ $t('nav.overview') }}
                                </Link>
                                <Link 
                                    v-if="hasPermission('manage_earning_rules')"
                                    :href="route('loyalty.earning-methods.index')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/earning-methods')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $t('nav.earning_methods') }}
                                </Link>
                                <Link 
                                    v-if="hasPermission('view_sms_logs')"
                                    :href="route('loyalty.sms-logs')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/sms-logs')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                    {{ $t('nav.sms_logs') }}
                                </Link>
                            </div>
                        </div>

                        <!-- Delivery Integrations -->
                        <Link 
                            v-if="hasPermission('view_delivery_settings') && hasFeature('delivery')"
                            :href="route('integrations.delivery.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/integrations/delivery') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="isSidebarCollapsed ? 'Delivery Integrations' : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.delivery_integrations') }}</span>
                        </Link>

                        <!-- Feedback -->
                        <Link 
                            v-if="hasPermission('view_feedback') && hasFeature('feedback')"
                            :href="route('feedback.index')" 
                            :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/feedback') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="isSidebarCollapsed ? 'Feedback' : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363 1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.feedback') }}</span>
                        </Link>

                        <!-- Communication -->
                        <Link 
                            v-if="hasPermission('view_communication') && hasFeature('marketing')"
                            :href="route('communication.index')" 
                           :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                $page.url.includes('/communication') 
                                    ? 'bg-primary/10 text-primary font-medium' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="isSidebarCollapsed ? 'Communication' : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.communication') }}</span>
                        </Link>

                        <!-- Financial (Sub-Group) -->
                        <div class="space-y-0.5" v-if="hasAnyPermission(['view_sales_reports', 'view_expense_reports']) && hasFeature('analytics')">
                            <button 
                                @click="toggleMenu('growth.financial')"
                                :class="[
                                    'w-full group flex items-center rounded-lg transition-all duration-200',
                                    isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5 justify-between',
                                    ($page.url.includes('/reports') || $page.url.includes('/monthly-expenses')) ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                ]"
                                :title="isSidebarCollapsed ? 'Financial' : ''"
                            >
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.financial') }}</span>
                                </div>
                                <svg 
                                    v-if="!isSidebarCollapsed"
                                    class="w-2.5 h-2.5 text-gray-400 transition-transform duration-200" 
                                    :class="openMenus['growth.financial'] ? 'rotate-180' : ''"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <div v-show="openMenus['growth.financial'] && !isSidebarCollapsed" class="pl-9 space-y-0.5">
                                <Link 
                                    v-if="hasPermission('view_expense_reports')"
                                    :href="route('monthly-expenses.index')"
                                    :class="[
                                        'flex items-center rounded-lg transition-all duration-200',
                                        isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2',
                                        $page.url.includes('/monthly-expenses') ? 'text-primary font-medium bg-primary/5' : 'text-gray-500 hover:text-primary hover:bg-primary/5'
                                    ]"
                                    :title="isSidebarCollapsed ? 'Monthly Expenses' : ''"
                                >
                                    <svg class="w-4 h-4" :class="!isSidebarCollapsed ? 'mr-2' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span v-if="!isSidebarCollapsed">{{ $t('nav.monthly_expenses') }}</span>
                                </Link>
                                <Link 
                                    v-if="hasPermission('view_sales_reports')"
                                    :href="route('reports.sales')"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-primary hover:bg-primary/5 transition-colors"
                                    :class="{'text-primary font-medium bg-primary/5': $page.url.includes('/reports')}"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $t('nav.sales_reports') }}
                                </Link>
                            </div>
                        </div>

                        <!-- System Section -->
                        <div v-if="hasAnyPermission(['manage_billing'])">
                            <div class="px-3 mb-1 mt-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider" v-if="!isSidebarCollapsed">
                                {{ $t('nav.system') }}
                            </div>
                            
                            <Link 
                                v-if="hasPermission('manage_billing')"
                                :href="route('plans.index')"
                                :class="[
                                    'group flex items-center rounded-lg transition-all duration-200',
                                    isSidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2.5',
                                    $page.url.includes('/plans') 
                                        ? 'bg-primary/10 text-primary font-medium' 
                                        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                                ]"
                                :title="isSidebarCollapsed ? 'Billing' : ''"
                            >
                                <svg class="w-5 h-5 flex-shrink-0" :class="!isSidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span class="text-sm" v-if="!isSidebarCollapsed">{{ $t('nav.billing') }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>


        </aside>

        <!-- Main Content -->
        <div class="min-h-screen flex flex-col transition-all duration-300" :class="[
            currentLocale === 'ar' ? (isSidebarCollapsed ? 'lg:mr-20' : 'lg:mr-64') : (isSidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64')
        ]">
            <!-- Header -->
            <header class="glass sticky top-0 z-40 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 border-b border-gray-100/50 dark:border-gray-700/50">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = !isSidebarOpen" class="lg:hidden p-2 text-gray-500 hover:text-primary hover:bg-gray-100 rounded-lg transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <!-- Restaurant Logo or Default Logo -->
                    <div class="flex items-center gap-3">
                        <img 
                            v-if="currentRestaurantLogo" 
                            :src="currentRestaurantLogo" 
                            :alt="currentRestaurantName"
                            class="h-10 w-auto object-contain rounded-lg"
                        />
                        <Logo v-else class="lg:hidden scale-90 origin-left" />
                        <div v-if="currentRestaurantLogo && userRestaurants.length === 1" class="hidden sm:block">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ currentRestaurantName }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3" :class="currentLocale === 'ar' ? 'space-x-reverse' : ''">
                    <!-- Restaurant Switcher (for users with multiple restaurants) -->
                    <div class="relative group" v-if="userRestaurants && userRestaurants.length > 1">
                        <button
                            @click="isRestaurantMenuOpen = !isRestaurantMenuOpen"
                            class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all shadow-sm"
                        >
                            <img 
                                v-if="currentRestaurantLogo" 
                                :src="currentRestaurantLogo" 
                                :alt="currentRestaurantName"
                                class="w-5 h-5 object-contain rounded"
                            />
                            <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="max-w-[80px] sm:max-w-[150px] truncate">{{ currentRestaurantName }}</span>
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
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
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-primary/5 hover:text-primary transition-colors flex items-center gap-3 group"
                            >
                                <img 
                                    v-if="restaurant.logo" 
                                    :src="restaurant.logo" 
                                    :alt="restaurant.name"
                                    class="w-8 h-8 object-contain rounded flex-shrink-0"
                                />
                                <div 
                                    v-else 
                                    class="w-8 h-8 bg-primary/10 rounded flex items-center justify-center flex-shrink-0"
                                >
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <span class="flex-1">{{ restaurant.name }}</span>
                                <svg v-if="currentRestaurantId === restaurant.id" class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Language Switcher Dropdown -->
                    <div class="relative">
                        <button 
                            @click="languageMenuOpen = !languageMenuOpen"
                            class="flex items-center gap-2 px-3 sm:px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 border border-gray-200 dark:border-gray-600 hover:border-primary/50"
                        >
                            <!-- Current Language Flag -->
                            <div class="flex items-center gap-2">
                                <div v-if="currentLocale === 'en'" class="w-5 h-5 rounded-full overflow-hidden border border-gray-300 flex items-center justify-center bg-blue-600">
                                    <svg class="w-6 h-6" viewBox="0 0 60 30" xmlns="http://www.w3.org/2000/svg">
                                        <clipPath id="s"><path d="M0,0 v30 h60 v-30 z"/></clipPath>
                                        <clipPath id="t"><path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/></clipPath>
                                        <g clip-path="url(#s)"><path d="M0,0 v30 h60 v-30 z" fill="#012169"/><path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/><path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#t)" stroke="#C8102E" stroke-width="4"/><path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/><path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/></g>
                                    </svg>
                                </div>
                                <div v-else class="w-5 h-5 rounded-full overflow-hidden border border-gray-300 flex items-center justify-center bg-green-600">
                                    <svg class="w-6 h-6" viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="900" height="600" fill="#165B33"/>
                                        <g fill="#fff">
                                            <path d="M450,150 L450,450 M300,300 L600,300" stroke="#fff" stroke-width="60"/>
                                            <circle cx="450" cy="300" r="60" fill="none" stroke="#fff" stroke-width="20"/>
                                        </g>
                                    </svg>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold">{{ currentLocale === 'ar' ? 'العربية' : 'English' }}</span>
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
                                v-if="languageMenuOpen"
                                class="absolute mt-2 w-48 glass-card rounded-xl shadow-lifted py-2 z-50"
                                :class="currentLocale === 'ar' ? 'left-0' : 'right-0'"
                            >
                                <button 
                                    @click="switchLanguage('en')"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors rounded-lg"
                                    :class="currentLocale === 'en' ? 'text-primary font-semibold bg-primary/5' : 'text-gray-700 dark:text-gray-300'"
                                >
                                    <!-- UK Flag -->
                                    <div class="w-6 h-6 rounded-full overflow-hidden border-2 border-gray-200 flex-shrink-0">
                                        <svg class="w-full h-full" viewBox="0 0 60 30" xmlns="http://www.w3.org/2000/svg">
                                            <clipPath id="s"><path d="M0,0 v30 h60 v-30 z"/></clipPath>
                                            <clipPath id="t"><path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/></clipPath>
                                            <g clip-path="url(#s)"><path d="M0,0 v30 h60 v-30 z" fill="#012169"/><path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/><path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#t)" stroke="#C8102E" stroke-width="4"/><path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/><path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/></g>
                                        </svg>
                                    </div>
                                    <span class="flex-1 text-left font-medium">English</span>
                                    <svg v-if="currentLocale === 'en'" class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                                <button 
                                    @click="switchLanguage('ar')"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors rounded-lg"
                                    :class="currentLocale === 'ar' ? 'text-primary font-semibold bg-primary/5' : 'text-gray-700 dark:text-gray-300'"
                                >
                                    <!-- Saudi Arabia Flag -->
                                    <div class="w-6 h-6 rounded-full overflow-hidden border-2 border-gray-200 flex-shrink-0">
                                        <svg class="w-full h-full" viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="900" height="600" fill="#165B33"/>
                                            <g fill="#fff">
                                                <text x="450" y="320" font-size="180" text-anchor="middle" font-family="Arial" font-weight="bold">العربية</text>
                                                <path d="M350,400 L550,400 M450,350 L450,450" stroke="#fff" stroke-width="25"/>
                                            </g>
                                        </svg>
                                    </div>
                                    <span class="flex-1 text-right font-medium" dir="rtl">العربية</span>
                                    <svg v-if="currentLocale === 'ar'" class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
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
                            class="flex items-center gap-2 sm:gap-3 p-1 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                        >
                            <img 
                                class="h-8 w-8 sm:h-9 sm:w-9 rounded-xl object-cover ring-2 ring-primary/10" 
                                :src="userAvatarUrl" 
                                alt="User avatar" 
                            />
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ userName }}</p>
                                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">{{ userRole }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                class="absolute right-0 mt-2 w-64 glass-card rounded-xl shadow-lifted py-2 z-50 overflow-hidden"
                            >
                                <!-- Subscription Info Section -->
                                <div class="px-2 pb-2 border-b border-gray-100 dark:border-gray-700">
                                    <Link 
                                        :href="route('plans.index')"
                                        class="block group"
                                    >
                                        <div 
                                            v-if="currentSubscription"
                                            class="px-3 py-3 rounded-xl bg-gradient-to-r from-primary/5 to-primary/10 hover:from-primary/10 hover:to-primary/15 transition-all duration-200 cursor-pointer border border-primary/20 hover:border-primary/30"
                                        >
                                            <div class="flex items-start gap-3">
                                                <div class="p-2 bg-primary/10 rounded-lg group-hover:bg-primary/20 transition-colors">
                                                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ currentSubscription.plan?.name || 'No Plan' }}</p>
                                                        <svg class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </div>
                                                    <p class="text-[10px] text-gray-600 dark:text-gray-400 mb-1">
                                                        <span class="font-medium">{{ subscriptionStatus }}</span>
                                                    </p>
                                                    <div class="flex items-center gap-1 text-[10px] text-gray-500">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <span>Expires {{ formatExpiryDate(currentSubscription.ends_at) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div 
                                            v-else
                                            class="px-3 py-3 rounded-xl bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 hover:from-gray-100 hover:to-gray-200 dark:hover:from-gray-600 dark:hover:to-gray-500 transition-all duration-200 cursor-pointer text-center"
                                        >
                                            <p class="text-xs font-semibold text-gray-900 dark:text-white">No Active Plan</p>
                                            <p class="text-[10px] text-primary mt-1 font-medium">Click to choose a plan</p>
                                        </div>
                                    </Link>
                                </div>
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ userName }}</p>
                                    <p class="text-xs text-gray-500">{{ userEmail }}</p>
                                </div>
                                <div class="py-1">
                                    <Link :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $t('auth.profile') }}
                                    </Link>
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
                                        as="button"
                                        class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
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

            <!-- Page Header -->
            <header class="bg-white dark:bg-gray-800 shadow mb-6" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 animate-fade-in">
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
import { ref, computed, Transition, onMounted, watch, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Logo from '@/Components/Logo.vue';
import Toast from '@/Components/Toast.vue';
import { usePermissions } from '@/Composables/usePermissions';
import { useFeatures } from '@/Composables/useFeatures';

const { hasPermission, hasAnyPermission } = usePermissions();
const { hasFeature } = useFeatures();

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
        'growth.loyalty': false,
        'growth.financial': false
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
const userRole = computed(() => {
    const role = (page.props.auth as any)?.user?.role || 'User';
    return role.charAt(0).toUpperCase() + role.slice(1);
});
const userAvatarUrl = computed(() => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}&background=4F46E5&color=fff&bold=true`;
});

// Restaurant Switcher
const userRestaurants = computed(() => (page.props as any).user_restaurants || []);
const currentRestaurantId = computed(() => (page.props as any).active_restaurant_id || null);
const currentRestaurantName = computed(() => {
    const restaurant = userRestaurants.value.find((r: any) => r.id === currentRestaurantId.value);
    return restaurant?.name || 'Select Restaurant';
});

const currentRestaurantLogo = computed(() => {
    const restaurant = userRestaurants.value.find((r: any) => r.id === currentRestaurantId.value);
    return restaurant?.logo || null;
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

// Subscription Management
const currentSubscription = computed(() => (page.props as any).current_subscription || null);

const subscriptionStatus = computed(() => {
    if (!currentSubscription.value) return 'No active plan';
    
    const days = daysUntilExpiry(currentSubscription.value.ends_at);
    if (days <= 0) return 'Expired';
    if (days <= 7) return `Expiring soon`;
    return 'Active';
});

const formatExpiryDate = (date: string) => {
    const expiryDate = new Date(date);
    const now = new Date();
    const diffDays = Math.ceil((expiryDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) return 'Expired';
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    if (diffDays <= 7) return `in ${diffDays} days`;
    
    return expiryDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const daysUntilExpiry = (endDate: string) => {
    const end = new Date(endDate);
    const now = new Date();
    return Math.ceil((end.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
};

// Set initial direction based on locale
onMounted(() => {
    const dir = currentLocale.value === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.setAttribute('dir', dir);
    locale.value = currentLocale.value;

    window.addEventListener('notify', (event: any) => {
        const { message, title, type } = event.detail;
        toastMessage.value = message;
        toastTitle.value = title || (type === 'error' ? 'Error' : 'Success');
        toastType.value = type || 'success';
        toastTrigger.value++;
    });
});
// Global Notification Logic
const pollInterval = ref<any>(null);

onMounted(async () => {
    // Only polling for users with delivery permission
    if (hasPermission('deliver_orders')) {
        const checkForOrders = async () => {
            try {
                const res = await fetch(route('service.delivery.check'));
                if (!res.ok) return;
                
                const data = await res.json();
                const currentIds = new Set(data.ids as string[]);
                
                // Load known IDs
                let knownIds = new Set<string>();
                try {
                     const stored = localStorage.getItem('known_ready_orders');
                     if (stored) knownIds = new Set(JSON.parse(stored));
                } catch (e) {}
                
                let hasNew = false;
                for (const id of currentIds) {
                    if (!knownIds.has(id)) {
                        hasNew = true;
                        break;
                    }
                }

                if (hasNew) {
                    // Vibrate
                    if (typeof navigator !== 'undefined' && navigator.vibrate) {
                        try {
                            navigator.vibrate([200, 100, 200, 100, 200]);
                        } catch(e) {}
                    }
                    
                    // Show Toast (DISABLED to prevent spamming)
                    // toastType.value = 'success';
                    // toastTitle.value = 'Order Ready';
                    // toastMessage.value = 'New orders are ready for pickup!';
                    // toastTrigger.value++;
                }
                
                // Save current state
                localStorage.setItem('known_ready_orders', JSON.stringify([...currentIds]));

            } catch (e) {}
        };

        // Initial check
        checkForOrders();

        // Start polling
        pollInterval.value = setInterval(checkForOrders, 3000);
    }
});

onUnmounted(() => {
    if (pollInterval.value) clearInterval(pollInterval.value);
});
</script>

