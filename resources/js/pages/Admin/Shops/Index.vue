<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    Store,
    ExternalLink,
    Edit3,
    Trash2,
    CheckCircle,
    XCircle,
    ShoppingBag,
} from '@lucide/vue';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

type Owner = {
    id: number;
    name: string;
    email: string;
};

type Shop = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
    is_active: boolean;
    owner: Owner | null;
    shop_products_count: number;
    created_at: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    shops: {
        data: Shop[];
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
        '/admin/shops',
        { search: search.value || undefined },
        { preserveState: true, replace: true }
    );
};

const toggleStatus = (shop: Shop) => {
    router.post(`/admin/shops/${shop.id}/toggle-status`, {}, { preserveScroll: true });
};

const deleteShop = (shop: Shop) => {
    if (confirm(`Are you sure you want to delete "${shop.name}"? All assigned products in this shop will be unassigned.`)) {
        router.delete(`/admin/shops/${shop.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout title="Shops Management">
        <Head title="Shops Management" />

        <div class="space-y-6">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Shops Directory</h2>
                    <p class="text-xs text-neutral-500 mt-1">
                        Total {{ shops.total }} shops registered across the platform.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/shops/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 px-4 py-2 text-xs font-semibold text-white shadow transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Create Shop</span>
                    </Link>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 p-4 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-xs">
                <div class="relative flex-1">
                    <Search class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Search shops by name or slug..."
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
                                <th class="px-6 py-3.5">Shop</th>
                                <th class="px-6 py-3.5">Storefront Slug</th>
                                <th class="px-6 py-3.5">Owner</th>
                                <th class="px-6 py-3.5 text-center">Assigned Products</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-if="shops.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-neutral-500 text-sm">
                                    No shops found matching your criteria.
                                </td>
                            </tr>
                            <tr
                                v-for="shop in shops.data"
                                :key="shop.id"
                                class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden border border-neutral-200 dark:border-neutral-700 shrink-0">
                                            <img
                                                v-if="shop.logo"
                                                :src="`/storage/${shop.logo}`"
                                                :alt="shop.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <Store v-else class="w-5 h-5 text-neutral-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                                {{ shop.name }}
                                            </div>
                                            <div class="text-xs text-neutral-500 truncate max-w-xs">
                                                {{ shop.description || 'No description' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-mono text-xs">
                                    <a
                                        :href="`/shops/${shop.slug}`"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 hover:underline"
                                    >
                                        <span>/shops/{{ shop.slug }}</span>
                                        <ExternalLink class="w-3.5 h-3.5" />
                                    </a>
                                </td>

                                <td class="px-6 py-4">
                                    <div v-if="shop.owner">
                                        <div class="font-medium text-xs">{{ shop.owner.name }}</div>
                                        <div class="text-[11px] text-neutral-500">{{ shop.owner.email }}</div>
                                    </div>
                                    <span v-else class="text-xs text-neutral-400 italic">Unassigned</span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                                        <ShoppingBag class="w-3 h-3 text-neutral-400" />
                                        {{ shop.shop_products_count }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="toggleStatus(shop)"
                                        :title="shop.is_active ? 'Click to deactivate' : 'Click to activate'"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition"
                                        :class="shop.is_active
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-100'
                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 hover:bg-rose-100'"
                                    >
                                        <CheckCircle v-if="shop.is_active" class="w-3 h-3" />
                                        <XCircle v-else class="w-3 h-3" />
                                        <span>{{ shop.is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/shops/${shop.id}/edit`"
                                            class="p-1.5 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 hover:text-neutral-900 transition"
                                            title="Edit Shop"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </Link>
                                        <button
                                            @click="deleteShop(shop)"
                                            class="p-1.5 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/40 text-neutral-400 hover:text-rose-600 transition"
                                            title="Delete Shop"
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
                <div v-if="shops.links.length > 3" class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <div class="text-neutral-500">
                        Page {{ shops.current_page }} of {{ shops.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, idx) in shops.links"
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
