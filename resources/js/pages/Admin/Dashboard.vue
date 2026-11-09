<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Store, Users, Package, ArrowUpRight } from '@lucide/vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

type Props = {
    stats?: {
        shopsCount: number;
        ownersCount: number;
        productsCount: number;
    };
};

defineProps<Props>();
</script>

<template>
    <AdminLayout title="Admin Dashboard">
        <Head title="Admin Dashboard" />

        <div class="space-y-6">
            <!-- Welcome banner -->
            <div class="rounded-2xl bg-gradient-to-r from-indigo-700 to-indigo-900 p-6 sm:p-8 text-white shadow-md">
                <h2 class="text-2xl font-bold tracking-tight">Welcome to ShopDeck Admin</h2>
                <p class="mt-2 text-indigo-100 max-w-2xl text-sm leading-relaxed">
                    Manage multi-shop infrastructure, assign dedicated shop owners, and supervise the centralized global product catalog.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Link
                        href="/admin/shops/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-semibold text-indigo-900 shadow hover:bg-indigo-50 transition"
                    >
                        <span>Create New Shop</span>
                        <ArrowUpRight class="w-3.5 h-3.5" />
                    </Link>
                    <Link
                        href="/admin/products/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-800/80 hover:bg-indigo-800 border border-indigo-600 px-4 py-2 text-xs font-semibold text-white transition"
                    >
                        <span>Add Shared Product</span>
                        <ArrowUpRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Total Shops</span>
                        <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600">
                            <Store class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold">{{ stats?.shopsCount ?? 0 }}</div>
                    <Link href="/admin/shops" class="mt-4 inline-flex items-center text-xs text-indigo-600 dark:text-indigo-400 font-medium hover:underline">
                        Manage Shops &rarr;
                    </Link>
                </div>

                <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Shop Owners</span>
                        <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600">
                            <Users class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold">{{ stats?.ownersCount ?? 0 }}</div>
                    <Link href="/admin/shop-owners" class="mt-4 inline-flex items-center text-xs text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                        Manage Owners &rarr;
                    </Link>
                </div>

                <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Global Products</span>
                        <div class="p-2 rounded-lg bg-violet-50 dark:bg-violet-950/60 text-violet-600">
                            <Package class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 text-3xl font-extrabold">{{ stats?.productsCount ?? 0 }}</div>
                    <Link href="/admin/products" class="mt-4 inline-flex items-center text-xs text-violet-600 dark:text-violet-400 font-medium hover:underline">
                        Browse Catalog &rarr;
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
