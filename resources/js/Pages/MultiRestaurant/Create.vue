<template>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
        
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                 <div class="flex justify-center mb-6">
                     <Logo class="h-20 w-20" iconClass="w-20 h-20" :showText="true" />
                 </div>
                 <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl">
                     Add New Restaurant
                 </h1>
                 <p class="mt-4 text-xl text-gray-500 max-w-2xl mx-auto">
                     Expand your business by adding another location
                 </p>
                 <div class="mt-4">
                     <a href="/en/select-restaurant" class="text-primary hover:text-primary-hover font-medium">← Back to restaurant list</a>
                 </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
                <div class="p-8 md:p-10">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Restaurant Details</h2>
                        <p class="text-gray-500 mt-1">This restaurant will be added to your existing subscription plan.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                            <Input
                                v-model="form.restaurant_name"
                                label="Restaurant Name"
                                type="text"
                                placeholder="e.g. My Great Bistro - Downtown"
                                required
                                :error="form.errors.restaurant_name"
                            />
                        </div>

                        <!-- Loyalty Setup -->
                        <div class="pt-6 border-t border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Loyalty Program Setup</h3>
                            <div class="space-y-4">
                                <label class="block text-sm font-medium text-gray-700">How should customers earn points?</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div 
                                        @click="form.earning_method_type = 'order_total'"
                                        class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200"
                                        :class="form.earning_method_type === 'order_total' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-full" :class="form.earning_method_type === 'order_total' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">Per Spend</div>
                                                <div class="text-xs text-gray-500">Earn points based on bill total</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div 
                                        @click="form.earning_method_type = 'visit'"
                                        class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200"
                                        :class="form.earning_method_type === 'visit' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-full" :class="form.earning_method_type === 'visit' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">Per Visit</div>
                                                <div class="text-xs text-gray-500">Fixed points per order/visit</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 bg-gray-50 p-4 rounded-xl">
                                     <Input
                                        v-model="form.earning_points"
                                        :label="form.earning_method_type === 'order_total' ? 'Points per 1 AED Spent' : 'Points per Visit'"
                                        type="number"
                                        min="1"
                                        required
                                        :error="(form.errors as any).earning_points"
                                    />
                                    <p class="text-xs text-gray-500 mt-2">
                                        {{ form.earning_method_type === 'order_total' ? 'Example: If set to 1, a 100 AED order earns 100 points.' : 'Example: If set to 10, every visit earns 10 points regardless of spend.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <Button
                                type="submit"
                                :loading="form.processing"
                                block
                                size="xl"
                                class="w-full text-lg font-bold py-4 rounded-xl"
                            >
                                Create Restaurant
                            </Button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';

const form = useForm({
    restaurant_name: '',
    earning_method_type: 'order_total',
    earning_points: 1,
});

const submit = () => {
    form.post((window as any).route('restaurants.store'));
};
</script>
