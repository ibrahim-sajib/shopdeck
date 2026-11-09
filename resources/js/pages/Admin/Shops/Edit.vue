<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Upload, Store } from '@lucide/vue';
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
    owner_id: number | null;
    is_active: boolean;
};

type Props = {
    shop: Shop;
    owners: Owner[];
};

const props = defineProps<Props>();

const form = useForm<{
    _method: string;
    name: string;
    slug: string;
    owner_id: number | null;
    description: string;
    logo: File | null;
    is_active: boolean;
}>({
    _method: 'PUT',
    name: props.shop.name,
    slug: props.shop.slug,
    owner_id: props.shop.owner_id,
    description: props.shop.description || '',
    logo: null,
    is_active: props.shop.is_active,
});

const logoPreview = ref<string | null>(
    props.shop.logo ? `/storage/${props.shop.logo}` : null
);

const handleLogoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(`/admin/shops/${props.shop.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <AdminLayout title="Edit Shop">
        <Head :title="`Edit ${shop.name}`" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    href="/admin/shops"
                    class="p-2 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Edit Shop: {{ shop.name }}</h2>
                    <p class="text-xs text-neutral-500">Update storefront details and owner assignment.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 sm:p-8 shadow-xs space-y-5">
                <!-- Shop Name -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Shop Name <span class="text-rose-500">*</span>
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

                <!-- Slug -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Storefront Slug <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex rounded-lg shadow-xs">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-neutral-300 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-800 text-neutral-500 text-xs font-mono">
                            /shops/
                        </span>
                        <input
                            v-model="form.slug"
                            type="text"
                            class="flex-1 min-w-0 block w-full px-3.5 py-2.5 text-sm font-mono bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-r-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            required
                        />
                    </div>
                    <div v-if="form.errors.slug" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.slug }}
                    </div>
                </div>

                <!-- Owner Assignment -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Shop Owner
                    </label>
                    <select
                        v-model="form.owner_id"
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                        <option :value="null">-- Select an Active Owner --</option>
                        <option v-for="owner in owners" :key="owner.id" :value="owner.id">
                            {{ owner.name }} ({{ owner.email }})
                        </option>
                    </select>
                    <div v-if="form.errors.owner_id" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.owner_id }}
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Description
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    ></textarea>
                    <div v-if="form.errors.description" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.description }}
                    </div>
                </div>

                <!-- Logo Upload -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Shop Logo
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 flex items-center justify-center overflow-hidden shrink-0">
                            <img v-if="logoPreview" :src="logoPreview" alt="Logo preview" class="w-full h-full object-cover" />
                            <Store v-else class="w-8 h-8 text-neutral-400" />
                        </div>
                        <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                            <Upload class="w-4 h-4" />
                            <span>Change Logo Image</span>
                            <input type="file" accept="image/*" class="hidden" @change="handleLogoChange" />
                        </label>
                    </div>
                    <div v-if="form.errors.logo" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.logo }}
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
                        <span class="text-sm font-medium">Shop is Active (Visible on public storefront)</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end gap-3">
                    <Link
                        href="/admin/shops"
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
