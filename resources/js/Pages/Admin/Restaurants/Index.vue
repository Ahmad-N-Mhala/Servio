<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Restaurants Management</h1>
            </div>

            <Table 
                :columns="columns"
                :data="restaurants.data"
                :pagination="restaurants"
                v-model:search="search"
                title="All Restaurants"
                :row-class="getRowClass"
            >
                <!-- Header Actions -->
                <template #header-actions>
                    <div class="flex items-center gap-3">
                        <div class="w-40">
                            <Select
                                v-model="statusFilter"
                                :options="statusFilterOptions"
                                placeholder="Status"
                                class="text-sm"
                            />
                        </div>
                        

                        
                        <Link :href="route('admin.restaurants.create')" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-hover shadow-lg shadow-primary/30 text-center whitespace-nowrap flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add New Restaurant
                        </Link>
                    </div>
                </template>

                <!-- Combined Name & Location Column -->
                <template #cell-name="{ row }">
                    <div class="flex flex-col gap-1">
                        <div class="font-bold text-gray-900 text-base flex items-center gap-2">
                             {{ row.name }}
                             <span v-if="row.id" class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded font-mono">#{{ row.id.substring(0,6) }}</span>
                        </div>
                        
                        <!-- Owner Info -->
                        <div class="flex flex-col text-sm text-gray-500">
                            <div class="flex items-center gap-1">
                                <span class="text-gray-400">Owner:</span>
                                <button 
                                    v-if="getOwner(row)" 
                                    @click="openOwnerModal(getOwner(row))"
                                    class="text-primary hover:text-primary-hover hover:underline font-medium transition-colors"
                                >
                                    {{ getOwner(row).name }}
                                </button>
                                <span v-else class="italic text-gray-400">Unassigned</span>
                            </div>
                            <div v-if="getOwner(row)" class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>{{ getOwner(row).email }}</span>
                            </div>
                        </div>

                        <!-- Location Info -->
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span v-if="row.country" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                🌍 {{ row.country }}
                            </span>
                            <span v-if="row.city" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                🏙️ {{ row.city }}
                            </span>
                            <a 
                                v-if="row.google_map_location" 
                                :href="row.google_map_location" 
                                target="_blank" 
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors border border-blue-100"
                            >
                                📍 Map
                            </a>
                        </div>
                    </div>
                </template>

                <!-- Detailed Subscription Column -->
                <template #cell-subscription="{ row }">
                    <div v-if="row.subscription" class="flex flex-col gap-1.5">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900">{{ row.subscription.plan?.name?.en || row.subscription.plan?.name || 'Unknown Plan' }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded-md bg-gray-100 text-gray-600 border border-gray-200 capitalize">
                                {{ row.subscription.billing_cycle === 'monthly' ? 'half year' : (row.subscription.billing_cycle || 'half year') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span 
                                class="px-2 py-0.5 rounded-full text-xs font-bold capitalize"
                                :class="{
                                    'bg-green-100 text-green-700': !isExpired(row) && row.subscription.status === 'active',
                                    'bg-red-100 text-red-700': isExpired(row) || row.subscription.status === 'expired' || row.subscription.status === 'cancelled',
                                    'bg-yellow-100 text-yellow-700': !isExpired(row) && row.subscription.status === 'trial',
                                    'bg-gray-100 text-gray-600': !isExpired(row) && !row.subscription.status
                                }"
                            >
                                {{ isExpired(row) ? 'expired' : (row.subscription.status || 'Inactive') }}
                            </span>
                            
                            <span v-if="row.subscription.ends_at" class="text-xs text-gray-500 flex items-center gap-1">
                                🕒 {{ formatExpiry(row.subscription.ends_at) }}
                            </span>
                        </div>
                        
                        <!-- Log Icon -->
                         <button 
                            @click.stop="openLogsModal(row)"
                            class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mt-1 w-fit hover:underline"
                            title="View Subscription History"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                             View History
                        </button>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic">
                        No Active Subscription
                    </div>
                </template>

                <!-- Status Column -->
                <template #cell-status="{ row }">
                    <div class="flex flex-col gap-1">
                        <span 
                            class="px-3 py-1 rounded-full text-xs font-bold capitalize inline-flex items-center gap-1 w-fit"
                            :class="{
                                'bg-green-100 text-green-700': row.status === 'active' && !row.deleted_at,
                                'bg-yellow-100 text-yellow-700': row.status === 'suspended',
                                'bg-red-100 text-red-700': row.deleted_at,
                                'bg-gray-100 text-gray-700': !row.status
                            }"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="{
                                'bg-green-500': row.status === 'active' && !row.deleted_at,
                                'bg-yellow-500': row.status === 'suspended',
                                'bg-red-500': row.deleted_at,
                                'bg-gray-500': !row.status
                            }"></span>
                            {{ row.deleted_at ? 'Deleted' : (row.status || 'Active') }}
                        </span>
                        <span v-if="row.deleted_at" class="text-xs text-gray-500">
                            {{ new Date(row.deleted_at).toLocaleDateString() }}
                        </span>
                    </div>
                </template>
                
                <!-- Actions Column -->
                <template #cell-actions="{ row }">
                    <button 
                        @click="openActionModal(row)"
                        class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-sm hover:shadow flex items-center justify-center gap-1 ml-auto"
                    >
                        <span>Manage</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                    </button>
                </template>
            </Table>
        </div>

        <!-- Owner Details Modal -->
        <Modal :show="showOwnerModal" @close="closeOwnerModal">
            <div v-if="selectedOwner" class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Owner Details</h2>
                    <button @click="closeOwnerModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-2xl font-bold text-gray-500">
                            {{ selectedOwner.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ selectedOwner.name }}</h3>
                            <p class="text-sm text-gray-500">Restaurant Owner</p>
                        </div>
                </div>
                
                <div class="border-t border-gray-100 pt-4 space-y-3">
                        <div class="flex items-center gap-3">
                             <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                             </div>
                             <div>
                                 <p class="text-xs text-gray-500 uppercase font-semibold">Email Address</p>
                                 <p class="text-sm font-medium text-gray-900">{{ selectedOwner.email }}</p>
                             </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                             <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                             </div>
                             <div>
                                 <p class="text-xs text-gray-500 uppercase font-semibold">Phone Number</p>
                                 <p class="text-sm font-medium text-gray-900">{{ selectedOwner.phone || 'Not Provided' }}</p>
                             </div>
                        </div>
                </div>
            </div>
        </Modal>

        <!-- Actions Modal -->
        <Modal :show="showActionModal" @close="closeActionModal">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Manage Restaurant</h3>
                    <button @click="closeActionModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div v-if="editingRestaurant" class="space-y-3">
                    <div class="bg-gray-50 p-4 rounded-xl mb-4 border border-gray-100">
                        <h4 class="font-bold text-gray-900">{{ editingRestaurant.name }}</h4>
                        <p class="text-sm text-gray-500">{{ editingRestaurant.email }}</p>
                    </div>

                    <Link :href="route('admin.restaurants.edit', editingRestaurant.id)" class="w-full flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:bg-indigo-50 hover:border-indigo-200 transition-all font-medium text-gray-700 hover:text-indigo-700 text-left">
                        <span class="p-2 bg-indigo-100 text-indigo-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></span>
                        <div>
                            <div>Edit Details</div>
                            <div class="text-xs text-gray-400 font-normal">Update address, settings, and contact info</div>
                        </div>
                    </Link>

                    <button @click="closeActionModal(); openSubscriptionModal(editingRestaurant)" class="w-full flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:bg-green-50 hover:border-green-200 transition-all font-medium text-gray-700 hover:text-green-700 text-left">
                        <span class="p-2 bg-green-100 text-green-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg></span>
                        <div>
                            <div>Manage Subscription</div>
                            <div class="text-xs text-gray-400 font-normal">Upgrade plan, extend validation, or cancel</div>
                        </div>
                    </button>
                    
                    <button v-if="!editingRestaurant.deleted_at" @click="closeActionModal(); deleteRestaurant(editingRestaurant)" class="w-full flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:bg-red-50 hover:border-red-200 transition-all font-medium text-gray-700 hover:text-red-700 text-left">
                        <span class="p-2 bg-red-100 text-red-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></span>
                        <div>
                            <div>Delete Restaurant</div>
                            <div class="text-xs text-gray-400 font-normal">Deactivate access (reversible)</div>
                        </div>
                    </button>
                    
                    <button v-else @click="closeActionModal(); restoreRestaurant(editingRestaurant)" class="w-full flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:bg-yellow-50 hover:border-yellow-200 transition-all font-medium text-gray-700 hover:text-yellow-700 text-left">
                        <span class="p-2 bg-yellow-100 text-yellow-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></span>
                        <div>
                            <div>Restore Restaurant</div>
                            <div class="text-xs text-gray-400 font-normal">Reactivate access and users</div>
                        </div>
                    </button>

                    <button v-if="editingRestaurant.deleted_at" @click="closeActionModal(); forceDeleteRestaurant(editingRestaurant)" class="w-full flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:bg-red-50 hover:border-red-200 transition-all font-medium text-gray-700 hover:text-red-700 text-left mt-3">
                        <span class="p-2 bg-red-600 text-white rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></span>
                        <div>
                            <div class="font-bold text-red-600">Permanently Delete</div>
                            <div class="text-xs text-red-400 font-normal">WARNING: Deletes ALL data forever</div>
                        </div>
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Subscription Management Modal -->
        <Modal :show="showSubscriptionModal" @close="closeSubscriptionModal">
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ editingRestaurant?.subscription ? 'Update' : 'Assign' }} Subscription
                    </h3>
                    <button @click="closeSubscriptionModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="saveSubscription">
                    <div class="space-y-4">
                        <!-- Plan -->
                        <div>
                            <Select
                                v-model="subForm.plan_id"
                                label="Subscription Plan"
                                :options="planOptions"
                                placeholder="Select Plan"
                                :error="subForm.errors.plan_id"
                            />
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Input
                                    v-model="subForm.starts_at"
                                    label="Start Date"
                                    type="date"
                                />
                            </div>
                            <div>
                                <Input
                                    v-model="subForm.ends_at"
                                    label="End Date (Optional)"
                                    type="date"
                                />
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <Select
                                v-model="subForm.status"
                                :label="$t('common.status')"
                                :options="subscriptionStatusOptions"
                            />
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-between border-t pt-6">
                        <button 
                            type="button"
                            v-if="editingRestaurant?.subscription"
                            @click="deleteSubscription(editingRestaurant.subscription.id); closeSubscriptionModal()"
                            class="text-red-600 hover:text-red-700 text-sm font-bold flex items-center gap-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Remove Subscription
                        </button>
                        <div v-else></div>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeSubscriptionModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-xl font-medium transition-colors">{{ $t('common.cancel') }}</button>
                            <button 
                                type="submit" 
                                :disabled="subForm.processing"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 disabled:opacity-50"
                            >
                                {{ editingRestaurant?.subscription ? 'Update' : 'Assign' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Logs Modal -->
        <Modal :show="showLogsModal" @close="closeLogsModal">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Subscription History</h3>
                    <button @click="closeLogsModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div v-if="logsLoading" class="flex justify-center py-8">
                     <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                
                <div v-else-if="subscriptionLogs.length === 0" class="text-center py-8 text-gray-500">
                    No history found.
                </div>

                <div v-else class="relative border-l-2 border-indigo-100 ml-3 space-y-8 py-4">
                    <div v-for="log in subscriptionLogs" :key="log.id" class="relative pl-8">
                        <!-- Timeline Dot -->
                        <span 
                            class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-2 border-white shadow-sm"
                            :class="{
                                'bg-green-500': log.status === 'active',
                                'bg-red-500': log.status === 'expired' || log.status === 'cancelled',
                                'bg-yellow-500': log.status === 'trial',
                                'bg-gray-300': !log.status
                            }"
                        ></span>
                        
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 shadow-sm">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ log.plan?.name?.en || log.plan?.name || 'Unknown Plan' }}</h4>
                                    <span class="text-xs text-gray-500 capitalize">{{ log.billing_cycle === 'monthly' ? 'half year' : (log.billing_cycle || 'half year') }} cycle</span>
                                </div>
                                <span 
                                    class="px-2 py-0.5 rounded text-xs font-bold capitalize"
                                    :class="{
                                        'bg-green-100 text-green-700': log.status === 'active',
                                        'bg-red-100 text-red-700': log.status === 'expired' || log.status === 'cancelled',
                                        'bg-yellow-100 text-yellow-700': log.status === 'trial',
                                        'bg-gray-100 text-gray-600': !log.status
                                    }"
                                >
                                    {{ log.status }}
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600 grid grid-cols-2 gap-2">
                                <div>
                                    <span class="block text-xs text-gray-400 uppercase">Started</span>
                                    {{ new Date(log.starts_at).toLocaleDateString() }}
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-400 uppercase">Ended</span>
                                    {{ log.ends_at ? new Date(log.ends_at).toLocaleDateString() : 'Ongoing' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Table from '@/Components/Table.vue';
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import { debounce } from 'lodash';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const isExpired = (row: any) => {
    if (!row.subscription) return false;
    if (row.subscription.status === 'expired') return true;
    if (row.subscription.ends_at) {
        const expiryDate = new Date(row.subscription.ends_at);
        return expiryDate < new Date();
    }
    return false;
};

const getRowClass = (row: any) => {
    return isExpired(row) ? 'expired-row' : '';
};

const columns = [
    { key: 'name', label: 'Restaurant & Owner', sortable: true },
    { key: 'subscription', label: 'Subscription Details', sortable: false },
    { key: 'status', label: 'Status', sortable: true, align: 'center' as const },
    { key: 'actions', label: 'Actions', sortable: false, align: 'right' as const },
];

const statusFilterOptions = [
    { label: 'All Status', value: '' },
    { label: 'Active Only', value: 'active' },
    { label: 'Deleted Only', value: 'deleted' },
];

const subscriptionStatusOptions = computed(() => [
    { label: t('common.active'), value: 'active' },
    { label: 'Cancelled', value: 'cancelled' },
    { label: 'Expired', value: 'expired' },
    { label: 'Trial', value: 'trial' },
]);

// Helper to format date relative (e.g. "in 5 days" or "Active")
const formatExpiry = (dateStr: string) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffTime = date.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    
    if (diffDays < 0) return `Expired ${Math.abs(diffDays)} days ago`;
    if (diffDays === 0) return 'Expires Today';
    if (diffDays === 1) return 'Expires Tomorrow';
    if (diffDays <= 30) return `Expires in ${diffDays} days`;
    
    return date.toLocaleDateString();
};

const props = defineProps<{
    restaurants: {
        data: any[];
        links: any[]; // Pagination links
    };
    filters: {
        search?: string;
        status?: string;
    };
    plans: Array<any>;
}>();

const planOptions = computed(() => {
    return props.plans.map(p => ({ label: p.name, value: p.id }));
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const handleSearch = debounce(() => {
    router.get(route('admin.restaurants.index'), { 
        search: search.value,
        status: statusFilter.value
    }, { preserveState: true, replace: true });
}, 300);

watch([search, statusFilter], handleSearch);

const showOwnerModal = ref(false);
const selectedOwner = ref<any>(null);

const getOwner = (restaurant: any) => {
    if (Array.isArray(restaurant.owner)) {
        return restaurant.owner[0] || null;
    }
    return restaurant.owner || null;
};

const openOwnerModal = (owner: any) => {
    selectedOwner.value = owner;
    showOwnerModal.value = true;
};

const closeOwnerModal = () => {
    showOwnerModal.value = false;
    selectedOwner.value = null;
};

const deleteRestaurant = (restaurant: any) => {
    if (confirm('Are you sure you want to delete this restaurant? All associated users will be deactivated.')) {
        router.delete(route('admin.restaurants.destroy', restaurant.id));
    }
};

const restoreRestaurant = (restaurant: any) => {
    if (confirm('Are you sure you want to restore this restaurant? All associated users will be reactivated.')) {
        router.post(route('admin.restaurants.restore', restaurant.id));
    }
};

const forceDeleteRestaurant = (restaurant: any) => {
    if (confirm('WARNING: This action is permanent! Are you sure you want to delete this restaurant and ALL related data (orders, staff, menu, etc.)? This cannot be undone.')) {
        router.delete(route('admin.restaurants.force-destroy', restaurant.id));
    }
};

const editingRestaurant = ref<any>(null);
const showSubscriptionModal = ref(false);

const subForm = useForm({
    restaurant_id: '',
    plan_id: '',
    starts_at: new Date().toISOString().split('T')[0],
    ends_at: '',
    status: 'active',
});

const openSubscriptionModal = (restaurant: any) => {
    editingRestaurant.value = restaurant;
    subForm.restaurant_id = restaurant.id;
    
    if (restaurant.subscription) {
        subForm.plan_id = restaurant.subscription.plan_id;
        subForm.starts_at = restaurant.subscription.starts_at?.split('T')[0] || '';
        subForm.ends_at = restaurant.subscription.ends_at?.split('T')[0] || '';
        subForm.status = restaurant.subscription.status;
    } else {
        subForm.plan_id = '';
        subForm.starts_at = new Date().toISOString().split('T')[0];
        subForm.ends_at = '';
        subForm.status = 'active';
    }
    showSubscriptionModal.value = true;
};

const closeSubscriptionModal = () => {
    showSubscriptionModal.value = false;
    editingRestaurant.value = null;
    subForm.reset();
};

const saveSubscription = () => {
    if (editingRestaurant.value?.subscription) {
         subForm.put(route('admin.subscriptions.update', editingRestaurant.value.subscription.id), {
            preserveScroll: true,
            onSuccess: () => closeSubscriptionModal(),
        });
    } else {
         subForm.post(route('admin.subscriptions.store'), {
            preserveScroll: true,
            onSuccess: () => closeSubscriptionModal(),
        });
    }
};

const deleteSubscription = (id: number) => {
    if (confirm('Are you sure you want to remove this subscription?')) {
        router.delete(route('admin.subscriptions.destroy', id), {
             preserveScroll: true,
        });
    }
};

const showActionModal = ref(false);
const openActionModal = (restaurant: any) => {
    editingRestaurant.value = restaurant;
    showActionModal.value = true;
};
const closeActionModal = () => {
    showActionModal.value = false;
};

// Logs Logic
const showLogsModal = ref(false);
const subscriptionLogs = ref<any[]>([]);
const logsLoading = ref(false);

const openLogsModal = async (restaurant: any) => {
    editingRestaurant.value = restaurant;
    showLogsModal.value = true;
    logsLoading.value = true;
    subscriptionLogs.value = [];

    try {
        const response = await fetch(route('admin.restaurants.subscription-logs', restaurant.id));
        if (response.ok) {
            subscriptionLogs.value = await response.json();
        }
    } catch (error) {
        console.error('Failed to fetch logs', error);
    } finally {
        logsLoading.value = false;
    }
};

const closeLogsModal = () => {
    showLogsModal.value = false;
    subscriptionLogs.value = [];
};

const route = (window as any).route;
</script>

<style scoped>
:deep(.expired-row) td {
    border-top: 2px solid #ef4444 !important;
    border-bottom: 2px solid #ef4444 !important;
    background-color: rgba(254, 242, 242, 0.4) !important;
}
:deep(.expired-row) td:first-child {
    border-left: 2px solid #ef4444 !important;
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}
:deep(.expired-row) td:last-child {
    border-right: 2px solid #ef4444 !important;
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}
</style>
