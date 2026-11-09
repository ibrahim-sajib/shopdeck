<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Upload, Package, Sparkles } from '@lucide/vue';
import { ref } from 'vue';
import OwnerLayout from '@/layouts/OwnerLayout.vue';

type Shop = {
    id: number;
    name: string;
};

type Props = {
    shop: Shop | null;
};

defineProps<Props>();

const form = useForm<{
    name: string;
    sku: string;
    description: string;
    thumbnail: File | null;
    price: string;
}>({
    name: '',
    sku: '',
    description: '',
    thumbnail: null,
    price: '',
});

const thumbnailPreview = ref<string | null>(null);

const handleThumbnailChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.thumbnail = file;
        thumbnailPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post('/owner/products', {
        forceFormData: true,
    });
};
</script>

<template>
    <OwnerLayout title="Create Product">
        <Head title="Create New Product" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    href="/owner/products"
                    class="p-2 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Create & List New Product</h2>
                    <p class="text-xs text-neutral-500">
                        This item will join the shared product catalog and be listed directly in <span class="font-semibold text-emerald-600">{{ shop?.name }}</span>.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 sm:p-8 shadow-xs space-y-5">
                <div class="p-3.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-200 flex items-start gap-2.5">
                    <Sparkles class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                    <div>
                        <strong>Shared Catalog Feature:</strong> Products you create are saved to the global catalog so other shops can discover them, while being automatically added to your store at the selling price you define below.
                    </div>
                </div>

                <!-- Product Title -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Product Title <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="e.g. Handmade Silk Scarf"
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                        required
                    />
                    <div v-if="form.errors.name" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- Initial Shop Selling Price -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Your Shop Selling Price (BDT) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-lg shadow-xs">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400 font-bold text-sm">
                            ৳
                        </span>
                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="w-full pl-9 pr-3.5 py-2.5 text-sm font-semibold bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                            required
                        />
                    </div>
                    <div v-if="form.errors.price" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.price }}
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
                        placeholder="e.g. HND-SLK-001"
                        class="w-full px-3.5 py-2.5 text-sm font-mono bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                    />
                    <p class="mt-1 text-[11px] text-neutral-500">Optional unique identifier across the catalog.</p>
                    <div v-if="form.errors.sku" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.sku }}
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Product Description
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Describe the product material, sizing, features, etc..."
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                    ></textarea>
                    <div v-if="form.errors.description" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.description }}
                    </div>
                </div>

                <!-- Thumbnail -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Product Thumbnail Image
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 flex items-center justify-center overflow-hidden shrink-0">
                            <img v-if="thumbnailPreview" :src="thumbnailPreview" alt="Thumbnail preview" class="w-full h-full object-cover" />
                            <Package v-else class="w-8 h-8 text-neutral-400" />
                        </div>
                        <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                            <Upload class="w-4 h-4" />
                            <span>Select Image</span>
                            <input type="file" accept="image/*" class="hidden" @change="handleThumbnailChange" />
                        </label>
                    </div>
                    <div v-if="form.errors.thumbnail" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.thumbnail }}
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end gap-3">
                    <Link
                        href="/owner/products"
                        class="px-4 py-2 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create & List Product' }}
                    </button>
                </div>
            </form>
        </div>
    </OwnerLayout>
</template>
