<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

type Shop = {
    id: number;
    name: string;
    slug: string;
    owner_id: number | null;
};

type Owner = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    shop: Shop | null;
};

type Props = {
    owner: Owner;
    shops: Shop[];
};

const props = defineProps<Props>();

const form = useForm({
    _method: 'PUT',
    name: props.owner.name,
    email: props.owner.email,
    password: '',
    password_confirmation: '',
    shop_id: props.owner.shop ? props.owner.shop.id : (null as number | null),
    is_active: props.owner.is_active,
});

const submit = () => {
    form.post(`/admin/shop-owners/${props.owner.id}`);
};
</script>

<template>
    <AdminLayout title="Edit Shop Owner">
        <Head :title="`Edit ${owner.name}`" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    href="/admin/shop-owners"
                    class="p-2 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Edit Owner: {{ owner.name }}</h2>
                    <p class="text-xs text-neutral-500">Update owner details, credentials, or reassign shop.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 sm:p-8 shadow-xs space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        required
                    />
                    <div v-if="form.errors.name" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Email Address <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        required
                    />
                    <div v-if="form.errors.email" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- Password update (Optional) -->
                <div class="p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200 dark:border-neutral-700 space-y-4">
                    <p class="text-xs text-neutral-500 font-medium">
                        Leave password fields blank to keep the current password.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                                New Password
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="New password"
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            />
                            <div v-if="form.errors.password" class="mt-1 text-xs text-rose-600 font-medium">
                                {{ form.errors.password }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Confirm New Password
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Repeat new password"
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            />
                        </div>
                    </div>
                </div>

                <!-- Assign to Shop -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Assign Shop
                    </label>
                    <select
                        v-model="form.shop_id"
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                        <option :value="null">-- No Shop Assigned --</option>
                        <option v-for="shop in shops" :key="shop.id" :value="shop.id">
                            {{ shop.name }} ({{ shop.slug }})
                            <template v-if="shop.owner_id && shop.owner_id !== owner.id">
                                - currently owned by user #{{ shop.owner_id }}
                            </template>
                        </option>
                    </select>
                    <div v-if="form.errors.shop_id" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.shop_id }}
                    </div>
                </div>

                <!-- Active Status -->
                <div class="pt-2">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-neutral-300"
                        />
                        <span class="text-sm font-medium">Account is Active (Can log in to owner portal)</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end gap-3">
                    <Link
                        href="/admin/shop-owners"
                        class="px-4 py-2 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
