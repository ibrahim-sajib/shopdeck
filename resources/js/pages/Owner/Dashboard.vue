<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingBag, Globe, PlusCircle, ExternalLink, AlertCircle } from '@lucide/vue';
import OwnerLayout from '@/layouts/OwnerLayout.vue';

type Props = {
    shop?: {
        id: number;
        name: string;
        slug: string;
        is_active: boolean;
        description?: string;
    } | null;
    stats?: {
        activeListingsCount: number;
        catalogCount: number;
    };
};

defineProps<Props>();
</script>

<template>
    <OwnerLayout title="Owner Dashboard">
        <Head title="Owner Dashboard" />

        <div class="space-y-6">
            <!-- No shop warning if unassigned -->
            <div v-if="!shop" class="rounded-2xl border border-amber-300 bg-amber-50 dark:bg-amber-950/40 dark:border-amber-900 p-6 text-amber-900 dark:text-amber-200">
                <div class="flex items-start gap-3">
                    <AlertCircle class="w-6 h-6 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-bold text-base">No Shop Assigned</h3>
                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-300">
                            Your account is not currently assigned to any shop. Please contact a ShopDeck administrator to assign your shop before listing products.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Welcome banner -->
            <div v-else class="rounded-2xl bg-gradient-to-r from-emerald-700 to-teal-900 p-6 sm:p-8 text-white shadow-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" :class="shop.is_active ? 'bg-emerald-500/30 text-emerald-100 border border-emerald-400/40' : 'bg-amber-500/30 text-amber-100 border border-amber-400/40'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="shop.is_active ? 'bg-emerald-300' : 'bg-amber-300'"></span>
                            {{ shop.is_active ? 'Shop Active' : 'Shop Inactive' }}
                        </span>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight">{{ shop.name }}</h2>
                        <p class="mt-1 text-emerald-100 text-sm max-w-xl">
                            {{ shop.description || 'Welcome to your shop management dashboard. Browse products or create your own custom items.' }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <a
                            v-if="shop.is_active"
                            :href="`/shops/${shop.slug}`"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-semibold text-emerald-950 shadow hover:bg-emerald-50 transition"
                        >
                            <span>View Storefront</span>
                            <ExternalLink class="w-3.5 h-3.5" />
                        </a>
                        <Link
                            href="/owner/catalog"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-800/80 hover:bg-emerald-800 border border-emerald-600 px-4 py-2 text-xs font-semibold text-white transition"
                        >
                            <span>Browse Catalog</span>
                            <Globe class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Quick actions & Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">My Shop Listings</span>
                        <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600">
                            <ShoppingBag class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold">{{ stats?.activeListingsCount ?? 0 }}</div>
                    <div class="mt-4 flex items-center justify-between">
                        <Link href="/owner/products" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                            Manage My Listings &rarr;
                        </Link>
                        <Link href="/owner/products/create" class="inline-flex items-center gap-1 text-xs text-neutral-600 dark:text-neutral-300 font-medium hover:underline">
                            <PlusCircle class="w-3.5 h-3.5" />
                            <span>Create Product</span>
                        </Link>
                    </div>
                </div>

                <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Available Catalog Products</span>
                        <div class="p-2 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-600">
                            <Globe class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold">{{ stats?.catalogCount ?? 0 }}</div>
                    <div class="mt-4">
                        <Link href="/owner/catalog" class="text-xs text-teal-600 dark:text-teal-400 font-medium hover:underline">
                            Browse & Add Products &rarr;
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </OwnerLayout>
</template>
