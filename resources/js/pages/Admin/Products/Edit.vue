<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Upload, Package } from '@lucide/vue';
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
};

type Props = {
    product: Product;
};

const props = defineProps<Props>();

const form = useForm<{
    _method: string;
    name: string;
    sku: string;
    description: string;
    thumbnail: File | null;
    is_active: boolean;
}>({
    _method: 'PUT',
    name: props.product.name,
    sku: props.product.sku || '',
    description: props.product.description || '',
    thumbnail: null,
    is_active: props.product.is_active,
});

const thumbnailPreview = ref<string | null>(
    props.product.thumbnail ? `/storage/${props.product.thumbnail}` : null
);

const handleThumbnailChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.thumbnail = file;
        thumbnailPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(`/admin/products/${props.product.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <AdminLayout title="Edit Catalog Product">
        <Head :title="`Edit ${product.name}`" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    href="/admin/products"
                    class="p-2 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Edit Product: {{ product.name }}</h2>
                    <p class="text-xs text-neutral-500">
                        Created by {{ product.creator?.name || 'System' }} ({{ product.creator?.role || 'admin' }}).
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 sm:p-8 shadow-xs space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Product Title <span class="text-rose-500">*</span>
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

                <!-- SKU -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        SKU (Stock Keeping Unit)
                    </label>
                    <input
                        v-model="form.sku"
                        type="text"
                        class="w-full px-3.5 py-2.5 text-sm font-mono bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    />
                    <div v-if="form.errors.sku" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.sku }}
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Standard Catalog Description
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

                <!-- Thumbnail -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Product Thumbnail
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 flex items-center justify-center overflow-hidden shrink-0">
                            <img v-if="thumbnailPreview" :src="thumbnailPreview" alt="Thumbnail preview" class="w-full h-full object-cover" />
                            <Package v-else class="w-8 h-8 text-neutral-400" />
                        </div>
                        <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                            <Upload class="w-4 h-4" />
                            <span>Change Image</span>
                            <input type="file" accept="image/*" class="hidden" @change="handleThumbnailChange" />
                        </label>
                    </div>
                    <div v-if="form.errors.thumbnail" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.thumbnail }}
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
                        <span class="text-sm font-medium">Product is Active (Available for shop listing)</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end gap-3">
                    <Link
                        href="/admin/products"
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
