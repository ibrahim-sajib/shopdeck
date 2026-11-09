<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    Package,
    Edit3,
    Trash2,
    CheckCircle,
    XCircle,
    User as UserIcon,
} from '@lucide/vue';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

type Creator = {
    id: number;
    name: string;
    email: string;
    role: string;
};

type Product = {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    thumbnail: string | null;
    created_by: number;
    is_active: boolean;
    creator: Creator | null;
    created_at: string;
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
    filters: {
        search?: string;
    };
};

const props = defineProps<Props>();
const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get(
        '/admin/products',
        { search: search.value || undefined },
        { preserveState: true, replace: true }
    );
};

const toggleStatus = (product: Product) => {
    router.post(`/admin/products/${product.id}/toggle-status`, {}, { preserveScroll: true });
};

const deleteProduct = (product: Product) => {
    if (confirm(`Are you sure you want to delete "${product.name}"? This will also remove all associated listings across shops.`)) {
        router.delete(`/admin/products/${product.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout title="Shared Product Catalog">
        <Head title="Shared Product Catalog" />

        <div class="space-y-6">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Global Product Catalog</h2>
                    <p class="text-xs text-neutral-500 mt-1">
                        Total {{ products.total }} products available across all multi-shop storefronts.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/products/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 px-4 py-2 text-xs font-semibold text-white shadow transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Add Shared Product</span>
                    </Link>
                </div>
            </div>

            <!-- Search -->
            <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 p-4 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-xs">
                <div class="relative flex-1">
                    <Search class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Search catalog by product name or SKU..."
                        class="w-full pl-10 pr-4 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
                <button
                    @click="handleSearch"
                    class="px-4 py-2 text-xs font-medium rounded-lg bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 transition"
                >
                    Filter
                </button>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/60 text-xs font-semibold uppercase tracking-wider text-neutral-500 border-b border-neutral-200 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-3.5">Product</th>
                                <th class="px-6 py-3.5">SKU</th>
                                <th class="px-6 py-3.5">Created By</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-if="products.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-neutral-500 text-sm">
                                    No products found in the catalog.
                                </td>
                            </tr>
                            <tr
                                v-for="product in products.data"
                                :key="product.id"
                                class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden border border-neutral-200 dark:border-neutral-700 shrink-0">
                                            <img
                                                v-if="product.thumbnail"
                                                :src="`/storage/${product.thumbnail}`"
                                                :alt="product.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <Package v-else class="w-6 h-6 text-neutral-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                                {{ product.name }}
                                            </div>
                                            <div class="text-xs text-neutral-500 truncate max-w-sm">
                                                {{ product.description || 'No description provided' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-mono text-xs">
                                    <span v-if="product.sku" class="px-2 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                                        {{ product.sku }}
                                    </span>
                                    <span v-else class="text-neutral-400 italic">None</span>
                                </td>

                                <td class="px-6 py-4">
                                    <div v-if="product.creator" class="flex items-center gap-2">
                                        <div class="text-xs font-medium">{{ product.creator.name }}</div>
                                        <span
                                            class="text-[10px] px-1.5 py-0.2 rounded font-semibold uppercase tracking-wider"
                                            :class="product.creator.role === 'admin'
                                                ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                                                : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'"
                                        >
                                            {{ product.creator.role }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-neutral-400">System</span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="toggleStatus(product)"
                                        :title="product.is_active ? 'Click to make inactive' : 'Click to make active'"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition"
                                        :class="product.is_active
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-100'
                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 hover:bg-rose-100'"
                                    >
                                        <CheckCircle v-if="product.is_active" class="w-3 h-3" />
                                        <XCircle v-else class="w-3 h-3" />
                                        <span>{{ product.is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/products/${product.id}/edit`"
                                            class="p-1.5 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 hover:text-neutral-900 transition"
                                            title="Edit Product"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </Link>
                                        <button
                                            @click="deleteProduct(product)"
                                            class="p-1.5 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/40 text-neutral-400 hover:text-rose-600 transition"
                                            title="Delete Product"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="products.links.length > 3" class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <div class="text-neutral-500">
                        Page {{ products.current_page }} of {{ products.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, idx) in products.links"
                            :key="idx"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-md font-medium transition',
                                link.active
                                    ? 'bg-indigo-600 text-white font-semibold'
                                    : 'text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800',
                                !link.url ? 'opacity-40 pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
