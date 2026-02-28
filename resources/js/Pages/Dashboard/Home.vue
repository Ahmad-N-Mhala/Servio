<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Header -->
            <div class="mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
                <div class="space-y-1">
                    <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">{{ $t('dashboard.welcome') }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">{{ $t('dashboard.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-3 w-full lg:w-auto">
                    <a 
                        :href="route('dashboard.export', { start_date: dateRange.start_date, end_date: dateRange.end_date, format: 'excel', tab: currentTab })"
                        target="_blank"
                        class="flex-1 lg:flex-none"
                    >
                         <Button variant="secondary" class="w-full bg-white/80 backdrop-blur-md border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-2.5 shadow-sm rounded-xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span class="font-bold">{{ $t('inventory.download_excel') }}</span>
                         </Button>
                    </a>
                    <a 
                        :href="route('dashboard.export', { start_date: dateRange.start_date, end_date: dateRange.end_date, format: 'pdf', tab: currentTab })"
                        target="_blank"
                        class="flex-1 lg:flex-none"
                    >
                        <Button variant="primary" class="w-full bg-primary text-white hover:bg-primary-hover px-5 py-2.5 shadow-lg shadow-primary/20 rounded-xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="font-bold">{{ $t('common.export') }} PDF</span>
                        </Button>
                    </a>
                </div>
            </div>

            <!-- Date Range Picker -->
            <DateRangePicker
                :initial-start-date="dateRange.start_date"
                :initial-end-date="dateRange.end_date"
                @update="onDateRangeUpdate"
                class="mb-6"
            />

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        @click="switchTab('overview')"
                        :class="[
                            currentTab === 'overview'
                                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        {{ $t('dashboard.overview') }}
                    </button>
                    <button
                        @click="switchTab('item_sales')"
                        :class="[
                            currentTab === 'item_sales'
                                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        {{ $t('dashboard.item_sales') }}
                    </button>
                </nav>
            </div>

            <!-- Overview Content -->
            <div v-if="currentTab === 'overview'" class="space-y-8">
                
                <!-- 1. Highlights Section -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                         <div class="flex items-center gap-2">
                             <div class="w-1 h-6 bg-primary rounded-full"></div>
                             <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $t('dashboard.highlights') }}</h2>
                         </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <StatsCard
                            :title="$t('dashboard.sales')"
                            :value="formatCurrency(highlights.sales)"
                            icon="revenue"
                            color="green"
                            @click="fetchDetails('selection_sales')"
                            class="cursor-pointer"
                        />
                        <StatsCard
                            :title="$t('dashboard.total_orders')"
                            :value="highlights.orders"
                            icon="orders"
                            color="blue"
                            @click="fetchDetails('total_orders')"
                            class="cursor-pointer"
                        />
                         <StatsCard
                            :title="$t('dashboard.customers')"
                            :value="highlights.customers"
                            :subtitle="`${highlights.new_customers} new / ${highlights.repeat_customers} repeat`"
                            icon="customers"
                            color="purple"
                            @click="fetchDetails('new_customers')"
                            class="cursor-pointer"
                        />
                         <StatsCard
                            :title="$t('dashboard.rewards_redeemed')"
                            :value="highlights.rewards_redeemed"
                            icon="gift"
                            color="yellow"
                            @click="fetchDetails('rewards_redeemed')"
                            class="cursor-pointer"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <ChartCard :title="$t('dashboard.period_sales')" :subtitle="formatCurrency(periodSales.total)" height="350px">
                        <template #icon>
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </template>
                        <canvas ref="revenueChartCanvas"></canvas>
                    </ChartCard>
                    <ChartCard :title="$t('dashboard.period_visits')" :subtitle="periodVisits.total.toString()" height="350px">
                        <template #icon>
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </template>
                        <canvas ref="visitsChartCanvas"></canvas>
                    </ChartCard>
                </div>

                <!-- 3. Distribution & Trends -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ChartCard :title="$t('dashboard.payment_methods')" height="350px">
                        <template #icon>
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </template>
                        <canvas ref="paymentChartCanvas"></canvas>
                    </ChartCard>
                    <ChartCard :title="$t('dashboard.order_status')" height="350px">
                        <template #icon>
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </template>
                         <canvas ref="statusChartCanvas"></canvas>
                    </ChartCard>
                     <ChartCard :title="$t('dashboard.waste_trend')" height="350px">
                        <template #icon>
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </template>
                        <canvas ref="wasteChartCanvas"></canvas>
                    </ChartCard>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <ChartCard :title="$t('dashboard.peak_hours')" height="350px">
                        <template #icon>
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <canvas ref="peakHoursChartCanvas"></canvas>
                    </ChartCard>
                    <ChartCard :title="$t('dashboard.avg_completion_time')" height="350px">
                        <template #icon>
                            <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </template>
                        <canvas ref="completionTimeChartCanvas"></canvas>
                    </ChartCard>
                </div>



                <!-- 4. Top Insights & Popular Times -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Top Insights Grid -->
                    <div class="glass-card rounded-2xl p-8 lg:col-span-2 shadow-xl border border-white/20">
                         <div class="flex items-center gap-3 mb-8">
                             <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                                 <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                 </svg>
                             </div>
                             <h3 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $t('dashboard.top_insights') }}</h3>
                         </div>
                         <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                             <!-- Pareto -->
                             <div class="p-6 rounded-2xl bg-gray-50/50 dark:bg-gray-700/30 border border-gray-100 transition-all hover:bg-white hover:shadow-lg">
                                 <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ $t('dashboard.revenue_distribution') }}</p>
                                 <div class="flex items-end gap-3">
                                     <span class="text-4xl font-black text-emerald-600 tracking-tighter">{{ topInsights.pareto_percent }}%</span>
                                     <span class="text-xs text-gray-400 font-medium mb-1.5 uppercase tracking-tighter">{{ $t('dashboard.from_top_20_percent') }}</span>
                                 </div>
                             </div>
                             <!-- AOV -->
                             <div class="p-6 rounded-2xl bg-gray-50/50 dark:bg-gray-700/30 border border-gray-100 transition-all hover:bg-white hover:shadow-lg">
                                 <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ $t('dashboard.avg_order_value') }}</p>
                                 <div class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ formatCurrency(topInsights.avg_order_value) }}</div>
                             </div>
                             <!-- Avg Items -->
                             <div class="p-6 rounded-2xl bg-gray-50/50 dark:bg-gray-700/30 border border-gray-100 transition-all hover:bg-white hover:shadow-lg">
                                 <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ $t('dashboard.avg_items_per_order') }}</p>
                                 <div class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ topInsights.avg_items_per_order }}</div>
                             </div>
                             <!-- Avg Visits -->
                             <div class="p-6 rounded-2xl bg-gray-50/50 dark:bg-gray-700/30 border border-gray-100 transition-all hover:bg-white hover:shadow-lg">
                                 <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ $t('dashboard.avg_visits_per_year') }}</p>
                                 <div class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ topInsights.avg_visits_per_year }}</div>
                             </div>
                         </div>
                    </div>

                    <!-- Popular Times -->
                    <div class="glass-card rounded-2xl p-8 shadow-xl border border-white/20">
                        <div class="flex items-center gap-3 mb-8">
                             <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                                 <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                 </svg>
                             </div>
                             <h3 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $t('dashboard.popular_times') }}</h3>
                         </div>
                        <div class="space-y-6">
                            <div class="p-5 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100/50 transition-all hover:shadow-md">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">{{ $t('dashboard.most_popular') }}</p>
                                </div>
                                <p v-if="popularTimes.most_popular" class="text-xl font-black text-gray-900 dark:text-white">
                                    {{ $t('dashboard.day_' + popularTimes.most_popular.day) }} {{ $t('dashboard.period_' + popularTimes.most_popular.period) }}
                                </p>
                                <p v-if="popularTimes.most_popular" class="text-xs font-bold text-emerald-600/70 mt-1 uppercase">
                                    {{ popularTimes.most_popular.orders }} {{ $t('dashboard.orders_total') }}
                                </p>
                                <p v-else class="text-sm text-gray-500">N/A</p>
                            </div>
                            <div class="p-5 bg-rose-50 dark:bg-rose-900/20 rounded-2xl border border-rose-100/50 transition-all hover:shadow-md">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ $t('dashboard.least_popular') }}</p>
                                </div>
                                <p v-if="popularTimes.least_popular" class="text-xl font-black text-gray-900 dark:text-white">
                                    {{ $t('dashboard.day_' + popularTimes.least_popular.day) }} {{ $t('dashboard.period_' + popularTimes.least_popular.period) }}
                                </p>
                                <p v-if="popularTimes.least_popular" class="text-xs font-bold text-rose-600/70 mt-1 uppercase">
                                    {{ popularTimes.least_popular.orders }} {{ $t('dashboard.orders_total') }}
                                </p>
                                <p v-else class="text-sm text-gray-500">N/A</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Lists: Customer Frequency, Rewards, Top Items -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Customer Frequency -->
                    <div class="glass-card rounded-2xl p-8 shadow-xl border border-white/20">
                         <div class="flex items-center gap-3 mb-8">
                             <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/30 flex items-center justify-center">
                                 <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                 </svg>
                             </div>
                             <h3 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $t('dashboard.customer_frequency') }}</h3>
                         </div>
                        <div class="space-y-5">
                             <div v-for="(count, label) in customerFrequency" :key="label" 
                                class="flex items-center justify-between cursor-pointer group p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-xl transition-all"
                                @click="fetchDetails('retention_bucket', { range: String(label) })"
                             >
                                 <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider group-hover:text-primary transition-colors">{{ $t('dashboard.visit_' + String(label).replace('+', '_plus').replace('-', '_')) }}</span>
                                 <div class="flex items-center gap-4">
                                     <div class="w-24 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner">
                                         <div class="h-full bg-primary transition-all duration-1000 group-hover:scale-x-105 origin-left" :style="`width: ${Math.min(100, (count / (customerInsights.total || 1)) * 100)}%`"></div>
                                     </div>
                                     <span class="text-sm font-black text-gray-900 dark:text-white w-8 text-right">{{ count }}</span>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- Top Rewards -->
                    <div class="glass-card rounded-2xl p-8 shadow-xl border border-white/20">
                        <div class="flex items-center gap-3 mb-8">
                             <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                                 <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c.57.201 1 .761 1 1.403V19a2 2 0 01-2 2h-1v-4.546M21 15.546c-.57-.201-1.393-.546-2.103-.921a.5.5 0 00-.459.89c.729.387 1.558.732 2.126.931m0-2.4c-.035-.012-.07-.024-.103-.037M5 20l-3-1v-2.339c0-.6.39-.41-1.455.51a.5.5 0 01.444.894C2.445 15.454 4 15.85 5 16.205V20zm0 0V4a2 2 0 012-2h7a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2z" />
                                 </svg>
                             </div>
                             <h3 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $t('dashboard.top_rewards') }}</h3>
                         </div>
                        <div v-if="topRewards.length > 0" class="space-y-4">
                            <div v-for="(reward, idx) in topRewards" :key="idx" class="flex items-center gap-4 p-3 hover:bg-white hover:shadow-lg rounded-2xl border border-transparent transition-all group">
                                <span class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-amber-50 text-amber-600 rounded-xl text-xs font-black group-hover:bg-amber-100 transition-colors">{{ idx + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-gray-900 truncate uppercase tracking-tight">{{ reward.name }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 truncate uppercase mt-0.5">{{ reward.description }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-gray-700">{{ reward.count }}</span>
                                    <p class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">{{ $t('dashboard.used') }}</p>
                                </div>
                            </div>
                        </div>
                         <p v-else class="text-sm text-gray-500 text-center py-4">{{ $t('common.no_results') }}</p>
                    </div>

                    <!-- Top Items -->
                     <div class="glass-card rounded-2xl p-8 shadow-xl border border-white/20">
                        <div class="flex items-center gap-3 mb-8">
                             <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                 <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                 </svg>
                             </div>
                             <h3 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $t('dashboard.top_menu_items') }}</h3>
                         </div>
                        <div v-if="topItems.length > 0" class="space-y-4">
                             <div v-for="(item, idx) in topItems" :key="idx" class="flex items-center gap-4 p-3 hover:bg-white hover:shadow-lg rounded-2xl border border-transparent transition-all group">
                                <span class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl text-xs font-black group-hover:bg-blue-100 transition-colors">{{ idx + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-gray-900 truncate uppercase tracking-tight">{{ item.name }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-gray-700">{{ item.quantity }}</span>
                                    <p class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">{{ $t('dashboard.sold') }}</p>
                                </div>
                            </div>
                        </div>
                         <p v-else class="text-sm text-gray-500 text-center py-4">{{ $t('common.no_results') }}</p>
                    </div>
                </div>

            </div>

             <!-- Item Sales Tab (Existing) -->
            <div v-if="currentTab === 'item_sales'" class="space-y-6">
                <!-- Table -->
                <Table
                    :columns="itemSalesColumns"
                    :data="itemSalesList"
                    :pagination="itemSalesPagination"
                    v-model:search="searchQuery"
                    :title="$t('dashboard.item_sales_report')"
                    :empty-message="$t('common.no_results')"
                    :server-side="true"
                    @sort="handleTableSort"
                >
                    <template #cell-name="{ row }">
                         <span class="font-medium text-gray-900 dark:text-white">{{ row.name }}</span>
                    </template>
                    <template #cell-revenue="{ row }">
                        {{ formatCurrency(row.revenue) }}
                    </template>
                </Table>
            </div>
        </div>

        <!-- Details Modal (retained for backward compatibility if needed, though mostly unused in new layout for now) -->
        <Modal :show="showDetailsModal" @close="showDetailsModal = false" max-width="4xl">
             <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ detailsTitle }}</h2>
                    <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div v-if="loadingDetails" class="py-12 flex flex-col items-center justify-center space-y-4">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-600"></div>
                    <p class="text-gray-500">{{ $t('common.loading') }}</p>
                </div>
                
                <div v-else>
                    <div v-if="detailsData.length > 0" class="overflow-x-auto">
                        <Table
                            :columns="detailsColumns"
                            :data="detailsData"
                            :title="''"
                            :show-search="false"
                        >
                            <template v-for="col in detailsColumns" :key="col.key" v-slot:[`cell-${col.key}`]="{ row }">
                                <template v-if="col.format === 'currency'">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(row[col.key]) }}</span>
                                </template>
                                <template v-else-if="col.format === 'datetime'">
                                    <span class="text-gray-500 whitespace-nowrap">{{ new Date(row[col.key]).toLocaleString() }}</span>
                                </template>
                                <template v-else-if="col.format === 'status'">
                                    <span :class="{
                                        'px-2 py-0.5 rounded text-xs font-medium': true,
                                        'bg-green-100 text-green-800': row[col.key] === 'completed',
                                        'bg-blue-100 text-blue-800': row[col.key] === 'preparing' || row[col.key] === 'ready',
                                        'bg-yellow-100 text-yellow-800': row[col.key] === 'pending',
                                        'bg-red-100 text-red-800': row[col.key] === 'cancelled',
                                        'bg-gray-100 text-gray-800': row[col.key] === 'deleted'
                                    }">
                                        {{ $t('common.' + row[col.key]) || row[col.key] }}
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="text-gray-700 dark:text-gray-300">{{ row[col.key] }}</span>
                                </template>
                            </template>
                        </Table>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-gray-500">{{ $t('common.no_results') }}</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <Button @click="showDetailsModal = false" variant="secondary">{{ $t('common.close') }}</Button>
                </div>
             </div>
        </Modal>

    </MainLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import StatsCard from '@/Components/StatsCard.vue';
import ChartCard from '@/Components/ChartCard.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import Table from '@/Components/Table.vue';
import axios from 'axios';

// Simple debounce
const debounce = (fn: Function, wait: number) => {
    let timeout: any;
    return (...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
};

const { t } = useI18n();
Chart.register(...registerables);

const page = usePage();
const dateRange = computed(() => page.props.date_range as any);
const currentTab = computed(() => (page.props.active_tab as string) || 'overview');
const isRtl = computed(() => page.props.isRtl as boolean);
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

// Data Props
const highlights = computed(() => page.props.highlights as any || { sales: 0, orders: 0, customers: 0, rewards_redeemed: 0, new_customers: 0, repeat_customers: 0 });
const periodSales = computed(() => page.props.period_sales as any || { total: 0, valid_count: 0, blocked_count: 0, chart: [] });
const periodVisits = computed(() => page.props.period_visits as any || { total: 0, chart: [] });
const customerInsights = computed(() => page.props.customer_insights as any || { total: 0, active: 0, inactive: 0 });

const topInsights = computed(() => page.props.top_insights as any || { pareto_percent: 0, avg_order_value: 0, avg_items_per_order: 0, avg_visits_per_year: 0 });
const popularTimes = computed(() => page.props.popular_times as any || {});
const customerFrequency = computed(() => page.props.customer_frequency as any || {});
const topRewards = computed(() => page.props.top_rewards as any[] || []);
const topItems = computed(() => page.props.top_items as any[] || []);
const paymentDistribution = computed(() => page.props.payment_distribution as any[] || []);
const statusDistribution = computed(() => page.props.status_distribution as any[] || []);
const peakHours = computed(() => page.props.peak_hours as any[] || []);
const wasteTrend = computed(() => page.props.waste_chart as any[] || []);
const completionTimeTrend = computed(() => page.props.avg_completion_time as any[] || []);
// Legacy/Other props
const itemSalesData = computed(() => page.props.item_sales_data as any);
const filters = computed(() => page.props.filters as any || {});


// Tables Logic
const searchQuery = ref(filters.value.q || '');
const currentSort = ref(filters.value.sort || 'quantity_desc');
const itemSalesColumns = computed(() => [
    { key: 'name', label: t('common.item'), sortable: true, align: 'center' as const },
    { key: 'category', label: t('common.category'), sortable: false, align: 'center' as const },
    { key: 'quantity', label: t('dashboard.quantity_sold'), sortable: true, align: 'center' as const },
    { key: 'revenue', label: t('common.revenue'), sortable: true, format: 'currency' as const, align: 'center' as const }
]);
const itemSalesList = computed(() => itemSalesData.value?.data || []);
const itemSalesPagination = computed(() => itemSalesData.value || {});

const handleTableSort = (key: string, direction: 'asc' | 'desc') => {
    const sortValue = `${key}_${direction}`;
    currentSort.value = sortValue;
    updateParams({ sort: sortValue, page: 1 });
};

watch(searchQuery, debounce((val: string) => {
    if (currentTab.value === 'item_sales') updateParams({ q: val, page: 1 });
}, 500));

// Navigation
const updateParams = (params: any) => {
    router.get(window.location.pathname, {
        start_date: dateRange.value.start_date,
        end_date: dateRange.value.end_date,
        tab: currentTab.value,
        ...params
    }, { preserveState: true, preserveScroll: true });
};

const onDateRangeUpdate = (range: { startDate: string; endDate: string }) => {
    const params: any = { start_date: range.startDate, end_date: range.endDate, tab: currentTab.value };
    if (currentTab.value === 'item_sales') {
        params.q = searchQuery.value;
        params.sort = currentSort.value;
    }
    router.get(window.location.pathname, params, { preserveState: true, preserveScroll: true });
};

const switchTab = (tab: string) => {
    router.get(window.location.pathname, {
        start_date: dateRange.value.start_date,
        end_date: dateRange.value.end_date,
        tab: tab
    }, { preserveState: true, preserveScroll: true });
};

// Utils
const formatCurrency = (amount: number) => new Intl.NumberFormat('en-AE', { style: 'currency', currency: currency.value }).format(amount);


// Charts
const revenueChartCanvas = ref<HTMLCanvasElement | null>(null);
const visitsChartCanvas = ref<HTMLCanvasElement | null>(null);
const paymentChartCanvas = ref<HTMLCanvasElement | null>(null);
const statusChartCanvas = ref<HTMLCanvasElement | null>(null);
const peakHoursChartCanvas = ref<HTMLCanvasElement | null>(null);
const wasteChartCanvas = ref<HTMLCanvasElement | null>(null);
const completionTimeChartCanvas = ref<HTMLCanvasElement | null>(null);

let revenueChartInstance: Chart | null = null;
let visitsChartInstance: Chart | null = null;
let paymentChartInstance: Chart | null = null;
let statusChartInstance: Chart | null = null;
let peakHoursChartInstance: Chart | null = null;
let wasteChartInstance: Chart | null = null;
let completionTimeChartInstance: Chart | null = null;

const initRevenueChart = () => {
    if (!revenueChartCanvas.value) return;
    if (revenueChartInstance) revenueChartInstance.destroy();
    
    const chartData = periodSales.value.chart || [];
    const ctx = revenueChartCanvas.value.getContext('2d');
    if (!ctx) return;
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
    
    revenueChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map((d: any) => d.date),
            datasets: [{
                label: t('dashboard.sales'),
                data: chartData.map((d: any) => d.revenue),
                borderColor: '#10B981',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#10B981',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
                backgroundColor: gradient
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#1f2937',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label: (context) => ` ${t('dashboard.sales')}: ${formatCurrency(context.raw as number)}`
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 10, weight: 'bold' as any }, callback: (val) => formatCurrency(val as number) } 
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: 'bold' as any } }
                }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements && activeElements.length > 0) {
                    const firstElement = activeElements[0];
                    if (firstElement && typeof firstElement.index !== 'undefined') {
                        const dataIndex = firstElement.index;
                        const chartData = periodSales.value.chart || [];
                        if (chartData[dataIndex]) {
                            const date = chartData[dataIndex].date;
                            fetchDetails('revenue_chart_point', { date });
                        }
                    }
                }
            }
        }
    });
};

const initVisitsChart = () => {
    if (!visitsChartCanvas.value) return;
    if (visitsChartInstance) visitsChartInstance.destroy();
    const chartData = periodVisits.value.chart || [];
    const ctx = visitsChartCanvas.value.getContext('2d');
    if (!ctx) return;
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.2)');

    visitsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map((d: any) => d.date),
            datasets: [{
                label: t('dashboard.period_visits'),
                data: chartData.map((d: any) => d.count),
                backgroundColor: gradient,
                borderRadius: 8,
                hoverBackgroundColor: 'rgb(59, 130, 246)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#1f2937',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    padding: 12
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 10, weight: 'bold' as any }, precision: 0 } 
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: 'bold' as any } }
                }
            }
        }
    });
};

const initPaymentChart = () => {
    if (!paymentChartCanvas.value) return;
    if (paymentChartInstance) paymentChartInstance.destroy();
    const ctx = paymentChartCanvas.value.getContext('2d');
    if (!ctx) return;

    paymentChartInstance = new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: paymentDistribution.value.map(d => d.method),
            datasets: [{
                data: paymentDistribution.value.map(d => d.value),
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(239, 68, 68, 0.7)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' as any }, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#1f2937',
                    padding: 12,
                    callbacks: {
                        label: function(context: any) {
                            let label = context.label || '';
                            if (label) label += ': ';
                            if (context.raw !== null) label += formatCurrency(context.raw);
                            return label;
                        }
                    }
                }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    fetchDetails('payment_method_slice', { method: paymentDistribution.value[idx].method });
                }
            }
        }
    });
};

const initStatusChart = () => {
    if (!statusChartCanvas.value) return;
    if (statusChartInstance) statusChartInstance.destroy();
    const ctx = statusChartCanvas.value.getContext('2d');
    if (!ctx) return;

    statusChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusDistribution.value.map(d => t('common.' + d.status) || d.status),
            datasets: [{
                data: statusDistribution.value.map(d => d.count),
                backgroundColor: [
                    '#10B981', // completed
                    '#F59E0B', // pending
                    '#3B82F6', // preparing
                    '#EF4444', // cancelled
                    '#6B7280'  // deleted
                ],
                borderWidth: 5,
                borderColor: '#fff',
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' as any }, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#1f2937',
                    padding: 12
                }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    fetchDetails('status_slice', { status: statusDistribution.value[idx].status });
                }
            }
        }
    });
};

const initPeakHoursChart = () => {
    if (!peakHoursChartCanvas.value) return;
    if (peakHoursChartInstance) peakHoursChartInstance.destroy();
    const ctx = peakHoursChartCanvas.value.getContext('2d');
    if (!ctx) return;

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(139, 92, 246, 0.8)');
    gradient.addColorStop(1, 'rgba(139, 92, 246, 0.2)');

    peakHoursChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: peakHours.value.map(d => `${d.hour}:00`),
            datasets: [{
                label: t('dashboard.orders'),
                data: peakHours.value.map(d => d.count),
                backgroundColor: gradient,
                borderRadius: 8,
                hoverBackgroundColor: 'rgb(139, 92, 246)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#1f2937',
                    padding: 12
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 10, weight: 'bold' as any }, precision: 0 } 
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: 'bold' as any } }
                }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    fetchDetails('peak_hour_slice', { hour: peakHours.value[idx].hour });
                }
            }
        }
    });
};

const initWasteChart = () => {
    if (!wasteChartCanvas.value) return;
    if (wasteChartInstance) wasteChartInstance.destroy();
    const ctx = wasteChartCanvas.value.getContext('2d');
    if (!ctx) return;

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(239, 68, 68, 0.4)');
    gradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

    wasteChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: wasteTrend.value.map(d => d.date),
            datasets: [{
                label: t('dashboard.waste_loss'),
                data: wasteTrend.value.map(d => d.loss),
                borderColor: '#EF4444',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#1f2937',
                    padding: 12
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 10, weight: 'bold' as any }, callback: (val) => formatCurrency(val as number) } 
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: 'bold' as any } }
                }
            },
            onClick: (_e: any, activeElements: any[]) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    fetchDetails('waste_chart_point', { date: wasteTrend.value[idx].date });
                }
            }
        }
    });
};

const initCompletionTimeChart = () => {
    if (!completionTimeChartCanvas.value) return;
    if (completionTimeChartInstance) completionTimeChartInstance.destroy();
    const ctx = completionTimeChartCanvas.value.getContext('2d');
    if (!ctx) return;

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(14, 165, 233, 0.4)');
    gradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

    completionTimeChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: completionTimeTrend.value.map(d => d.date),
            datasets: [{
                label: t('dashboard.minutes'),
                data: completionTimeTrend.value.map(d => d.minutes),
                borderColor: '#0EA5E9',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#1f2937',
                    padding: 12
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 10, weight: 'bold' as any } },
                    title: { display: true, text: t('dashboard.minutes'), font: { size: 10, weight: 'bold' as any } } 
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: 'bold' as any } }
                }
            }
        }
    });
};

const route = (window as any).route;

// Modals
const showDetailsModal = ref(false);
const loadingDetails = ref(false);
const detailsTitle = ref('');
const detailsColumns = ref<any[]>([]);
const detailsData = ref<any[]>([]);
const currentDetailType = ref('');
const currentDetailParams = ref<any>({});

const fetchDetails = async (type: string, params: any = {}) => {
    showDetailsModal.value = true;
    loadingDetails.value = true;
    detailsData.value = [];
    currentDetailType.value = type;
    currentDetailParams.value = params;
    
    try {
        const response = await axios.get(route('dashboard.details'), {
            params: {
                type,
                start_date: dateRange.value.start_date,
                end_date: dateRange.value.end_date,
                ...params
            }
        });
        
        detailsTitle.value = response.data.title;
        detailsColumns.value = response.data.columns;
        detailsData.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch dashboard details', error);
        detailsTitle.value = t('common.error');
    } finally {
        loadingDetails.value = false;
    }
};

onMounted(() => {
    if (currentTab.value === 'overview') {
        initRevenueChart();
        initVisitsChart();
        initPaymentChart();
        initStatusChart();
        initPeakHoursChart();
        initWasteChart();
        initCompletionTimeChart();
    }
});

watch(() => page.props.period_sales, () => {
    if (currentTab.value === 'overview') initRevenueChart();
}, { deep: true });

watch(() => page.props.period_visits, () => {
    if (currentTab.value === 'overview') {
        initRevenueChart();
        initVisitsChart();
        initPaymentChart();
        initStatusChart();
        initPeakHoursChart();
        initWasteChart();
        initCompletionTimeChart();
    }
}, { deep: true });

</script>
