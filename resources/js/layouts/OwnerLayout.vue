<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Globe,
    ShoppingBag,
    PlusCircle,
    Store,
    LogOut,
    ExternalLink,
} from '@lucide/vue';
import { computed } from 'vue';

type Props = {
    title?: string;
};

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const shop = computed(() => page.props.shop as any);
const currentUrl = computed(() => page.url);

const navItems = [
    { name: 'Dashboard', href: '/owner/dashboard', icon: LayoutDashboard },
    { name: 'Shared Catalog', href: '/owner/catalog', icon: Globe },
    { name: 'My Shop Products', href: '/owner/products', icon: ShoppingBag },
    { name: 'Add New Product', href: '/owner/products/create', icon: PlusCircle },
];

const isActive = (href: string) => {
    return currentUrl.value === href || currentUrl.value.startsWith(`${href}/`);
};
</script>

<template>
    <div class="min-h-screen bg-neutral-50 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100 flex flex-col md:flex-row">
        <!-- Sidebar -->
        <aside class="w-full md:w-64 bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 flex flex-col shrink-0">
            <div class="h-16 flex items-center px-6 border-b border-neutral-200 dark:border-neutral-800 justify-between">
                <Link href="/owner/dashboard" class="flex items-center gap-2.5 font-bold text-lg tracking-tight">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-600 text-white font-black text-sm shadow">
                        SD
                    </span>
                    <span>ShopDeck <span class="text-xs font-semibold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 ml-1">Owner</span></span>
                </Link>
            </div>

            <!-- Shop Info Card in Sidebar -->
            <div v-if="shop" class="p-4 mx-3 my-2 rounded-xl bg-neutral-100 dark:bg-neutral-800/60 border border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center gap-2 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                    <Store class="w-3.5 h-3.5 text-emerald-600" />
                    <span>My Shop</span>
                </div>
                <div class="mt-1 font-bold text-sm truncate">{{ shop.name }}</div>
                <a
                    v-if="shop.slug && shop.is_active"
                    :href="`/shops/${shop.slug}`"
                    target="_blank"
                    class="mt-2 inline-flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-medium"
                >
                    <span>View Public Store</span>
                    <ExternalLink class="w-3 h-3" />
                </a>
                <span v-else-if="!shop.is_active" class="mt-1 inline-block text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                    (Shop Inactive)
                </span>
            </div>

            <nav class="p-4 space-y-1 flex-1">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors',
                        isActive(item.href)
                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-semibold shadow-xs'
                            : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-100'
                    ]"
                >
                    <component :is="item.icon" class="w-4 h-4 shrink-0" />
                    <span>{{ item.name }}</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-neutral-200 dark:border-neutral-800 space-y-3">
                <div class="flex items-center justify-between text-xs px-2 text-neutral-500">
                    <span class="truncate font-medium">{{ user?.name }}</span>
                    <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-neutral-200 dark:bg-neutral-800">
                        Owner
                    </span>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-md border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                >
                    <LogOut class="w-3.5 h-3.5" />
                    <span>Log Out</span>
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top navbar -->
            <header class="h-16 bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 px-6 flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold">{{ title || 'Shop Owner Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-xs text-neutral-500">{{ user?.email }}</span>
                </div>
            </header>

            <!-- Page Flash / Messages -->
            <div v-if="$page.props.flash?.success" class="m-6 mb-0 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                <span>{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash?.error" class="m-6 mb-0 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
                <span>{{ $page.props.flash.error }}</span>
            </div>

            <main class="flex-1 p-6 md:p-8 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
