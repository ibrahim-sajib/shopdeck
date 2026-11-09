<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Search,
    Package,
    Plus,
    Check,
    Store,
    ExternalLink,
    AlertCircle,
    X,
} from '@lucide/vue';
import { ref } from 'vue';
import OwnerLayout from '@/layouts/OwnerLayout.vue';

type Creator = {
    id: number;
    name: string;
    role: string;
};

type Product = {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    thumbnail: string | null;
    creator: Creator | null;
};

type Shop = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    products: {
        data: Product[];
        links: PaginationLink[];
        total: number;
        current_page: number;
        last_page: number;
    };
    addedProductIds: number[];
    existingListings: Record<number, number>;
    shop: Shop | null;
    filters: {
        search?: string;
    };
};

const props = defineProps<Props>();
const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get(
        '/owner/catalog',
        { search: search.value || undefined },
        { preserveState: true, replace: true }
    );
};

// Modal for adding product to shop
const selectedProduct = ref<Product | null>(null);
const showAddModal = ref(false);

const form = useForm({
    price: '',
    name_override: '',
    description_override: '',
});

const openAddModal = (product: Product) => {
    selectedProduct.value = product;
    form.reset();
    form.clearErrors();
    form.price = '';
    form.name_override = '';
    form.description_override = '';
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    selectedProduct.value = null;
};

const submitAdd = () => {
    if (!selectedProduct.value) return;

    form.post(`/owner/catalog/${selectedProduct.value.id}/add`, {
        preserveScroll: true,
        onSuccess: () => {
            closeAddModal();
        },
    });
};
</script>

<template>
    <OwnerLayout title="Browse Shared Catalog">
        <Head title="Shared Product Catalog" />

        <div class="space-y-6">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Centralized Product Catalog</h2>
                    <p class="text-xs text-neutral-500 mt-1">
                        Discover shared items across the system and list them directly on <span class="font-semibold text-emerald-600">{{ shop?.name || 'your shop' }}</span>.
                    </p>
                </div>
            </div>

            <!-- No shop warning -->
            <div v-if="!shop" class="p-4 rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 flex items-center gap-3">
                <AlertCircle class="w-5 h-5 text-amber-600 shrink-0" />
                <span class="text-xs font-medium">You need an active assigned shop to add products to your store.</span>
            </div>

            <!-- Search -->
            <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 p-4 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-xs">
                <div class="relative flex-1">
                    <Search class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Search catalog by title, SKU, or keywords..."
                        class="w-full pl-10 pr-4 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
                <button
                    @click="handleSearch"
                    class="px-4 py-2 text-xs font-medium rounded-lg bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 transition"
                >
                    Search
                </button>
            </div>

            <!-- Products Grid -->
            <div v-if="products.data.length === 0" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-12 text-center text-sm text-neutral-500">
                No products found in the catalog matching your search.
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-xs flex flex-col overflow-hidden hover:border-neutral-300 dark:hover:border-neutral-700 transition"
                >
                    <!-- Thumbnail preview -->
                    <div class="h-44 bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden relative">
                        <img
                            v-if="product.thumbnail"
                            :src="`/storage/${product.thumbnail}`"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="flex flex-col items-center text-neutral-400 gap-1">
                            <Package class="w-10 h-10" />
                            <span class="text-[10px]">No image</span>
                        </div>

                        <!-- Creator badge -->
                        <div class="absolute top-2.5 right-2.5">
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider shadow-xs backdrop-blur-md"
                                :class="product.creator?.role === 'admin'
                                    ? 'bg-indigo-600/90 text-white'
                                    : 'bg-emerald-600/90 text-white'"
                            >
                                {{ product.creator?.name }} ({{ product.creator?.role }})
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between text-[11px] font-mono text-neutral-400 mb-1">
                                <span>{{ product.sku || 'SKU: None' }}</span>
                            </div>
                            <h3 class="font-bold text-sm text-neutral-900 dark:text-neutral-100 line-clamp-1">
                                {{ product.name }}
                            </h3>
                            <p class="mt-1.5 text-xs text-neutral-500 line-clamp-2 leading-relaxed">
                                {{ product.description || 'No description provided.' }}
                            </p>
                        </div>

                        <!-- Action Button / Added Status -->
                        <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-neutral-800">
                            <!-- Already Added -->
                            <div v-if="addedProductIds.includes(product.id)" class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-full">
                                    <Check class="w-3.5 h-3.5" />
                                    <span>Listed in Shop</span>
                                </span>
                                <Link
                                    v-if="existingListings[product.id]"
                                    :href="`/owner/products/${existingListings[product.id]}/edit`"
                                    class="text-xs text-neutral-600 dark:text-neutral-300 hover:text-emerald-600 font-medium hover:underline"
                                >
                                    Edit Listing &rarr;
                                </Link>
                            </div>

                            <!-- Not Added Yet -->
                            <div v-else>
                                <button
                                    @click="openAddModal(product)"
                                    :disabled="!shop"
                                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-xs font-semibold shadow-xs transition disabled:opacity-40"
                                >
                                    <Plus class="w-4 h-4" />
                                    <span>Add to My Shop</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="products.links.length > 3" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 px-6 py-4 flex items-center justify-between text-xs">
                <div class="text-neutral-500">
                    Page {{ products.current_page }} of {{ products.last_page }} (Total {{ products.total }} items)
                </div>
                <div class="flex items-center gap-1">
                    <Link
                        v-for="(link, idx) in products.links"
                        :key="idx"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-md font-medium transition',
                            link.active
                                ? 'bg-emerald-600 text-white font-semibold'
                                : 'text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800',
                            !link.url ? 'opacity-40 pointer-events-none' : ''
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <!-- Add To Shop Modal -->
        <div v-if="showAddModal && selectedProduct" class="fixed inset-0 z-50 bg-neutral-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-5 animate-in fade-in-50 zoom-in-95">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">List on {{ shop?.name }}</span>
                        <h3 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                            {{ selectedProduct.name }}
                        </h3>
                    </div>
                    <button @click="closeAddModal" class="p-1 rounded-md text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitAdd" class="space-y-4">
                    <!-- Shop-specific price -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                            Shop Listing Price (BDT) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative rounded-lg shadow-xs">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400 font-bold text-sm">
                                ৳
                            </span>
                            <input
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full pl-9 pr-3.5 py-2.5 text-sm font-semibold bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                                required
                                autofocus
                            />
                        </div>
                        <p class="mt-1 text-[11px] text-neutral-500">Each shop sets its own selling price for this catalog item.</p>
                        <div v-if="form.errors.price" class="mt-1 text-xs text-rose-600 font-medium">
                            {{ form.errors.price }}
                        </div>
                    </div>

                    <!-- Custom Name Override (Optional) -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                            Custom Title Override (Optional)
                        </label>
                        <input
                            v-model="form.name_override"
                            type="text"
                            :placeholder="`Default: ${selectedProduct.name}`"
                            class="w-full px-3.5 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                        />
                        <p class="mt-1 text-[11px] text-neutral-500">Leave blank to use the standard catalog title.</p>
                    </div>

                    <!-- Custom Description Override (Optional) -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                            Custom Description Override (Optional)
                        </label>
                        <textarea
                            v-model="form.description_override"
                            rows="2"
                            placeholder="Leave blank to use the standard catalog description..."
                            class="w-full px-3.5 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="pt-3 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="closeAddModal"
                            class="px-4 py-2 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Adding...' : 'Confirm & Add to Shop' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </OwnerLayout>
</template>
