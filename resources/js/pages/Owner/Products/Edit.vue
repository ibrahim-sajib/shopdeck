<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Upload, Package, Info } from '@lucide/vue';
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
    is_active: boolean;
    product: GlobalProduct;
};

type Shop = {
    id: number;
    name: string;
    slug: string;
};

type Props = {
    listing: Listing;
    shop: Shop;
};

const props = defineProps<Props>();

const form = useForm<{
    _method: string;
    price: string | number;
    name_override: string;
    description_override: string;
    thumbnail_override: File | null;
    is_active: boolean;
}>({
    _method: 'PUT',
    price: props.listing.price,
    name_override: props.listing.name_override || '',
    description_override: props.listing.description_override || '',
    thumbnail_override: null,
    is_active: props.listing.is_active,
});

const thumbnailPreview = ref<string | null>(
    props.listing.thumbnail_override
        ? `/storage/${props.listing.thumbnail_override}`
        : props.listing.product.thumbnail
          ? `/storage/${props.listing.product.thumbnail}`
          : null
);

const handleThumbnailChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.thumbnail_override = file;
        thumbnailPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(`/owner/products/${props.listing.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <OwnerLayout title="Edit Shop Listing">
        <Head :title="`Edit ${listing.name_override || listing.product.name}`" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    href="/owner/products"
                    class="p-2 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Edit Shop Listing</h2>
                    <p class="text-xs text-neutral-500">
                        Customize pricing and presentation specifically for <span class="font-semibold text-emerald-600">{{ shop.name }}</span>.
                    </p>
                </div>
            </div>

            <!-- Global Product Info Box -->
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs flex items-center gap-4">
                <div class="w-14 h-14 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden shrink-0">
                    <img
                        v-if="listing.product.thumbnail"
                        :src="`/storage/${listing.product.thumbnail}`"
                        :alt="listing.product.name"
                        class="w-full h-full object-cover"
                    />
                    <Package v-else class="w-6 h-6 text-neutral-400" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Catalog Reference</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 font-mono">
                            {{ listing.product.sku || 'No SKU' }}
                        </span>
                    </div>
                    <div class="font-bold text-sm truncate text-neutral-900 dark:text-neutral-100">
                        {{ listing.product.name }}
                    </div>
                    <div class="text-xs text-neutral-500 truncate">
                        {{ listing.product.description || 'No base description.' }}
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 sm:p-8 shadow-xs space-y-5">
                <!-- Shop Selling Price -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Shop Selling Price (BDT) <span class="text-rose-500">*</span>
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

                <!-- Custom Name Override -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Custom Shop Title Override
                    </label>
                    <input
                        v-model="form.name_override"
                        type="text"
                        :placeholder="`Default: ${listing.product.name}`"
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                    />
                    <p class="mt-1 text-[11px] text-neutral-500">
                        Leave blank to display the catalog title on your storefront.
                    </p>
                    <div v-if="form.errors.name_override" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.name_override }}
                    </div>
                </div>

                <!-- Custom Description Override -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Custom Shop Description Override
                    </label>
                    <textarea
                        v-model="form.description_override"
                        rows="3"
                        placeholder="Write tailored sales copy or details for your shop customers..."
                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                    ></textarea>
                    <p class="mt-1 text-[11px] text-neutral-500">
                        Leave blank to fall back to the catalog description.
                    </p>
                    <div v-if="form.errors.description_override" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.description_override }}
                    </div>
                </div>

                <!-- Custom Thumbnail Override -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Custom Thumbnail Image Override
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 flex items-center justify-center overflow-hidden shrink-0">
                            <img v-if="thumbnailPreview" :src="thumbnailPreview" alt="Thumbnail preview" class="w-full h-full object-cover" />
                            <Package v-else class="w-8 h-8 text-neutral-400" />
                        </div>
                        <div>
                            <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                <Upload class="w-4 h-4" />
                                <span>Upload Custom Image</span>
                                <input type="file" accept="image/*" class="hidden" @change="handleThumbnailChange" />
                            </label>
                            <p class="mt-1.5 text-[11px] text-neutral-500">Overrides the global catalog picture on your shop.</p>
                        </div>
                    </div>
                    <div v-if="form.errors.thumbnail_override" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ form.errors.thumbnail_override }}
                    </div>
                </div>

                <!-- Active Status -->
                <div class="pt-2">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-neutral-300"
                        />
                        <span class="text-sm font-medium">Listing is Active (Visible on your storefront)</span>
                    </label>
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
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </OwnerLayout>
</template>
