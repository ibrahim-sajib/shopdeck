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
    Globe,
    AlertCircle,
    ExternalLink,
} from '@lucide/vue';
import { ref } from 'vue';
import OwnerLayout from '@/layouts/OwnerLayout.vue';

type Creator = {
    id: number;
    name: string;
    role: string;
};

type GlobalProduct = {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    thumbnail: string | null;
    creator: Creator | null;
};

type Listing = {
    id: number;
    shop_id: number;
    product_id: number;
    price: string | number;
    name_override: string | null;
    description_override: string | null;
    thumbnail_override: string | null;
    resolved_name: string;
    resolved_description: string | null;
    resolved_thumbnail: string | null;
    is_active: boolean;
    product: GlobalProduct;
    created_at: string;
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
    listings: {
        data: Listing[];
        links: PaginationLink[];
        total: number;
        current_page: number;
        last_page: number;
    };
    shop: Shop | null;
    filters: {
        search?: string;
    };
};

const props = defineProps<Props>();
const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get(
        '/owner/products',
        { search: search.value || undefined },
        { preserveState: true, replace: true }
    );
};

const toggleStatus = (listing: Listing) => {
    router.post(`/owner/products/${listing.id}/toggle-status`, {}, { preserveScroll: true });
};

const removeListing = (listing: Listing) => {
    if (confirm(`Are you sure you want to remove "${listing.resolved_name}" from your shop? (It will remain in the shared global catalog).`)) {
        router.delete(`/owner/products/${listing.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <OwnerLayout title="My Shop Listings">
        <Head title="My Shop Listings" />

        <div class="space-y-6">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Shop Listings</h2>
                    <p class="text-xs text-neutral-500 mt-1">
                        Products actively curated for <span class="font-semibold text-emerald-600">{{ shop?.name || 'your store' }}</span>.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/owner/catalog"
                        class="inline-flex items-center gap-2 rounded-lg bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50 px-4 py-2 text-xs font-semibold shadow-xs transition"
                    >
                        <Globe class="w-4 h-4 text-emerald-600" />
                        <span>Browse Catalog</span>
                    </Link>
                    <Link
                        href="/owner/products/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Create Own Product</span>
                    </Link>
                </div>
            </div>

            <!-- No shop assigned state -->
            <div v-if="!shop" class="p-4 rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 flex items-center gap-3">
                <AlertCircle class="w-5 h-5 text-amber-600 shrink-0" />
                <span class="text-xs font-medium">Your account is not assigned to a shop yet. Please contact an administrator.</span>
            </div>

            <!-- Search -->
            <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 p-4 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-xs">
                <div class="relative flex-1">
                    <Search class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="search"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Search your listings by title or SKU..."
                        class="w-full pl-10 pr-4 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-emerald-500"
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
                                <th class="px-6 py-3.5">Listing Title & Image</th>
                                <th class="px-6 py-3.5">Catalog Source</th>
                                <th class="px-6 py-3.5 text-right">Selling Price</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-if="listings.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-neutral-500 text-sm">
                                    No products listed in your shop yet. Browse the shared catalog to add items or create a new product.
                                </td>
                            </tr>
                            <tr
                                v-for="listing in listings.data"
                                :key="listing.id"
                                class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden border border-neutral-200 dark:border-neutral-700 shrink-0">
                                            <img
                                                v-if="listing.resolved_thumbnail"
                                                :src="`/storage/${listing.resolved_thumbnail}`"
                                                :alt="listing.resolved_name"
                                                class="w-full h-full object-cover"
                                            />
                                            <Package v-else class="w-6 h-6 text-neutral-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
                                                <span>{{ listing.resolved_name }}</span>
                                                <span v-if="listing.name_override" class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                                                    Custom Title
                                                </span>
                                            </div>
                                            <div class="text-xs text-neutral-500 truncate max-w-sm">
                                                {{ listing.resolved_description || 'No description' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-xs font-medium text-neutral-800 dark:text-neutral-200">
                                        {{ listing.product?.name }}
                                    </div>
                                    <div class="text-[11px] text-neutral-400 flex items-center gap-1.5 mt-0.5">
                                        <span>SKU: {{ listing.product?.sku || 'None' }}</span>
                                        <span>&bull;</span>
                                        <span>By {{ listing.product?.creator?.name }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-sm text-emerald-600 dark:text-emerald-400">
                                        ৳{{ Number(listing.price).toFixed(2) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="toggleStatus(listing)"
                                        :title="listing.is_active ? 'Click to make inactive in shop' : 'Click to make active in shop'"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition"
                                        :class="listing.is_active
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-100'
                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 hover:bg-rose-100'"
                                    >
                                        <CheckCircle v-if="listing.is_active" class="w-3 h-3" />
                                        <XCircle v-else class="w-3 h-3" />
                                        <span>{{ listing.is_active ? 'Active' : 'Hidden' }}</span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/owner/products/${listing.id}/edit`"
                                            class="p-1.5 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 hover:text-neutral-900 transition"
                                            title="Edit Shop Overrides"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </Link>
                                        <button
                                            @click="removeListing(listing)"
                                            class="p-1.5 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/40 text-neutral-400 hover:text-rose-600 transition"
                                            title="Remove from Shop"
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
                <div v-if="listings.links.length > 3" class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <div class="text-neutral-500">
                        Page {{ listings.current_page }} of {{ listings.last_page }} (Total {{ listings.total }} listings)
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, idx) in listings.links"
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
        </div>
    </OwnerLayout>
</template>
