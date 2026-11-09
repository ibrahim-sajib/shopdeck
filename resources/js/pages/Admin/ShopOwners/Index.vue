<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    UserCheck,
    Store,
    Edit3,
    Trash2,
    CheckCircle,
    XCircle,
    Mail,
} from '@lucide/vue';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

type Shop = {
    id: number;
    name: string;
    slug: string;
};

type Owner = {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    shop: Shop | null;
    created_at: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    owners: {
        data: Owner[];
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
        '/admin/shop-owners',
        { search: search.value || undefined },
        { preserveState: true, replace: true }
    );
};

const toggleStatus = (owner: Owner) => {
    router.post(`/admin/shop-owners/${owner.id}/toggle-status`, {}, { preserveScroll: true });
};

const deleteOwner = (owner: Owner) => {
    if (confirm(`Are you sure you want to delete owner "${owner.name}"? If this owner is assigned to a shop, the shop will be unassigned.`)) {
        router.delete(`/admin/shop-owners/${owner.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout title="Shop Owners">
        <Head title="Shop Owners" />

        <div class="space-y-6">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Shop Owners Directory</h2>
                    <p class="text-xs text-neutral-500 mt-1">
                        Total {{ owners.total }} shop owners with role access.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/shop-owners/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 px-4 py-2 text-xs font-semibold text-white shadow transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Create Shop Owner</span>
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
                        placeholder="Search owners by name or email..."
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
                                <th class="px-6 py-3.5">Owner Profile</th>
                                <th class="px-6 py-3.5">Assigned Shop</th>
                                <th class="px-6 py-3.5 text-center">Account Status</th>
                                <th class="px-6 py-3.5 text-center">Registered</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-if="owners.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-neutral-500 text-sm">
                                    No shop owners found matching your search.
                                </td>
                            </tr>
                            <tr
                                v-for="owner in owners.data"
                                :key="owner.id"
                                class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center text-xs shrink-0">
                                            {{ owner.name.substring(0, 2).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                                {{ owner.name }}
                                            </div>
                                            <div class="text-xs text-neutral-500 flex items-center gap-1">
                                                <Mail class="w-3 h-3 text-neutral-400" />
                                                <span>{{ owner.email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div v-if="owner.shop" class="flex items-center gap-1.5">
                                        <Store class="w-3.5 h-3.5 text-emerald-600" />
                                        <span class="font-medium text-xs">{{ owner.shop.name }}</span>
                                        <span class="text-[10px] text-neutral-400">({{ owner.shop.slug }})</span>
                                    </div>
                                    <span v-else class="text-xs text-neutral-400 italic">No shop assigned</span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="toggleStatus(owner)"
                                        :title="owner.is_active ? 'Click to deactivate account' : 'Click to activate account'"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition"
                                        :class="owner.is_active
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-100'
                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 hover:bg-rose-100'"
                                    >
                                        <CheckCircle v-if="owner.is_active" class="w-3 h-3" />
                                        <XCircle v-else class="w-3 h-3" />
                                        <span>{{ owner.is_active ? 'Active' : 'Deactivated' }}</span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-center text-xs text-neutral-500 font-mono">
                                    {{ new Date(owner.created_at).toLocaleDateString() }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/shop-owners/${owner.id}/edit`"
                                            class="p-1.5 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 hover:text-neutral-900 transition"
                                            title="Edit Owner Profile"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </Link>
                                        <button
                                            @click="deleteOwner(owner)"
                                            class="p-1.5 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/40 text-neutral-400 hover:text-rose-600 transition"
                                            title="Delete Owner"
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
                <div v-if="owners.links.length > 3" class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <div class="text-neutral-500">
                        Page {{ owners.current_page }} of {{ owners.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, idx) in owners.links"
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
