<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $t('communication.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('communication.manage_description') }}</p>
                </div>
                <div v-if="activeTab === 'templates'">
                    <Button @click="openTemplateModal()" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        {{ $t('communication.new_rule') }}
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- SMS Balance -->
                <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full uppercase tracking-wider">{{ $t('communication.sms') }}</span>
                        </div>
                        <h3 class="text-4xl font-bold mb-1">{{ balances.sms }}</h3>
                        <p class="text-sm font-medium text-blue-100 opacity-90">{{ $t('communication.sms_credits') }}</p>
                    </div>
                </div>

                <!-- Email Balance -->
                <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full uppercase tracking-wider">{{ $t('communication.email') }}</span>
                        </div>
                        <h3 class="text-4xl font-bold mb-1">{{ balances.email }}</h3>
                        <p class="text-sm font-medium text-purple-100 opacity-90">{{ $t('communication.email_credits') }}</p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex space-x-1 bg-gray-100 p-1 rounded-xl w-fit">
                <button 
                    @click="activeTab = 'templates'"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                        activeTab === 'templates' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'
                    ]"
                >
                    {{ $t('communication.automation_rules') }}
                </button>
                <button 
                    @click="activeTab = 'logs'"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                        activeTab === 'logs' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'
                    ]"
                >
                    {{ $t('communication.communication_logs') }}
                </button>
                <button 
                    @click="activeTab = 'feedback'"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                        activeTab === 'feedback' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'
                    ]"
                >
                    {{ $t('communication.feedback_automation') }}
                </button>
            </div>

            <!-- Active Filter Indicator -->
            <div v-if="params.template_id" class="flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-lg border border-blue-100 animate-fade-in">
                 <span class="text-sm font-medium">{{ $t('communication.filter_by_rule_logs') }}</span>
                 <button @click="clearTemplateFilter" class="hover:text-blue-900">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                     </svg>
                 </button>
            </div>
        </div>

        <!-- NEW: Feedback Settings Tab -->
        <div v-if="activeTab === 'feedback'" class="max-w-4xl mx-auto animate-fade-in" :class="!hasFeature('feedback') ? 'opacity-85 pointer-events-none' : ''">
             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                <!-- Lock Overlay Banner -->
                <div v-if="!hasFeature('feedback')" class="absolute inset-0 z-50 flex items-center justify-center p-6 bg-gray-50/10 backdrop-blur-[1px] pointer-events-auto">
                    <div class="bg-white p-8 rounded-2xl shadow-2xl border border-amber-100 text-center max-w-md animate-bounce-in">
                        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $t('communication.feedback_locked') }}</h3>
                        <p class="text-gray-600 mb-6 text-sm">
                            {{ $t('communication.feedback_locked_msg') }}
                        </p>
                        <button 
                            @click="$inertia.visit(route('dashboard'))"
                            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-600 text-white font-semibold rounded-xl hover:bg-amber-700 transition-colors shadow-lg shadow-amber-200"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {{ $t('communication.upgrade_plan') }}
                        </button>
                    </div>
                </div>

                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363 1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        {{ $t('communication.automated_feedback_requests') }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $t('communication.automated_feedback_desc') }}
                    </p>
                    <div class="mt-2 text-right lg:text-left">
                        <Link :href="route('feedback.index')" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                            {{ $t('communication.view_received_feedback') }}
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </Link>
                    </div>
                </div>
                
                <form @submit.prevent="saveFeedbackSettings" class="p-6 space-y-8">
                     <!-- Activation Toggle -->
                     <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="feedbackForm.is_active" class="sr-only peer" :disabled="!hasFeature('feedback')">
                            <div dir="ltr" class="w-11 h-6 flex-shrink-0 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-900">{{ $t('communication.enable_feedback') }}</span>
                        </label>
                     </div>

                     <!-- Channels -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('communication.channels') }} <span class="text-red-500">*</span></label>
                        <div class="flex gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="checkbox" v-model="feedbackForm.channels" value="sms" class="text-blue-600 focus:ring-blue-500 rounded">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <span class="text-sm font-medium">{{ $t('communication.sms') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer hover:text-purple-600 transition-colors">
                                <input type="checkbox" v-model="feedbackForm.channels" value="email" class="text-purple-600 focus:ring-purple-500 rounded">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm font-medium">{{ $t('communication.email') }}</span>
                            </label>
                        </div>
                     </div>

                     <!-- SMS Message Editor -->
                     <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                     <div v-if="feedbackForm.channels.includes('sms')">
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                             <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                             {{ $t('communication.sms_message') }}
                        </label>
                        <div class="relative">
                            <textarea 
                                v-model="feedbackForm.message_body" 
                                rows="4"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-sans"
                                :placeholder="$t('communication.message_placeholder')"
                            ></textarea>
                            
                            <!-- Validation / Help Text -->
                            <div class="mt-2 flex justify-between items-start text-xs text-gray-500">
                                <p>
                                    {{ $t('communication.variables_help') }}
                                </p>
                                <span :class="(feedbackForm.message_body?.length || 0) > 160 ? 'text-red-600 font-bold' : ''">
                                    {{ feedbackForm.message_body?.length || 0 }}/160
                                </span>
                            </div>
                        </div>
                     </div>
                     </transition>

                     <!-- Email Editor -->
                     <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                     <div v-if="feedbackForm.channels.includes('email')" class="space-y-4 pt-4 border-t border-gray-100">
                        <label class="block text-sm font-medium text-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            {{ $t('communication.email_settings') }}
                        </label>
                        
                        <Input 
                            v-model="feedbackForm.email_subject"
                            :label="$t('communication.email_subject')"
                            :placeholder="$t('communication.email_subject_placeholder')"
                        />

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('communication.email_body') }}</label>
                            <textarea 
                                v-model="feedbackForm.email_content"
                                rows="6"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                :placeholder="$t('communication.email_body_placeholder')"
                            ></textarea>
                            <p class="mt-2 text-xs text-gray-500">
                                {{ $t('communication.variables_help') }}
                            </p>
                        </div>
                     </div>
                     </transition>

                     <!-- Google Maps Review Link -->
                     <div>
                        <div class="flex items-center gap-2 mb-2">
                            <label class="block text-sm font-medium text-gray-700">{{ $t('communication.google_review_link') }}</label>
                            <div class="relative group">
                                <svg class="w-4 h-4 text-gray-400 hover:text-blue-600 cursor-help transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                
                                <!-- Tooltip -->
                                <div class="absolute left-0 bottom-full mb-2 w-80 p-4 bg-gray-900 text-white text-xs rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="font-semibold mb-2">How to get your Google Maps review link:</div>
                                    <ol class="list-decimal list-inside space-y-1.5">
                                        <li>Open Google Maps and search for your restaurant</li>
                                        <li>Click on your restaurant name</li>
                                        <li>Click the "Share" button</li>
                                        <li>Copy the link and paste it here</li>
                                    </ol>
                                    <div class="mt-3 pt-3 border-t border-gray-700">
                                        <p class="font-semibold mb-1">Supported formats:</p>
                                        <ul class="space-y-1 text-gray-300">
                                            <li>• Short URL: maps.app.goo.gl/...</li>
                                            <li>• Full URL: google.com/maps/place/...</li>
                                            <li>• Place ID: ChIJ...</li>
                                        </ul>
                                    </div>
                                    <!-- Arrow -->
                                    <div class="absolute left-4 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                        <input 
                            v-model="feedbackForm.google_review_link" 
                            type="url"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://maps.app.goo.gl/... or https://www.google.com/maps/place/..."
                        >
                        <p class="mt-2 text-xs text-gray-500">
                            {{ $t('communication.google_review_help') }}
                        </p>
                     </div>

                     <!-- Timing and Delay -->
                     <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mb-6 transition-all duration-300">
                        <label class="block text-sm font-medium text-blue-900 mb-3">{{ $t('communication.timing_settings') }}</label>
                        <div class="space-y-4">
                            <!-- Option 1: Immediately (Implicit when checkbox is unchecked) -->
                            <!-- Checkbox: Delay Sending -->
                            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/50 transition-colors" :class="{'bg-white shadow-sm ring-1 ring-blue-200': feedbackForm.timing_mode === 'delay'}">
                                <input 
                                    type="checkbox" 
                                    v-model="feedbackForm.timing_mode" 
                                    true-value="delay" 
                                    false-value="immediately"
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-3"
                                >
                                <div class="flex-1">
                                    <span class="block text-gray-900 font-medium mb-2 mt-2">{{ $t('communication.delay_sending') }}</span>
                                    
                                    <div class="flex flex-wrap items-center gap-2 transition-opacity duration-200" :class="feedbackForm.timing_mode === 'delay' ? 'opacity-100' : 'opacity-50 pointer-events-none'">
                                        <span class="text-sm text-gray-600">{{ $t('communication.wait_for') }}</span>
                                        <div class="w-24">
                                            <input 
                                                v-model="feedbackForm.timing_val" 
                                                type="number" 
                                                min="1"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            >
                                        </div>
                                        <div class="w-40">
                                            <Select 
                                                v-model="feedbackForm.timing_unit"
                                                :options="timingUnitOptions"
                                                :clearable="false"
                                            />
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $t('communication.after_payment') }}</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                     </div>

                     <!-- Rewards Section -->
                     <div class="mb-6 p-4 bg-yellow-50 rounded-xl border border-yellow-100">
                        <label class="block text-sm font-medium text-yellow-900 mb-2">{{ $t('communication.loyalty_reward') }}</label>
                        <p class="text-xs text-yellow-700 mb-3">{{ $t('communication.loyalty_reward_desc') }}</p>
                        <div class="flex items-center gap-3">
                            <div class="w-32">
                                <Input 
                                    v-model="feedbackForm.feedback_points"
                                    type="number"
                                    placeholder="0"
                                    min="0"
                                />
                            </div>
                            <span class="text-sm font-medium text-yellow-800">{{ $t('communication.points') }}</span>
                        </div>
                     </div>

                     <!-- Conditions -->
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <Input 
                            v-model="feedbackForm.min_order_amount"
                            :label="$t('communication.min_order_amount')"
                            type="number"
                            placeholder="0"
                            :hint="$t('communication.min_order_hint')"
                         />
                         <Input 
                            v-model="feedbackForm.min_orders_count"
                            :label="$t('communication.min_past_orders')"
                            type="number"
                            placeholder="0"
                            :hint="$t('communication.min_past_orders_hint')"
                         />
                     </div>

                    <!-- Submit -->
                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <Button :loading="feedbackForm.processing" class="px-8">
                            {{ $t('communication.save_changes') }}
                        </Button>
                    </div>
                </form>
             </div>
        </div>

        <!-- Templates / Automation Rules Section -->
        <div v-if="activeTab === 'templates'" class="grid grid-cols-1 gap-6 animate-fade-in">
        <!-- ... existing template list ... -->
        <!-- FILTER OUT FEEDBACK TEMPLATE FROM LIST TO AVOID DUPES -->
            <div v-if="templates.filter(t => t.trigger_event !== 'order_completed_feedback').length === 0" class="text-center py-12 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <!-- ... empty state ... -->
                <h3 class="text-lg font-medium text-gray-900">{{ $t('communication.no_rules_defined') }}</h3>
                <!-- ... -->
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Filter the list -->
                <div 
                    v-for="template in templates.filter(t => t.trigger_event !== 'order_completed_feedback')" 
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                                <div class="flex gap-1" v-if="template.channels && template.channels.length">
                                    <div v-if="template.channels.includes('sms')" class="p-2 rounded-lg bg-blue-100" title="SMS Enabled">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                    </div>
                                    <div v-if="template.channels.includes('email')" class="p-2 rounded-lg bg-purple-100" title="Email Enabled">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div v-else class="p-2 rounded-lg bg-gray-100">
                                    <!-- Fallback/Legacy -->
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="font-bold text-gray-900">{{ template.name }}</h3>
                                    <span class="text-xs text-gray-500">{{ template.trigger_event.replace('_', ' ').toUpperCase() }}</span>
                                </div>
                            </div>
                            <span :class="template.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="px-2 py-1 text-xs rounded-full font-medium">
                                {{ template.is_active ? $t('common.active') : $t('common.inactive') }}
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-3 mb-4">
                            <p class="text-sm text-gray-600 line-clamp-3 italic">
                                "{{ template.content_en || template.content || template.sms_content_en || template.sms_content || 'No content defined' }}"
                            </p>
                        </div>

                         <!-- Stats & Actions -->
                        <div class="flex items-center justify-between text-sm text-gray-500 pt-4 border-t border-gray-100">
                            <button @click="showLogs(template)" class="flex items-center gap-1 hover:text-blue-600 transition-colors" title="View Logs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span class="underline">{{ $t('communication.sent') }}: {{ template.logs_count || 0 }}</span>
                            </button>
                            <div class="flex gap-2">
                                <button @click="openTemplateModal(template)" class="text-blue-600 hover:text-blue-800 font-medium">{{ $t('common.edit') }}</button>
                                <button @click="deleteTemplate(template)" class="text-red-600 hover:text-red-800 font-medium">{{ $t('common.delete') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs Section -->
            <div v-if="activeTab === 'logs'" class="glass-card rounded-2xl overflow-hidden animate-fade-in p-6">
                <div class="flex flex-col sm:flex-row gap-4 mb-6">
                    <div class="flex-1">
                        <Input v-model="params.search" :placeholder="$t('communication.search_logs')" type="search" />
                    </div>
                    <div class="w-40">
                        <Select v-model="params.type" :options="channelOptions" />
                    </div>
                    <div class="w-40">
                        <Select v-model="params.status" :options="statusOptions" />
                    </div>
                    <DateRangePicker 
                        :initial-start-date="params.date_from"
                        :initial-end-date="params.date_to"
                        @update="onDateRangeUpdate" 
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-gray-900 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 font-semibold">{{ $t('common.date') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('communication.rule') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('communication.recipient') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('communication.message') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('communication.channel') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('common.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3">{{ new Date(log.created_at).toLocaleString() }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="log.template" class="text-blue-600 font-medium">{{ log.template.name }}</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3">{{ log.recipient }}</td>
                                <td class="px-4 py-3 truncate max-w-xs" :title="log.message">{{ log.message }}</td>
                                <td class="px-4 py-3 uppercase text-xs font-bold">{{ log.type }}</td>
                                <td class="px-4 py-3">
                                    <span :class="log.status === 'sent' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'" class="px-2 py-1 rounded-full text-xs">
                                        {{ log.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ $t('communication.no_logs') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <Pagination :meta="logs" />
                </div>
            </div>

            <!-- Bundles Section -->
            <div v-if="activeTab === 'bundles'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in">
                <div v-for="bundle in bundles" :key="bundle.id" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-lg transition-all text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-if="bundle.type === 'sms'">
                            <!-- SMS Icon -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-else>
                            <!-- Email Icon -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ bundle.name }}</h3>
                    <div class="my-4">
                        <span class="text-3xl font-bold text-gray-900">{{ bundle.currency }} {{ bundle.price }}</span>
                    </div>
                    <p class="text-gray-500 mb-6">{{ bundle.quantity }} {{ bundle.type.toUpperCase() }} {{ $t('communication.credits') }}</p>
                    <Button @click="purchase(bundle)" class="w-full justify-center">{{ $t('communication.purchase') }}</Button>
                </div>
            </div>

        <!-- Create/Edit Template Modal -->
        <Modal :show="showTemplateModal" @close="closeTemplateModal" :title="editingTemplate ? $t('communication.edit_rule') : $t('communication.new_rule')" size="4xl">
            <form @submit.prevent="submitTemplate" class="p-1 space-y-6">
                <!-- Rule Information Section -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                         <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $t('communication.rule_info') }}
                        </h3>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Name -->
                        <div class="w-full">
                            <Input 
                                v-model="templateForm.name"
                                :label="$t('communication.rule_name')"
                                :placeholder="$t('communication.rule_name_placeholder')"
                                required
                                :error="templateForm.errors.name"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Trigger Event -->
                            <div>
                                 <Select 
                                    v-model="templateForm.trigger_event"
                                    :label="$t('communication.trigger_event')"
                                    :options="triggerEventOptions"
                                    required
                                    :hint="$t('communication.trigger_when_hint')"
                                 />
                            </div>

                            <!-- Channels (Checkbox Group) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $t('communication.channels') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200 h-[42px] items-center">
                                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600 transition-colors">
                                        <input type="checkbox" v-model="templateForm.channels" value="sms" class="text-blue-600 focus:ring-blue-500 rounded w-4 h-4">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                        <span class="text-sm font-medium">{{ $t('communication.sms') }}</span>
                                    </label>
                                    <div class="w-px h-4 bg-gray-300 mx-2"></div>
                                    <label class="flex items-center gap-2 cursor-pointer hover:text-purple-600 transition-colors">
                                        <input type="checkbox" v-model="templateForm.channels" value="email" class="text-purple-600 focus:ring-purple-500 rounded w-4 h-4">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm font-medium">{{ $t('communication.email') }}</span>
                                    </label>
                                </div>
                                <p v-if="templateForm.channels.length === 0" class="text-xs text-red-500 mt-1">{{ $t('communication.select_channel_required') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SMS Configuration -->
                <transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="templateForm.channels.includes('sms')" class="bg-white rounded-xl border border-blue-200 shadow-sm ring-1 ring-blue-50 overflow-hidden">
                        <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                             <h3 class="text-base font-bold text-blue-900 flex items-center gap-2">
                                <span class="bg-blue-200 p-1.5 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                </span>
                                {{ $t('communication.sms_settings') }}
                            </h3>
                        </div>

                        <div class="p-6 space-y-6">
                            <Input 
                                v-model="templateForm.sms_sender_name"
                                :label="$t('communication.sender_name')"
                                :placeholder="$t('communication.sender_name_placeholder')"
                                :error="templateForm.errors.sms_sender_name"
                                :hint="$t('communication.sender_name_hint')"
                                class="max-w-md"
                            />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $t('communication.sms_message') }} (EN) <span class="text-red-500">*</span>
                                    </label>
                                    <textarea 
                                        v-model="templateForm.sms_content_en"
                                        rows="5"
                                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm leading-relaxed"
                                        :placeholder="$t('communication.sms_message_placeholder')"
                                        :required="templateForm.channels.includes('sms')"
                                        maxlength="160"
                                    ></textarea>
                                    <div class="flex justify-between mt-1.5">
                                        <p class="text-xs text-gray-500">{{ $t('communication.sms_length_hint') }}</p>
                                        <p class="text-xs font-bold font-mono" :class="(templateForm.sms_content_en?.length || 0) > 160 ? 'text-red-600' : 'text-gray-400'">
                                            {{ templateForm.sms_content_en?.length || 0 }}/160
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1 text-right">
                                        {{ $t('communication.sms_message') }} (AR) <span class="text-red-500">*</span>
                                    </label>
                                    <textarea 
                                        v-model="templateForm.sms_content_ar"
                                        rows="5"
                                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm text-right leading-relaxed"
                                        dir="rtl"
                                        :placeholder="$t('communication.sms_message_placeholder')"
                                        :required="templateForm.channels.includes('sms')"
                                        maxlength="160"
                                    ></textarea>
                                    <div class="flex justify-between mt-1.5">
                                        <p class="text-xs text-gray-500">{{ $t('communication.sms_length_hint') }}</p>
                                        <p class="text-xs font-bold font-mono" :class="(templateForm.sms_content_ar?.length || 0) > 160 ? 'text-red-600' : 'text-gray-400'">
                                            {{ templateForm.sms_content_ar?.length || 0 }}/160
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Email Configuration -->
                <transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="templateForm.channels.includes('email')" class="bg-white rounded-xl border border-purple-200 shadow-sm ring-1 ring-purple-50 overflow-hidden">
                        <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
                            <h3 class="text-base font-bold text-purple-900 mb-0 flex items-center gap-2">
                                <span class="bg-purple-200 p-1.5 rounded-lg">
                                    <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                {{ $t('communication.email_settings') }}
                            </h3>
                        </div>

                        <div class="p-6 space-y-6">
                            <!-- Email Subject -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <Input 
                                    v-model="templateForm.subject_en"
                                    :label="$t('communication.email_subject') + ' (EN)'"
                                    :placeholder="$t('communication.email_subject_placeholder')"
                                    :required="templateForm.channels.includes('email')"
                                    :error="templateForm.errors.subject_en"
                                />
                                <Input 
                                    v-model="templateForm.subject_ar"
                                    :label="$t('communication.email_subject') + ' (AR)'"
                                    :placeholder="$t('communication.email_subject_placeholder')"
                                    :required="templateForm.channels.includes('email')"
                                    :error="templateForm.errors.subject_ar"
                                    dir="rtl"
                                />
                            </div>

                            <div class="border-t border-gray-100 pt-2"></div>

                            <!-- Email Header -->
                            <Input 
                                v-model="templateForm.email_header"
                                :label="$t('communication.email_header')"
                                :placeholder="$t('communication.email_header_placeholder')"
                                :error="templateForm.errors.email_header"
                                :hint="$t('communication.email_header_hint')"
                            />

                            <!-- Email Content -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ $t('communication.email_body') }} (EN) <span class="text-red-500">*</span>
                                    </label>
                                    <textarea 
                                        v-model="templateForm.content_en"
                                        rows="10"
                                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 leading-relaxed p-4"
                                        :placeholder="$t('communication.email_body_placeholder')"
                                        :required="templateForm.channels.includes('email')"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">
                                        {{ $t('communication.email_body') }} (AR) <span class="text-red-500">*</span>
                                    </label>
                                    <textarea 
                                        v-model="templateForm.content_ar"
                                        rows="10"
                                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-right leading-relaxed p-4"
                                        dir="rtl"
                                        :placeholder="$t('communication.email_body_placeholder')"
                                        :required="templateForm.channels.includes('email')"
                                    ></textarea>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 italic bg-gray-50 p-2 rounded-lg border border-gray-100">
                                💡 {{ $t('communication.email_body_hint') }}
                            </p>

                            <!-- Email Footer -->
                            <Input 
                                v-model="templateForm.email_footer"
                                :label="$t('communication.email_footer')"
                                :placeholder="$t('communication.email_footer_placeholder')"
                                :error="templateForm.errors.email_footer"
                                :hint="$t('communication.email_footer_hint')"
                            />
                        </div>
                    </div>
                </transition>

                <!-- Timing Configuration -->
                <div class="bg-white rounded-xl border border-green-200 shadow-sm overflow-hidden">
                    <div class="bg-green-50 px-6 py-4 border-b border-green-100">
                        <h3 class="text-base font-bold text-green-900 flex items-center gap-2">
                            <span class="bg-green-200 p-1.5 rounded-lg">
                                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            {{ $t('communication.timing_setup') }}
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <Select 
                                    v-model="templateForm.timing_type"
                                    :label="$t('communication.when')"
                                    :options="timingTypeOptions"
                                />
                            </div>
                            <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
                                <div v-if="templateForm.timing_type !== 'immediately'">
                                    <Input 
                                        v-model="templateForm.timing_days"
                                        :label="$t('communication.days')"
                                        type="number"
                                        min="0"
                                        placeholder="1"
                                    />
                                </div>
                            </transition>
                            <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
                                <div v-if="templateForm.timing_type !== 'immediately'">
                                    <Input 
                                        v-model="templateForm.timing_time"
                                        :label="$t('communication.at_time')"
                                        type="time"
                                    />
                                </div>
                            </transition>
                        </div>
                        <div class="mt-4 p-4 bg-green-50/50 rounded-lg border border-green-100">
                            <p class="text-sm text-green-800 flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span v-if="templateForm.timing_type === 'immediately'">
                                    <strong>{{ $t('communication.instant_delivery') }}</strong> {{ $t('communication.instant_desc') }}
                                </span>
                                <span v-else>
                                    <strong>{{ $t('communication.scheduled') }}</strong> {{ $t('communication.scheduled_desc', { days: templateForm.timing_days, type: templateForm.timing_type, time: templateForm.timing_time }) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Conditions Area -->
                <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
                    <div class="bg-amber-50 px-6 py-4 border-b border-amber-100">
                        <h3 class="text-base font-bold text-amber-900 flex items-center gap-2">
                            <span class="bg-amber-200 p-1.5 rounded-lg">
                                <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                            </span>
                            {{ $t('communication.conditions') }}
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-6 bg-amber-50/50 p-3 rounded-lg border border-amber-100 italic">
                            {{ $t('communication.conditions_desc') }}
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                             <template v-if="templateForm.trigger_event === 'feedback_received'">
                                <Input 
                                    v-model="templateForm.conditions.min_rating"
                                    :label="$t('communication.min_rating')"
                                    type="number"
                                    min="1"
                                    max="5"
                                    placeholder="1"
                                    :hint="$t('communication.min_rating_hint')"
                                />
                                <Input 
                                    v-model="templateForm.conditions.max_rating"
                                    :label="$t('communication.max_rating')"
                                    type="number"
                                    min="1"
                                    max="5"
                                    placeholder="5"
                                    :hint="$t('communication.max_rating_hint')"
                                />
                             </template>
                             <template v-else>
                                 <Input 
                                    v-model="templateForm.conditions.min_order_amount"
                                    :label="$t('communication.min_order_amount')"
                                    type="number"
                                    placeholder="0"
                                    :hint="$t('communication.min_order_hint')"
                                 />
                                 <Input 
                                    v-model="templateForm.conditions.min_orders_count"
                                    :label="$t('communication.min_past_orders')"
                                    type="number"
                                    placeholder="0"
                                    :hint="$t('communication.min_past_orders_hint')"
                                 />
                                 <Input 
                                    v-if="templateForm.trigger_event === 'churn_risk'"
                                    v-model="templateForm.conditions.days_since_last_order"
                                    :label="$t('communication.days_since_last_order')"
                                    type="number"
                                    placeholder="30"
                                    :hint="$t('communication.days_inactivity_hint')"
                                 />
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <input 
                        type="checkbox" 
                        v-model="templateForm.is_active"
                        id="is_active"
                        class="rounded border-gray-300 text-green-600 focus:ring-green-500 h-5 w-5"
                    >
                    <label for="is_active" class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <svg class="w-5 h-5" :class="templateForm.is_active ? 'text-green-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $t('communication.activate_rule') }}
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <Button type="button" variant="secondary" @click="closeTemplateModal">{{ $t('common.cancel') }}</Button>
                    <Button type="submit" :loading="templateForm.processing" class="px-6">
                        <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ editingTemplate ? $t('communication.update_rule') : $t('communication.create_rule') }}
                    </Button>
                </div>
            </form>
        </Modal>
    </MainLayout>
</template>



<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
// @ts-ignore
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import { computed } from 'vue';
import { useFeatures } from '@/Composables/useFeatures';

const { hasFeature } = useFeatures();
const { t } = useI18n();

interface TemplateCondition {
    min_order_amount?: number | null;
    min_orders_count?: number | null;
    days_since_last_order?: number | null;
    min_rating?: number | null;
    max_rating?: number | null;
    loyalty_tier?: string;
    feedback_points?: number | null;
    google_review_link?: string;
    delay_val?: number;
    delay_unit?: string;
}

interface Template {
    id: string;
    name: string;
    trigger_event: string;
    channels: string[];
    channel?: string; // Legacy support
    subject?: string;
    content?: string;
    subject_en?: string;
    subject_ar?: string;
    content_en?: string;
    content_ar?: string;
    sms_sender_name?: string;
    sms_content?: string;
    sms_content_en?: string;
    sms_content_ar?: string;
    email_header?: string;
    email_footer?: string;
    conditions?: TemplateCondition;
    is_active: boolean;
    timing_type?: string;
    timing_days?: number;
    timing_time?: string;
    logs_count?: number;
}

interface Bundle {
    id: string;
    name: string;
    type: string;
    currency: string;
    price: string;
    quantity: number;
}

// Logs from Laravel Paginator match PaginationMeta structure + data array
interface LogsPaginator {
    data: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: any[]; // Laravel standard links array
}

const props = defineProps<{
    balances: { sms: number; email: number };
    logs: LogsPaginator;
    bundles: Bundle[];
    templates: Template[];
    filters: any;
}>();

const route = (window as any).route;
// Initialize activeTab from params if available (e.g. redirected from showLogs)
const activeTab = ref(props.filters.active_tab || 'templates');

// --- Search & Filters ---
const params = ref({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    status: props.filters?.status || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    // Clear template_id if switching tabs or filtered
    template_id: props.filters?.template_id || '', 
});

const channelOptions = computed(() => [
    { label: t('communication.all_channels'), value: '' },
    { label: t('communication.sms'), value: 'sms' },
    { label: t('communication.email'), value: 'email' }
]);

const statusOptions = computed(() => [
    { label: t('communication.all_statuses'), value: '' },
    { label: t('common.sent'), value: 'sent' },
    { label: t('common.failed'), value: 'failed' }
]);

const timingUnitOptions = computed(() => [
    { label: t('communication.minutes'), value: 'minutes' },
    { label: t('communication.hours'), value: 'hours' },
    { label: t('communication.days'), value: 'days' }
]);

const timingTypeOptions = computed(() => [
    { label: '⚡ ' + t('communication.immediately'), value: 'immediately' },
    { label: '⏪ ' + t('communication.before_event'), value: 'before' },
    { label: '⏩ ' + t('communication.after_event'), value: 'after' }
]);

const triggerEventOptions = computed(() => [
    { label: '📝 ' + t('communication.trigger_registration'), value: 'registration' },
    { label: '🛒 ' + t('communication.trigger_order_created'), value: 'order_created' },
    { label: '✅ ' + t('communication.trigger_order_completed'), value: 'order_completed' },
    { label: '❌ ' + t('communication.trigger_order_cancelled'), value: 'order_cancelled' },
    { label: '🎂 ' + t('communication.trigger_birthday'), value: 'birthday' },
    { label: '⚠️ ' + t('communication.trigger_churn_risk'), value: 'churn_risk' },
    { label: '⭐ ' + t('communication.trigger_feedback_received'), value: 'feedback_received' }
]);

const onDateRangeUpdate = (range: { startDate: string; endDate: string }) => {
    params.value.date_from = range.startDate;
    params.value.date_to = range.endDate;
};

watch(
    params,
    debounce((value: any) => {
        router.get(route('communication.index'), { ...value, active_tab: activeTab.value }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 300),
    { deep: true }
);

// Watch tab change to clear relevant filters if needed
watch(activeTab, (val) => {
    if (val !== 'logs') {
        // clear logs specific filters if leaving logs tab?
        // For now, let's keep them persistent as per standard behavior
    }
});

// --- Bundles ---
const purchase = (bundle: Bundle) => {
    if (confirm(t('communication.confirm_purchase_bundle', { name: bundle.name, currency: bundle.currency, price: bundle.price }))) {
        router.post(route('communication.bundles.purchase', bundle.id));
    }
};

// --- Templates ---
const showTemplateModal = ref(false);
const editingTemplate = ref<Template | null>(null);

const templateForm = useForm({
    name: '',
    channels: [] as string[],
    trigger_event: 'registration',
    subject: '',
    content: '',
    subject_en: '',
    subject_ar: '',
    content_en: '',
    content_ar: '',
    sms_sender_name: '',
    sms_content: '',
    sms_content_en: '',
    sms_content_ar: '',
    email_header: '',
    email_footer: '',
    conditions: {
        min_order_amount: null as number | null,
        min_orders_count: null as number | null,
        days_since_last_order: null as number | null,
        min_rating: null as number | null,
        max_rating: null as number | null,
        loyalty_tier: ''
    },
    is_active: true,
    timing_type: 'immediately',
    timing_days: 0,
    timing_time: '12:00'
});

// --- Feedback Settings ---
const feedbackTemplateId = ref<string|null>(null);
const feedbackForm = useForm({
    is_active: false,
    channels: ['sms'],
    message_body: '',
    email_subject: '',
    email_content: '',
    min_order_amount: null as number | null,
    min_orders_count: null as number | null,
    timing_mode: 'immediately', 
    timing_val: 1,
    timing_unit: 'hours',
    feedback_points: null as number | null,
    google_review_link: ''
});

// Initialize form from existing template
watch(() => props.templates, (newTemplates) => {
    // ...
    const existing = newTemplates.find((t: Template) => t.trigger_event === 'order_completed_feedback');
    if (existing) {
        feedbackTemplateId.value = existing.id;
        feedbackForm.is_active = !!existing.is_active;
        feedbackForm.channels = existing.channels || (existing.channel ? [existing.channel] : ['sms']);
        
        feedbackForm.message_body = existing.sms_content || existing.sms_content_en || t('communication.message_placeholder');
        
        feedbackForm.email_subject = existing.subject || existing.subject_en || '';
        feedbackForm.email_content = existing.content || existing.content_en || '';
        
        feedbackForm.min_order_amount = existing.conditions?.min_order_amount || null;
        feedbackForm.min_orders_count = existing.conditions?.min_orders_count || null;
        feedbackForm.feedback_points = existing.conditions?.feedback_points || null;
        feedbackForm.google_review_link = existing.conditions?.google_review_link || '';

        // Load Timing
        if (existing.timing_type === 'custom_delay' && existing.conditions?.delay_unit) {
            feedbackForm.timing_mode = 'delay';
            feedbackForm.timing_val = existing.conditions?.delay_val || 1;
            feedbackForm.timing_unit = existing.conditions.delay_unit;
        } else if (existing.timing_type === 'delay_1_hour') {
            feedbackForm.timing_mode = 'delay';
            feedbackForm.timing_val = 1;
            feedbackForm.timing_unit = 'hours';
        } else if (existing.timing_type === 'delay_24_hours') {
            feedbackForm.timing_mode = 'delay';
            feedbackForm.timing_val = 24;
            feedbackForm.timing_unit = 'hours';
        } else {
            feedbackForm.timing_mode = 'immediately';
            feedbackForm.timing_val = 1;
            feedbackForm.timing_unit = 'hours';
        }

    } else {
        feedbackForm.channels = ['sms'];
        feedbackForm.message_body = t('communication.message_placeholder');
        feedbackForm.email_subject = '';
        feedbackForm.email_content = '';
        feedbackForm.timing_mode = 'immediately';
        feedbackForm.feedback_points = null;
    }
}, { immediate: true });

const saveFeedbackSettings = () => {
    // Validate SMS
    if (feedbackForm.channels.includes('sms')) {
         if (!feedbackForm.message_body.includes('{{customer_name}}') || !feedbackForm.message_body.includes('{{feedback_link}}')) {
            alert(t('communication.alert_message_variables') + ' (SMS)');
            return;
        }
    }
    // Validate Email
    if (feedbackForm.channels.includes('email')) {
         if (!feedbackForm.email_content.includes('{{customer_name}}') || !feedbackForm.email_content.includes('{{feedback_link}}')) {
            alert(t('communication.alert_message_variables') + ' (Email)');
            return;
        }
         if (!feedbackForm.email_subject) {
            alert(t('communication.alert_subject_required'));
            return;
        }
    }
    
    // Ensure at least one channel
    if (feedbackForm.channels.length === 0) {
        alert(t('communication.select_channel_required'));
        return;
    }

    const payload = {
        name: t('communication.automated_feedback_requests'),
        trigger_event: 'order_completed_feedback',
        channels: feedbackForm.channels,
        
        // SMS Content
        sms_content: feedbackForm.message_body,
        sms_content_en: feedbackForm.message_body, 
        sms_content_ar: feedbackForm.message_body, 

        // Email Content
        subject: feedbackForm.email_subject,
        subject_en: feedbackForm.email_subject,
        subject_ar: feedbackForm.email_subject,
        content: feedbackForm.email_content,
        content_en: feedbackForm.email_content,
        content_ar: feedbackForm.email_content,

        is_active: feedbackForm.is_active,
        timing_type: feedbackForm.timing_mode === 'delay' ? 'custom_delay' : 'immediately',
        timing_days: 0,
        timing_time: '12:00',
        conditions: {
            min_order_amount: feedbackForm.min_order_amount,
            min_orders_count: feedbackForm.min_orders_count,
            feedback_points: feedbackForm.feedback_points,
            delay_val: feedbackForm.timing_mode === 'delay' ? feedbackForm.timing_val : null,
            delay_unit: feedbackForm.timing_mode === 'delay' ? feedbackForm.timing_unit : null,
            google_review_link: feedbackForm.google_review_link
        }
    };

    if (feedbackTemplateId.value) {
        // @ts-ignore
        feedbackForm.transform(() => payload).put(route('communication.templates.update', feedbackTemplateId.value), {
            preserveScroll: true,
            onSuccess: () => {
                // success
            }
        });
    } else {
        // @ts-ignore
        feedbackForm.transform(() => payload).post(route('communication.templates.store'), {
            preserveScroll: true,
            onSuccess: () => {
                // success
            }
        });
    }
};

const openTemplateModal = (template: Template | null = null) => {
    if (template) {
        editingTemplate.value = template;
        templateForm.name = template.name;
        // Fix: backend might send 'channels' or older 'channel' if we didn't migrate old data perfectly.
        // But we did migration.
        templateForm.channels = template.channels || (template.channel ? [template.channel] : []);
        templateForm.trigger_event = template.trigger_event;
        templateForm.subject = template.subject || '';
        templateForm.content = template.content || '';
        templateForm.subject_en = template.subject_en || '';
        templateForm.subject_ar = template.subject_ar || '';
        templateForm.content_en = template.content_en || '';
        templateForm.content_ar = template.content_ar || '';
        templateForm.sms_sender_name = template.sms_sender_name || '';
        templateForm.sms_content = template.sms_content || '';
        templateForm.sms_content_en = template.sms_content_en || '';
        templateForm.sms_content_ar = template.sms_content_ar || '';
        templateForm.email_header = template.email_header || '';
        templateForm.email_footer = template.email_footer || '';
        templateForm.conditions = {
            min_order_amount: template.conditions?.min_order_amount || null,
            min_orders_count: template.conditions?.min_orders_count || null,
            days_since_last_order: template.conditions?.days_since_last_order || null,
            min_rating: template.conditions?.min_rating || null,
            max_rating: template.conditions?.max_rating || null,
            loyalty_tier: template.conditions?.loyalty_tier || ''
        };
       templateForm.is_active = !!template.is_active;
        templateForm.timing_type = template.timing_type || 'immediately';
        templateForm.timing_days = template.timing_days || 0;
        templateForm.timing_time = template.timing_time ? template.timing_time.substring(0, 5) : '12:00'; // Format H:i
    } else {
        editingTemplate.value = null;
        templateForm.reset();
        templateForm.clearErrors();
        templateForm.channels = ['email']; // default
        templateForm.timing_type = 'immediately';
        templateForm.timing_days = 0;
        templateForm.timing_time = '12:00';
    }
    showTemplateModal.value = true;
};

const closeTemplateModal = () => {
    showTemplateModal.value = false;
    templateForm.reset();
    editingTemplate.value = null;
};

const submitTemplate = () => {
    if (templateForm.channels.length === 0) {
        alert(t('communication.select_one_channel'));
        return;
    }
    if (editingTemplate.value) {
        templateForm.put(route('communication.templates.update', editingTemplate.value.id), {
            onSuccess: () => closeTemplateModal()
        });
    } else {
        templateForm.post(route('communication.templates.store'), {
            onSuccess: () => closeTemplateModal()
        });
    }
};

const deleteTemplate = (template: Template) => {
    if (confirm(t('communication.delete_rule_confirm'))) {
        router.delete(route('communication.templates.destroy', template.id));
    }
};

const showLogs = (template: Template) => {
    // Navigate to index but with template_id filter and active_tab=logs
    router.get(route('communication.index'), {
        template_id: template.id,
        active_tab: 'logs'
    });
};

const clearTemplateFilter = () => {
    params.value.template_id = '';
    router.get(route('communication.index'), { active_tab: 'logs' });
};
</script>
