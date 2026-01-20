<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $t('menu.title') }}</h1>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $t('menu.subtitle') || 'Manage your restaurant menu and categories' }}</p>
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
                <!-- Import Button -->
                <Button 
                    v-if="hasPermission('create_item')"
                    @click="showImportModal = true" 
                    variant="secondary"
                    size="md"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </template>
                    {{ $t('menu.import_items') }}
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
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $t('charts.no_data') }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">{{ $t('menu.no_categories') }}</p>
                    <Button 
                        v-if="hasPermission('create_category')"
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
                    :class="{'opacity-60 grayscale-[50%]': !category.is_active}"
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
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        {{ getCategoryName(category.name) }}
                                        <span v-if="!category.is_active" class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded border border-gray-300 dark:border-gray-600">{{ $t('common.inactive') }}</span>
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
                                    </template>{{ $t('common.edit') }}</Button>
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
                                :class="{'opacity-60 grayscale-[50%]': !item.is_available}"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="mb-4 aspect-[4/3] bg-gray-50 rounded-lg overflow-hidden relative group-hover:shadow-inner transition-shadow">
                                            <div v-if="item.images && item.images.length > 0" class="h-full w-full">
                                                <Carousel 
                                                    :images="item.images" 
                                                    heightClass="h-full" 
                                                />
                                            </div>
                                            <img 
                                                v-else-if="item.image" 
                                                :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image" 
                                                alt="Item Image" 
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                            />
                                             <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                        </div>
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
                                    
                                    <!-- Inventory Status -->
                                    <div class="flex gap-2">
                                         <div v-if="!item.is_available" class="group/tooltip relative">
                                            <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs font-bold rounded uppercase">
                                                {{ $t('common.inactive') }}
                                            </span>
                                        </div>
                                        <div v-else-if="item.inventory_status?.sold_out" class="group/tooltip relative">
                                            <span class="px-2 py-1 bg-red-100 text-red-600 text-xs font-bold rounded uppercase">
                                                {{ $t('menu.sold_out') }}
                                            </span>
                                            <!-- Tooltip -->
                                            <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 w-48 bg-black/80 text-white text-xs rounded p-2 opacity-0 group-hover/tooltip:opacity-100 pointer-events-none transition-opacity z-10">
                                                {{ $t('menu.missing') }}: {{ item.inventory_status.missing_ingredients.join(', ') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                        <button 
                                            v-if="hasPermission('edit_item')"
                                            @click="editItem(category, item)"
                                            class="p-1.5 text-gray-400 hover:text-primary rounded-lg hover:bg-primary/10 transition-colors"
                                            :title="$t('common.edit')"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button 
                                            v-if="hasPermission('delete_item')"
                                            @click="deleteItem(item)"
                                            class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors"
                                            :title="$t('common.delete')"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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

            <Input
                v-model="categoryForm.description"
                :label="$t('common.description')"
                type="textarea"
                rows="3"
                placeholder="Optional description..."
            />

            <!-- Active Status Toggle -->
             <div class="flex items-center gap-2">
                <input 
                    type="checkbox" 
                    id="category_active"
                    v-model="categoryForm.is_active"
                    class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary/20 focus:ring-opacity-50"
                >
                <label for="category_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $t('common.active') }} ({{ $t('menu.visible_in_menu') }})
                </label>
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
    <Modal :show="showItemModal" @close="closeItemModal" :title="editingItem ? $t('menu.edit_item') : $t('menu.add_item')" size="2xl">
        <form @submit.prevent="submitItem" class="flex flex-col h-[70vh]">
            <!-- Tabs Header -->
            <div class="flex border-b border-gray-200 dark:border-gray-700 mb-0 overflow-x-auto bg-white dark:bg-gray-800 sticky top-0 z-10">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    type="button"
                    @click="activeTab = tab.id"
                    class="px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap outline-none focus:outline-none"
                    :class="activeTab === tab.id ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-1 custom-scrollbar">
                
                <!-- Tab: Details -->
                <div v-show="activeTab === 'details'" class="space-y-5 py-4">
                    <!-- Type Selector -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg flex items-center justify-between border border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('menu.item_type') }}</span>
                        <div class="flex bg-white dark:bg-gray-800 rounded p-1 shadow-sm">
                            <button type="button" @click="itemForm.type = 'item'" 
                                class="px-4 py-1.5 text-sm rounded transition-all"
                                :class="itemForm.type === 'item' ? 'bg-primary text-white shadow' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                                {{ $t('menu.single_item') }}
                            </button>
                            <button type="button" @click="itemForm.type = 'meal'" 
                                class="px-4 py-1.5 text-sm rounded transition-all"
                                :class="itemForm.type === 'meal' ? 'bg-primary text-white shadow' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                                {{ $t('menu.meal_bundle') }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input v-model="itemForm.name.en" :label="$t('common.name_en')" required />
                        <Input v-model="itemForm.name.ar" :label="$t('common.name_ar')" dir="rtl" class="text-right" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input v-model="itemForm.sku" :label="$t('menu.sku_code')" :placeholder="$t('menu.sku_placeholder')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('menu.categories') }}</label>
                            <Select 
                                v-model="itemForm.menu_category_id" 
                                :options="categories.map(cat => ({ value: cat.id, label: getCategoryName(cat.name) }))"
                                :placeholder="$t('menu.select_category')"
                                required 
                                class="w-full"
                            />
                        </div>
                        <Input v-model="itemForm.price" :label="$t('menu.selling_price')" type="number" step="0.01" required />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input v-model="itemForm.sort_order" :label="$t('menu.sort_order')" type="number" />
                         <!-- Images Section -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('menu.images') }}</label>
                            <div class="flex gap-2 mb-2">
                                <button type="button" @click="triggerFileInput" class="flex-1 h-[42px] border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center gap-2 text-gray-500 hover:border-primary hover:text-primary transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span class="text-xs font-medium uppercase">{{ $t('menu.upload') }}</span>
                                </button>
                                <button type="button" @click="showUnsplashPicker = true" class="flex-1 h-[42px] border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center gap-2 text-gray-500 hover:border-primary hover:text-primary transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-xs font-medium uppercase">{{ $t('menu.stock_image') }}</span>
                                </button>
                            </div>
                            <!-- Existing & New Images Grid -->
                             <div v-if="itemForm.kept_images.length > 0 || previewUrls.length > 0" class="grid grid-cols-4 gap-2">
                                <!-- Existing Images -->
                                <div v-for="(img, idx) in itemForm.kept_images" :key="'kept-' + idx" class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100">
                                    <img :src="img.startsWith('http') ? img : '/storage/' + img" class="w-full h-full object-cover" />
                                    <button type="button" @click="removeKeptImage(idx)" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <!-- New Images -->
                                <div v-for="(url, idx) in previewUrls" :key="'new-' + idx" class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100">
                                    <img :src="url" class="w-full h-full object-cover" />
                                     <button type="button" @click="removeNewImage(idx)" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <!-- Hidden File Input -->
                             <input 
                                type="file" 
                                ref="fileInputRef"
                                class="hidden" 
                                multiple 
                                accept="image/*"
                                @change="handleImageUpload"
                             />
                        </div>
                    </div>

                    <Input v-model="itemForm.description" :label="$t('common.description')" type="textarea" rows="3" :placeholder="$t('menu.description_placeholder')" />
                    
                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <input type="checkbox" id="item_available" v-model="itemForm.is_available" class="rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="item_available" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('menu.item_available_sale') }}</label>
                    </div>
                </div>

                <!-- Tab: Recipe -->
                <div v-show="activeTab === 'recipe'" class="space-y-5 py-4">
                     <!-- Cost Summary -->
                     <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-between border border-blue-100 dark:border-blue-800">
                        <div>
                            <p class="text-xs text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">{{ $t('menu.total_cost') }}</p>
                            <p class="text-xl font-black text-blue-700 dark:text-blue-300">{{ totalIngredientCost.toFixed(2) }} <span class="text-sm font-normal">{{ currency }}</span></p>
                        </div>
                        <div class="text-right">
                             <p class="text-xs text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">{{ $t('menu.est_profit') }}</p>
                             <p class="text-xl font-black" :class="estimatedProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                 {{ estimatedProfit.toFixed(2) }} <span class="text-sm font-normal">{{ currency }}</span>
                             </p>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700 space-y-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('menu.add_ingredient_recipe') }}</label>
                        <div class="flex flex-wrap gap-2">
                            <div class="flex-1 min-w-[200px]">
                                <Select 
                                    v-model="newIngredientId" 
                                    :options="ingredients.filter(i => !itemForm.ingredients.find(existing => existing.id === i.id)).map(ing => ({ value: ing.id, label: `${getCategoryName(ing.name)} (${ing.cost} / ${ing.unit})` }))"
                                    :placeholder="$t('menu.select_ingredient_placeholder')"
                                />
                            </div>
                            <div class="w-24">
                                <Input v-model="newIngredientQty" type="number" step="0.0001" :placeholder="$t('menu.qty')" @keypress.enter.prevent="addIngredient" />
                            </div>
                             <div class="w-24">
                                <Select 
                                    v-model="newIngredientUnit" 
                                    :options="getAvailableUnits"
                                    :placeholder="$t('menu.unit')"
                                />
                            </div>
                            <Button type="button" @click="addIngredient" :disabled="!newIngredientId || !newIngredientUnit">{{ $t('common.add') }}</Button>
                        </div>
                    </div>

                    <!-- Ingredients List -->
                    <div class="space-y-2">
                        <div v-for="(ing, index) in itemForm.ingredients" :key="index" class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                             <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold">
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ getCategoryName(getIngredient(ing.id)?.name) }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ ing.usage_quantity || ing.quantity }} {{ ing.usage_unit || getIngredient(ing.id)?.unit }} x {{ getIngredient(ing.id)?.cost }}
                                    </p>
                                </div>
                             </div>
                             <div class="flex items-center gap-3">
                                 <span class="font-bold text-gray-700 dark:text-gray-300">{{ (getIngredient(ing.id)?.cost * ing.quantity).toFixed(2) }} {{ currency }}</span>
                                 <button type="button" @click="removeIngredient(index)" class="text-gray-400 hover:text-red-500 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                 </button>
                             </div>
                        </div>
                        <p v-if="itemForm.ingredients.length === 0" class="text-center text-gray-500 py-8 italic">{{ $t('menu.no_ingredients_recipe') }}</p>
                    </div>
                </div>

                <!-- Tab: Bundles (Meal) -->
                <div v-show="activeTab === 'bundles'" v-if="itemForm.type === 'meal'" class="space-y-4 py-4">
                    <div class="flex justify-between items-center">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $t('menu.bundled_items') }}</h4>
                        <Button type="button" size="sm" @click="addBundleRow">{{ $t('menu.add_item') }}</Button>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(bundle, idx) in itemForm.bundles" :key="idx" class="flex gap-3 items-start p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700">
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 mb-1 block">{{ $t('menu.item_to_include') }}</label>
                                <Select 
                                    v-model="bundle.child_menu_item_id"
                                    :options="allItems.map(item => ({ value: item.id, label: getItemName(item.name) }))"
                                    :placeholder="$t('menu.select_item')"
                                />
                            </div>
                            <div class="w-24">
                                <label class="text-xs text-gray-500 mb-1 block">{{ $t('menu.qty') }}</label>
                                <Input v-model="bundle.quantity" type="number" min="1" />
                            </div>
                            <button type="button" @click="removeBundleRow(idx)" class="mt-6 text-red-500 p-2 hover:bg-red-50 rounded-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                         <p v-if="itemForm.bundles.length === 0" class="text-center text-gray-500 py-8 italic">{{ $t('menu.no_items_bundled') }}</p>
                    </div>
                </div>

                <!-- Tab: Extras -->
                <div v-show="activeTab === 'extras'" class="space-y-5 py-4">
                     <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $t('menu.paid_addons') }}</h4>
                            <p class="text-xs text-gray-500">{{ $t('menu.customize_item_desc') }}</p>
                        </div>
                        <Button type="button" size="sm" @click="addExtraRow" variant="secondary">+ {{ $t('menu.add_option') }}</Button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(extra, idx) in itemForm.extras" :key="idx" class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-800 relative group transition-shadow hover:shadow-md">
                            <button type="button" @click="removeExtraRow(idx)" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pr-8">
                                <Input v-model="extra.name.en" :label="$t('common.name_en')" placeholder="e.g. Extra Cheese" required />
                                <Input v-model="extra.name.ar" :label="$t('common.name_ar')" placeholder="e.g. جبne إضافية" dir="rtl" class="text-right" required />
                            </div>

                            <div class="flex flex-col md:flex-row gap-4 items-end">
                                <div class="w-full md:w-1/3">
                                    <Input v-model="extra.price" :label="$t('menu.extra_cost_price')" type="number" step="0.01" :prefix="currency" />
                                </div>
                                
                                <!-- Inventory Link -->
                                <div class="flex-1 w-full bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1">
                                        {{ $t('menu.inventory_deduction') }} 
                                        <span class="text-[10px] font-normal normal-case text-gray-400">{{ $t('menu.deduct_stock_desc') }}</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <div class="flex-1">
                                            <Select 
                                                v-model="extra.ingredient_id" 
                                                :options="[{value: null, label: $t('menu.no_deduction')}, ...ingredients.map(ing => ({ value: ing.id, label: `${getCategoryName(ing.name)} (${t('common.stock')}: ${ing.unit})` }))]"
                                                :placeholder="$t('menu.link_ingredient')"
                                            />
                                        </div>
                                        <div class="w-24" v-if="extra.ingredient_id">
                                            <Input v-model="extra.quantity" type="number" step="0.001" :placeholder="$t('menu.qty')" />
                                        </div>
                                         <div class="w-28" v-if="extra.ingredient_id">
                                            <Select 
                                                v-model="extra.unit" 
                                                :options="getCompatibleUnits(extra.ingredient_id)"
                                                :placeholder="$t('menu.unit')"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div v-if="itemForm.extras.length === 0" class="text-center py-10 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/20">
                        <p class="text-gray-500 font-medium">{{ $t('menu.no_extras_defined') }}</p>
                        <button type="button" @click="addExtraRow" class="text-primary hover:underline mt-2 text-sm font-medium">{{ $t('menu.add_first_extra') }}</button>
                    </div>
                </div>

            </div>

             <!-- Footer Actions -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center bg-white dark:bg-gray-800 shrink-0 mt-auto sticky bottom-0 z-20">
                <Button 
                    v-if="editingItem && hasPermission('delete_item')"
                    type="button" 
                    variant="danger" 
                    @click="deleteItem"
                >
                    {{ $t('common.delete') }}
                </Button>
                <div v-else></div> <!-- Spacer -->

                <div class="flex gap-3">
                    <Button type="button" variant="secondary" @click="closeItemModal">{{ $t('common.cancel') }}</Button>
                    <Button type="submit" :loading="itemForm.processing">{{ editingItem ? $t('common.save') : $t('common.create') }}</Button>
                </div>
            </div>
        </form>
    </Modal>

    <!-- Import Modal -->
    <Modal :show="showImportModal" @close="showImportModal = false" :title="$t('menu.import_items')" size="md">
        <form @submit.prevent="submitImport" class="space-y-6">
            <div class="bg-blue-50 p-4 rounded-lg flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <h4 class="font-bold text-blue-800 text-sm">{{ $t('menu.import_instructions') }}</h4>
                    <p class="text-sm text-blue-700 mt-1">
                        {{ $t('menu.import_step_1') }}<br>
                        {{ $t('menu.import_step_2') }}<br>
                        {{ $t('menu.import_step_3') }}
                    </p>
                    <a :href="route('menu.items.template')" class="text-sm font-bold text-blue-600 hover:underline mt-2 inline-block">
                        {{ $t('menu.download_template') }}
                    </a>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('menu.upload_excel') }}</label>
                <input 
                    type="file" 
                    @change="handleImportFile" 
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                    accept=".xlsx,.xls,.csv"
                    required
                />
                <div v-if="importForm.errors.file" class="text-red-500 text-xs mt-1">{{ importForm.errors.file }}</div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <Button type="button" variant="secondary" @click="showImportModal = false">{{ $t('common.cancel') }}</Button>
                <Button type="submit" :loading="importForm.processing">{{ $t('menu.import_btn') }}</Button>
            </div>
        </form>
    </Modal>

    <!-- Unsplash Picker Modal -->
    <UnsplashPicker 
        :show="showUnsplashPicker"
        @close="showUnsplashPicker = false"
        @select="handleUnsplashSelect"
    />
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Carousel from '@/Components/Carousel.vue';
import UnsplashPicker from '@/Components/UnsplashPicker.vue';
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
const currency = computed(() => (page.props.current_restaurant as any)?.currency || 'AED');

// Category State
const showCategoryModal = ref(false);
const editingCategory = ref<any>(null);
const categoryForm = useForm({
    name: { en: '', ar: '' },
    description: '',
    sort_order: 0,
    is_active: true
});



const fileInputRef = ref<HTMLInputElement | null>(null);

// Item State
const showItemModal = ref(false);
const editingItem = ref<any>(null);
const newFiles = ref<File[]>([]); 
const newIngredientId = ref<number | null>(null);
const newIngredientQty = ref<number | string>('');
const newIngredientUnit = ref<string>('');

// Import State
const showImportModal = ref(false);
const importForm = useForm({
    file: null as File | null
});

const handleImportFile = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        importForm.file = target.files[0];
    }
};

const submitImport = () => {
    if (!importForm.file) return;
    importForm.post(route('menu.items.import'), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        }
    });
};

// Tabs Logic
const activeTab = ref('details');
const tabs = computed(() => {
    const list = [
        { id: 'details', label: t('common.details'), icon: '' },
        { id: 'recipe', label: t('menu.recipe'), icon: '' },
        { id: 'extras', label: t('menu.extras'), icon: '' },
    ];
    if (itemForm.type === 'meal') {
        list.splice(2, 0, { id: 'bundles', label: t('menu.meal_bundle'), icon: '' });
    }
    return list;
});

const itemForm = useForm({
    menu_category_id: null as number | null,
    type: 'item', // item, meal
    name: { en: '', ar: '' },
    description: '',
    sku: '',
    price: '',
    images: [] as File[],
    new_images: [] as File[],
    kept_images: [] as string[],
    sort_order: 0,
    allergens: [],
    
    ingredients: [] as any[], // Recipe
    extras: [] as any[], // Modifiers
    bundles: [] as any[], // Bundled Items (if meal)
    
    is_available: true,
    _method: 'POST'
});

// Computed All Items (Flattened)
const allItems = computed(() => {
    let items = props.categories.flatMap(cat => cat.items || []);
    // Filter out current editing item to avoid self-bundling
    if (editingItem.value) {
        items = items.filter(i => i.id !== editingItem.value.id);
    }
    return items;
});



const getIngredient = (id: number) => props.ingredients.find((i: any) => i.id === id);

// Unit Conversion Factors (to base units: g, ml, pcs)
const unitFactors: Record<string, number> = {
    // Mass (to g)
    'kg': 1000,
    'g': 1,
    'mg': 0.001,
    // Volume (to ml)
    'l': 1000,
    'ml': 1,
    // Count
    'pcs': 1,
    'box': 1, // Assume 1 unless defined
    'pack': 1,
    'can': 1,
    'bottle': 1,
    'dozen': 12
};

const getAvailableUnits = computed(() => {
    if (!newIngredientId.value) return [];
    return getCompatibleUnits(newIngredientId.value);
});

const getCompatibleUnits = (ingredientId: number) => {
    const ingredient = getIngredient(ingredientId);
    if (!ingredient) return [];
    
    const stockUnit = ingredient.unit?.toLowerCase();
    
    // Determine type based on stock unit
    const massUnits = ['kg', 'g', 'mg'];
    const volUnits = ['l', 'ml'];
    
    if (massUnits.includes(stockUnit)) return massUnits;
    if (volUnits.includes(stockUnit)) return volUnits;
    
    // If unknown or specific (pcs, box, etc), just return itself
    return [stockUnit || '']; 
};

const calculateNormalizedQuantity = (qty: number, fromUnit: string, toUnit: string) => {
    const fromFactor = unitFactors[fromUnit.toLowerCase()] || 1;
    const toFactor = unitFactors[toUnit.toLowerCase()] || 1;
    
    // If types match (both mass or both volume), convert. Else return raw (should be validated)
    return qty * (fromFactor / toFactor);
};

const addIngredient = () => {
    if (!newIngredientId.value || !newIngredientQty.value || !newIngredientUnit.value) return;
    
    const ingredient = getIngredient(newIngredientId.value);
    const normalizedQty = calculateNormalizedQuantity(
        Number(newIngredientQty.value), 
        newIngredientUnit.value, 
        ingredient?.unit || newIngredientUnit.value
    );

    itemForm.ingredients.push({
        id: newIngredientId.value,
        quantity: normalizedQty,
        usage_quantity: Number(newIngredientQty.value),
        usage_unit: newIngredientUnit.value
    });
    
    newIngredientQty.value = '';
    // Keep unit or reset?
};

// Set default unit when ingredient changes
import { watch } from 'vue';
watch(newIngredientId, (newId) => {
    if (newId) {
        const ingredient = getIngredient(newId);
        if (ingredient) {
            newIngredientUnit.value = ingredient.unit;
        }
    }
});

const showUnsplashPicker = ref(false);

const handleUnsplashSelect = async (image: any) => {
    try {
        const imageUrl = image.urls.regular;
        const response = await fetch(imageUrl);
        const blob = await response.blob();
        const file = new File([blob], `unsplash-${image.id}.jpg`, { type: 'image/jpeg' });
        
        // Add to newFiles
        newFiles.value = [...newFiles.value, file];
        
        // Add to previews
        previewUrls.value = [...previewUrls.value, URL.createObjectURL(file)];
        
    } catch (e) {
        console.error('Error processing unsplash image:', e);
        alert('Failed to load image from Unsplash.');
    }
};

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        // Convert FileList to Array and Append
        const addedFiles = Array.from(target.files);
        newFiles.value = [...newFiles.value, ...addedFiles];
        
        // Create previews and Append
        const addedPreviews = addedFiles.map(file => URL.createObjectURL(file));
        previewUrls.value = [...previewUrls.value, ...addedPreviews];
        
        // Reset Input
        target.value = '';
    }
};

const removeNewImage = (index: number) => {
    newFiles.value.splice(index, 1);
    previewUrls.value.splice(index, 1);
};

const previewUrls = ref<string[]>([]);

const removeKeptImage = (index: number) => {
    itemForm.kept_images.splice(index, 1);
};

// Cost Calculations
const totalIngredientCost = computed(() => {
    return itemForm.ingredients.reduce((total, ing) => {
        const ingredient = getIngredient(ing.id);
        const cost = ingredient ? Number(ingredient.cost || 0) : 0;
        return total + (cost * Number(ing.quantity));
    }, 0);
});

const estimatedProfit = computed(() => {
    const price = Number(itemForm.price || 0);
    return price - totalIngredientCost.value;
});

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
        categoryForm.is_active = category.is_active !== false; // Default true if undefined
    } else {
        editingCategory.value = null;
        categoryForm.reset();
        categoryForm.is_active = true;
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
    const options = {
        onSuccess: () => closeCategoryModal(),
        onError: (errors: any) => {
            if (errors.name) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: errors.name,
                        type: 'error',
                        title: 'Validation Error'
                    }
                }));
            }
        }
    };

    if (editingCategory.value) {
        categoryForm.put(route('menu.categories.update', editingCategory.value.id), options);
    } else {
        categoryForm.post(route('menu.categories.store'), options);
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



// Extras Logic
const addExtraRow = () => {
    itemForm.extras.push({
        name: { en: '', ar: '' },
        price: '',
        ingredient_id: null,
        quantity: 1,
        unit: ''
    });
};
const removeExtraRow = (index: number) => {
    itemForm.extras.splice(index, 1);
};

// Bundles Logic
const addBundleRow = () => {
    itemForm.bundles.push({
        child_menu_item_id: null,
        quantity: 1
    });
};
const removeBundleRow = (index: number) => {
    itemForm.bundles.splice(index, 1);
};

// Item Actions
const openItemModal = (category: any, item: any = null) => {
    itemForm.menu_category_id = category.id;
    newFiles.value = [];
    previewUrls.value = [];
    itemForm.images = [];
    itemForm.new_images = [];
    itemForm.kept_images = [];
    
    if (item) {
        editingItem.value = item;
        itemForm._method = 'PUT';
        itemForm.type = item.type || 'item';
        itemForm.name.en = item.name.en || item.name;
        itemForm.name.ar = item.name.ar || '';
        itemForm.name.ar = item.name.ar || '';
        itemForm.description = item.description;
        itemForm.sku = item.sku || '';
        itemForm.price = item.price;
        itemForm.sort_order = item.sort_order;
        itemForm.is_available = item.is_available !== false;
        
        // Populate kept_images
        if (item.images && Array.isArray(item.images)) {
             itemForm.kept_images = [...item.images];
        } else if (item.image) {
             itemForm.kept_images = [item.image];
        }
        // Recipe
        itemForm.ingredients = item.recipe ? item.recipe.map((r: any) => ({
             id: r.ingredient_id,
             quantity: r.quantity,
             usage_quantity: r.usage_quantity || r.quantity,
             usage_unit: r.usage_unit
        })) : [];

        // Extras
        itemForm.extras = item.extras ? item.extras.map((e: any) => ({
            name: { en: e.name.en || e.name, ar: e.name.ar || '' },
            price: e.price,
            ingredient_id: e.ingredient_id,
            quantity: e.quantity,
            unit: e.unit || ''
        })) : [];

        // Bundles
        itemForm.bundles = item.bundles ? item.bundles.map((b: any) => ({
            child_menu_item_id: b.child_menu_item_id,
            quantity: b.quantity
        })) : [];
    } else {
        editingItem.value = null;
        itemForm._method = 'POST';
        // Keep category_id
        const catId = itemForm.menu_category_id;
        itemForm.reset();
        itemForm.menu_category_id = catId;
        itemForm.is_available = true; // Default
        itemForm.clearErrors();
    }
    showItemModal.value = true;
};

const closeItemModal = () => {
    showItemModal.value = false;
    itemForm.reset();
    newFiles.value = [];
    previewUrls.value = [];
    itemForm.images = [];
    itemForm.new_images = [];
    itemForm.kept_images = [];
    editingItem.value = null;
};

const submitItem = () => {
    const options = {
        onSuccess: () => closeItemModal(),
        onError: (errors: any) => {
            if (errors.name) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: errors.name,
                        type: 'error',
                        title: 'Validation Error'
                    }
                }));
            }
        },
        forceFormData: true,
    };

    // Populate form files from ref
    if (editingItem.value) {
        itemForm.new_images = newFiles.value;
        if (!editingItem.value.id) {
            console.error("Missing Item ID for update");
            return;
        }
        itemForm._method = 'PUT'; // Set directly on form object
        itemForm.post(route('menu.items.update', editingItem.value.id), options);
    } else {
        itemForm.images = newFiles.value;
        itemForm.post(route('menu.items.store'), options);
    }
};

const addItem = (category: any) => {
    openItemModal(category);
};

const editItem = (category: any, item: any) => {
    openItemModal(category, item);
};

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const deleteItem = (item: any = null) => {
    const target = item || editingItem.value;
    if (!target) return;
    
    if (confirm('Are you sure you want to delete this menu item?')) {
        itemForm.delete(route('menu.items.destroy', target.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (editingItem.value && editingItem.value.id === target.id) {
                    closeItemModal();
                }
            }
        });
    }
};

</script>
