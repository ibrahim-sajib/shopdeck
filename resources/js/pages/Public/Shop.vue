<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Store,
    Search,
    Package,
    ShoppingBag,
    Tag,
    X,
    Sparkles,
    ChevronRight,
} from '@lucide/vue';
import { ref } from 'vue';

type Shop = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
};

type GlobalProduct = {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    thumbnail: string | null;
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
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    shop: Shop;
    listings: {
        data: Listing[];
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
        `/shops/${props.shop.slug}`,
        { search: search.value || undefined },
        { preserveState: true, replace: true }
    );
};

// Modal for viewing product details
const selectedListing = ref<Listing | null>(null);

const openDetail = (listing: Listing) => {
    selectedListing.value = listing;
};

const closeDetail = () => {
    selectedListing.value = null;
};
</script>

<template>
    <div class="min-h-screen bg-neutral-50 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 flex flex-col">
        <Head :title="`${shop.name} — Storefront`" />

        <!-- Storefront Header -->
        <header class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                        <img
                            v-if="shop.logo"
                            :src="`/storage/${shop.logo}`"
                            :alt="shop.name"
                            class="w-full h-full object-cover"
                        />
                        <Store v-else class="w-6 h-6 text-emerald-600" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="font-extrabold text-lg sm:text-xl tracking-tight text-neutral-900 dark:text-neutral-100">
                                {{ shop.name }}
                            </h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Verified Shop
                            </span>
                        </div>
                        <p class="text-xs text-neutral-500 truncate max-w-sm sm:max-w-md">
                            {{ shop.description || 'Welcome to our online store! Browse our featured products below.' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/login"
                        class="text-xs font-semibold px-3.5 py-2 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 transition"
                    >
                        Sign In
                    </Link>
                </div>
            </div>
        </header>

        <!-- Store Banner / Search -->
        <div class="bg-gradient-to-b from-white to-neutral-50 dark:from-neutral-900 dark:to-neutral-950 border-b border-neutral-200 dark:border-neutral-800 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        Official Store Catalog
                    </div>
                    <div class="text-2xl font-black tracking-tight mt-1">
                        Explore {{ shop.name }}
                    </div>
                    <p class="text-xs text-neutral-500 mt-1">
                        Showing {{ listings.total }} products curated specifically by this shop.
                    </p>
                </div>

                <!-- Search Input -->
                <div class="w-full md:w-80">
                    <div class="relative">
                        <Search class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                            v-model="search"
                            @keyup.enter="handleSearch"
                            type="text"
                            placeholder="Search in this shop..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-emerald-500 shadow-xs"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
            <!-- Empty State -->
            <div v-if="listings.data.length === 0" class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 p-12 text-center max-w-md mx-auto my-12 space-y-3">
                <div class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 mx-auto flex items-center justify-center text-neutral-400">
                    <ShoppingBag class="w-6 h-6" />
                </div>
                <h3 class="font-bold text-base">No Products Found</h3>
                <p class="text-xs text-neutral-500">
                    {{ search ? `No items match "${search}". Try searching with a different term.` : 'This shop has not listed any active products yet. Please check back later!' }}
                </p>
                <button
                    v-if="search"
                    @click="search = ''; handleSearch();"
                    class="mt-2 inline-flex text-xs font-semibold text-emerald-600 hover:underline"
                >
                    Clear Search
                </button>
            </div>

            <!-- Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div
                    v-for="listing in listings.data"
                    :key="listing.id"
                    @click="openDetail(listing)"
                    class="group bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-xs hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-800 transition cursor-pointer flex flex-col"
                >
                    <!-- Product Image -->
                    <div class="h-52 bg-neutral-100 dark:bg-neutral-800/80 flex items-center justify-center overflow-hidden relative">
                        <img
                            v-if="listing.resolved_thumbnail"
                            :src="`/storage/${listing.resolved_thumbnail}`"
                            :alt="listing.resolved_name"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                        />
                        <div v-else class="flex flex-col items-center text-neutral-400 gap-1">
                            <Package class="w-10 h-10" />
                            <span class="text-[10px]">No image</span>
                        </div>

                        <!-- Price Tag Badge -->
                        <div class="absolute bottom-3 right-3 bg-neutral-900/85 backdrop-blur-md text-white font-extrabold text-sm px-3 py-1 rounded-full shadow-md">
                            ৳{{ Number(listing.price).toFixed(2) }}
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="text-[11px] font-mono text-neutral-400 mb-1">
                                {{ listing.product?.sku || 'Item' }}
                            </div>
                            <h3 class="font-bold text-sm text-neutral-900 dark:text-neutral-100 line-clamp-1 group-hover:text-emerald-600 transition">
                                {{ listing.resolved_name }}
                            </h3>
                            <p class="mt-1.5 text-xs text-neutral-500 line-clamp-2 leading-relaxed">
                                {{ listing.resolved_description || 'Quality product available exclusively at this shop.' }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                <span>View Details</span>
                                <ChevronRight class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition" />
                            </span>
                            <span class="text-[11px] text-neutral-400">In Stock</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="listings.links.length > 3" class="mt-10 bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 px-6 py-4 flex items-center justify-between text-xs">
                <div class="text-neutral-500">
                    Showing {{ listings.data.length }} of {{ listings.total }} products
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
        </main>

        <!-- Product Quick-view Modal -->
        <div v-if="selectedListing" class="fixed inset-0 z-50 bg-neutral-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-5 animate-in fade-in-50 zoom-in-95">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Offered by {{ shop.name }}</span>
                        <h2 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100 mt-0.5">
                            {{ selectedListing.resolved_name }}
                        </h2>
                    </div>
                    <button @click="closeDetail" class="p-1.5 rounded-md text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row gap-5">
                    <div class="w-full sm:w-48 h-48 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden shrink-0">
                        <img
                            v-if="selectedListing.resolved_thumbnail"
                            :src="`/storage/${selectedListing.resolved_thumbnail}`"
                            :alt="selectedListing.resolved_name"
                            class="w-full h-full object-cover"
                        />
                        <Package v-else class="w-10 h-10 text-neutral-400" />
                    </div>
                    <div class="flex-1 space-y-3">
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                            ৳{{ Number(selectedListing.price).toFixed(2) }}
                        </div>
                        <div class="text-xs text-neutral-500 leading-relaxed max-h-36 overflow-y-auto">
                            {{ selectedListing.resolved_description || 'No detailed description provided by this shop.' }}
                        </div>
                        <div class="text-[11px] font-mono text-neutral-400 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                            Catalog SKU: {{ selectedListing.product?.sku || 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end">
                    <button
                        @click="closeDetail"
                        class="px-5 py-2 text-xs font-semibold rounded-lg bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 hover:opacity-90 transition"
                    >
                        Close Preview
                    </button>
                </div>
            </div>
        </div>

        <!-- Storefront Footer -->
        <footer class="bg-white dark:bg-neutral-900 border-t border-neutral-200 dark:border-neutral-800 py-6 text-center text-xs text-neutral-500">
            <div class="max-w-7xl mx-auto px-4">
                &copy; {{ new Date().getFullYear() }} {{ shop.name }} &bull; Powered by <span class="font-bold text-neutral-800 dark:text-neutral-200">ShopDeck</span> Multi-Shop Platform.
            </div>
        </footer>
    </div>
</template>
