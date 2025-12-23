<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('menu.title') }}</h1>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">Manage your restaurant menu and categories</p>
                </div>
                <Button 
                    v-if="hasPermission('create_category')"
                    @click="openCategoryModal()" 
                    variant="primary"
                    size="md"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </template>
                    {{ $t('menu.add_category') }}
                </Button>
            </div>

            <!-- Empty State -->
            <div v-if="categories.length === 0" class="glass-card rounded-2xl p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-primary/10 flex items-center justify-center">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No menu categories yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">{{ $t('menu.no_categories') }}</p>
                    <Button 
                        @click="openCategoryModal()"
                        variant="primary"
                        size="lg"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </template>
                        {{ $t('menu.create_first_category') }}
                    </Button>
                </div>
            </div>

            <!-- Categories List -->
            <div v-else class="space-y-6">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="glass-card rounded-2xl overflow-hidden card-hover animate-slide-up"
                >
                    <!-- Category Header -->
                    <div class="p-6 border-b border-gray-100/50 dark:border-gray-700/50">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ getCategoryName(category.name) }}
                                    </h2>
                                    <p v-if="category.description" class="text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ category.description }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button 
                                    v-if="hasPermission('edit_category')"
                                    @click="editCategory(category)" 
                                    variant="ghost"
                                    size="sm"
                                >
                                    <template #icon>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </template>
                                    Edit
                                </Button>
                                <Button 
                                    v-if="hasPermission('create_item')"
                                    @click="addItem(category)" 
                                    variant="success"
                                    size="sm"
                                >
                                    <template #icon>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </template>
                                    {{ $t('menu.add_item') }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Items Grid -->
                    <div class="p-6">
                        <div v-if="category.items && category.items.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="item in category.items"
                                :key="item.id"
                                class="group relative p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 hover:border-primary/30 hover:shadow-md transition-all duration-200"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary transition-colors">
                                            {{ getItemName(item.name) }}
                                        </h3>
                                        <p v-if="item.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                            {{ item.description }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-lg font-bold text-gradient">
                                        {{ item.currency }} {{ item.price }}
                                    </span>
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button 
                                            v-if="hasPermission('edit_item')"
                                            @click="editItem(category, item)"
                                            class="p-1.5 text-gray-400 hover:text-primary rounded-lg hover:bg-primary/10 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center">
                            <p class="text-gray-400 dark:text-gray-500">{{ $t('menu.no_items') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>

    <!-- Category Modal -->
    <Modal :show="showCategoryModal" @close="closeCategoryModal" :title="editingCategory ? $t('menu.edit_category') : $t('menu.add_category')" size="md">
        <form @submit.prevent="submitCategory" class="space-y-6">
            <div class="grid grid-cols-1 gap-4">
                <Input 
                    v-model="categoryForm.name.en"
                    :label="$t('common.name_en')"
                    placeholder="e.g. Starters"
                    required
                    :error="(categoryForm.errors as any)['name.en']"
                />
                <Input 
                    v-model="categoryForm.name.ar"
                    :label="$t('common.name_ar')"
                    placeholder="e.g. مقبلات"
                    class="text-right"
                    dir="rtl"
                    :error="(categoryForm.errors as any)['name.ar']"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('common.description') }}</label>
                <textarea 
                    v-model="categoryForm.description"
                    rows="3"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                    placeholder="Optional description..."
                ></textarea>
            </div>

            <div class="flex justify-between pt-4">
                <Button 
                    v-if="editingCategory && hasPermission('delete_category')"
                    type="button" 
                    variant="danger" 
                    @click="deleteCategory"
                >
                    {{ $t('common.delete') }}
                </Button>
                <div class="flex gap-3 ml-auto">
                    <Button type="button" variant="secondary" @click="closeCategoryModal">{{ $t('common.cancel') }}</Button>
                    <Button type="submit" :loading="categoryForm.processing">{{ editingCategory ? $t('common.save') : $t('common.create') }}</Button>
                </div>
            </div>
        </form>
    </Modal>

    <!-- Item Modal -->
    <Modal :show="showItemModal" @close="closeItemModal" :title="editingItem ? $t('menu.edit_item') : $t('menu.add_item')" size="lg">
        <form @submit.prevent="submitItem" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input 
                    v-model="itemForm.name.en"
                    :label="$t('common.name_en')"
                    placeholder="e.g. Beef Burger"
                    required
                    :error="(itemForm.errors as any)['name.en']"
                />
                <Input 
                    v-model="itemForm.name.ar"
                    :label="$t('common.name_ar')"
                    placeholder="e.g. برجر لحم"
                    class="text-right"
                    dir="rtl"
                    :error="(itemForm.errors as any)['name.ar']"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input 
                    v-model="itemForm.price"
                    :label="$t('common.price')"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    :error="itemForm.errors.price"
                />
                <!-- Add image upload later if needed -->
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('common.description') }}</label>
                <textarea 
                    v-model="itemForm.description"
                    rows="3"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                    placeholder="Ingredients, details..."
                ></textarea>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <h4 class="font-medium text-gray-900 dark:text-white mb-3">Recipe / Ingredients</h4>
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <div class="flex-1">
                             <select 
                                v-model="newIngredientId" 
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary"
                             >
                                 <option :value="null">Select Ingredient...</option>
                                 <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">
                                    {{ getCategoryName(ing.name) }} ({{ ing.unit }})
                                 </option>
                             </select>
                        </div>
                        <div class="w-32">
                            <Input 
                                v-model="newIngredientQty" 
                                type="number" 
                                step="0.0001" 
                                placeholder="Qty" 
                                @keypress.enter.prevent="addIngredient"
                            />
                        </div>
                        <Button type="button" @click="addIngredient" size="sm" :disabled="!newIngredientId">Add</Button>
                    </div>

                    <!-- List -->
                    <div v-if="itemForm.ingredients.length > 0" class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 space-y-2 max-h-40 overflow-y-auto">
                        <div v-for="(ing, index) in itemForm.ingredients" :key="index" class="flex justify-between items-center text-sm p-2 bg-white dark:bg-gray-800 rounded shadow-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                {{ getCategoryName(getIngredient(ing.id)?.name) }}
                            </span>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-500 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                    {{ ing.quantity }} {{ getIngredient(ing.id)?.unit }}
                                </span>
                                <button type="button" @click="removeIngredient(index)" class="text-red-500 hover:text-red-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-500 italic">No ingredients linked to this item.</p>
                </div>
            </div>

            <div class="flex justify-between pt-4">
                <Button 
                    v-if="editingItem && hasPermission('delete_item')"
                    type="button" 
                    variant="danger" 
                    @click="deleteItem"
                >
                    {{ $t('common.delete') }}
                </Button>
                <div class="flex gap-3 ml-auto">
                    <Button type="button" variant="secondary" @click="closeItemModal">{{ $t('common.cancel') }}</Button>
                    <Button type="submit" :loading="itemForm.processing">{{ editingItem ? $t('common.save') : $t('common.create') }}</Button>
                </div>
            </div>
        </form>
    </Modal>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { hasPermission } = usePermissions();

const { t, locale } = useI18n();
const page = usePage();
const route = (window as any).route;

const props = defineProps<{
    categories: any[];
    ingredients: any[];
}>();

const isRtl = computed(() => page.props.isRtl as boolean);

// Category State
const showCategoryModal = ref(false);
const editingCategory = ref<any>(null);
const categoryForm = useForm({
    name: { en: '', ar: '' },
    description: '',
    sort_order: 0
});

// Item State
const showItemModal = ref(false);
const editingItem = ref<any>(null);
const itemForm = useForm({
    menu_category_id: null as number | null,
    name: { en: '', ar: '' },
    description: '',
    price: '',
    image: null,
    sort_order: 0,
    allergens: [],
    ingredients: [] as any[]
});

const newIngredientId = ref<number | null>(null);
const newIngredientQty = ref<number | string>('');

const getIngredient = (id: number) => props.ingredients.find((i: any) => i.id === id);

const addIngredient = () => {
    if (!newIngredientId.value || !newIngredientQty.value) return;
    
    // Check if already exists
    const existing = itemForm.ingredients.find(i => i.id === newIngredientId.value);
    if (existing) {
        existing.quantity = Number(existing.quantity) + Number(newIngredientQty.value);
    } else {
        itemForm.ingredients.push({
            id: newIngredientId.value,
            quantity: Number(newIngredientQty.value)
        });
    }
    
    newIngredientId.value = null;
    newIngredientQty.value = '';
};

const removeIngredient = (index: number) => {
    itemForm.ingredients.splice(index, 1);
};

const getCategoryName = (name: any) => {
    if (typeof name === 'string') return name;
    return name[locale.value] || name.en || Object.values(name)[0];
};

const getItemName = (name: any) => {
    if (typeof name === 'string') return name;
    return name[locale.value] || name.en || Object.values(name)[0];
};

// Category Actions
const openCategoryModal = (category: any = null) => {
    if (category) {
        editingCategory.value = category;
        categoryForm.name.en = category.name.en || category.name;
        categoryForm.name.ar = category.name.ar || '';
        categoryForm.description = category.description;
        categoryForm.sort_order = category.sort_order;
    } else {
        editingCategory.value = null;
        categoryForm.reset();
        categoryForm.clearErrors();
    }
    showCategoryModal.value = true;
};

const closeCategoryModal = () => {
    showCategoryModal.value = false;
    categoryForm.reset();
    editingCategory.value = null;
};

const submitCategory = () => {
    if (editingCategory.value) {
        categoryForm.put(route('menu.categories.update', editingCategory.value.id), {
            onSuccess: () => closeCategoryModal()
        });
    } else {
        categoryForm.post(route('menu.categories.store'), {
            onSuccess: () => closeCategoryModal()
        });
    }
};

const editCategory = (category: any) => {
    openCategoryModal(category);
};

const deleteCategory = () => {
    if (!editingCategory.value) return;
    
    if (confirm(t('common.confirm_delete'))) {
        router.delete(route('menu.categories.destroy', editingCategory.value.id), {
            onSuccess: () => closeCategoryModal()
        });
    }
};

// Item Actions
const openItemModal = (category: any, item: any = null) => {
    itemForm.menu_category_id = category.id;
    
    if (item) {
        editingItem.value = item;
        itemForm.name.en = item.name.en || item.name;
        itemForm.name.ar = item.name.ar || '';
        itemForm.description = item.description;
        itemForm.price = item.price;
        itemForm.sort_order = item.sort_order;
        itemForm.ingredients = item.ingredients ? item.ingredients.map((i: any) => ({
            id: i.id,
            quantity: i.pivot.quantity
        })) : [];
    } else {
        editingItem.value = null;
        // Keep category_id
        const catId = itemForm.menu_category_id;
        itemForm.reset();
        itemForm.menu_category_id = catId;
        itemForm.clearErrors();
    }
    showItemModal.value = true;
};

const closeItemModal = () => {
    showItemModal.value = false;
    itemForm.reset();
    editingItem.value = null;
};

const submitItem = () => {
    if (editingItem.value) {
        itemForm.put(route('menu.items.update', editingItem.value.id), {
            onSuccess: () => closeItemModal()
        });
    } else {
        itemForm.post(route('menu.items.store'), {
            onSuccess: () => closeItemModal()
        });
    }
};

const addItem = (category: any) => {
    openItemModal(category);
};

const editItem = (category: any, item: any) => {
    openItemModal(category, item);
};

const deleteItem = () => {
    if (!editingItem.value) return;

    if (confirm(t('common.confirm_delete'))) {
        router.delete(route('menu.items.destroy', editingItem.value.id), {
            onSuccess: () => closeItemModal()
        });
    }
};
</script>
