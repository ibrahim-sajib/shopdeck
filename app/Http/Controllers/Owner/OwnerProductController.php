<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreOwnerProductRequest;
use App\Http\Requests\Owner\UpdateShopProductRequest;
use App\Models\Product;
use App\Models\ShopProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OwnerProductController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $shop = $user->shop;

        if (! $shop) {
            return Inertia::render('Owner/Products/Index', [
                'listings' => [
                    'data' => [],
                    'links' => [],
                    'total' => 0,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
                'shop' => null,
                'filters' => [],
            ]);
        }

        $search = $request->input('search');

        $listings = ShopProduct::where('shop_id', $shop->id)
            ->with(['product.creator'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_override', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Owner/Products/Index', [
            'listings' => $listings,
            'shop' => $shop,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Owner/Products/Create', [
            'shop' => $request->user()->shop,
        ]);
    }

    public function store(StoreOwnerProductRequest $request): RedirectResponse
    {
        $user = $request->user();
        $shop = $user->shop;

        if (! $shop) {
            return back()->with('error', 'You must have an assigned shop to create products.');
        }

        DB::transaction(function () use ($request, $user, $shop) {
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('products', 'public');
            }

            // 1. Create global catalog product
            $product = Product::create([
                'name' => $request->name,
                'sku' => $request->sku ?: null,
                'description' => $request->description ?: null,
                'thumbnail' => $thumbnailPath,
                'created_by' => $user->id,
                'is_active' => true,
            ]);

            // 2. Add listing to own shop
            ShopProduct::create([
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'price' => $request->price,
                'name_override' => null,
                'description_override' => null,
                'thumbnail_override' => null,
                'is_active' => true,
            ]);
        });

        return redirect()->route('owner.products.index')->with('success', 'Product created and listed in your shop.');
    }

    public function edit(Request $request, ShopProduct $shopProduct): Response
    {
        $user = $request->user();
        $shop = $user->shop;

        abort_unless($shop && $shopProduct->shop_id === $shop->id, 403, 'Unauthorized access to this shop listing.');

        $shopProduct->load(['product.creator']);

        return Inertia::render('Owner/Products/Edit', [
            'listing' => $shopProduct,
            'shop' => $shop,
        ]);
    }

    public function update(UpdateShopProductRequest $request, ShopProduct $shopProduct): RedirectResponse
    {
        $user = $request->user();
        $shop = $user->shop;

        abort_unless($shop && $shopProduct->shop_id === $shop->id, 403, 'Unauthorized access to this shop listing.');

        $data = [
            'price' => $request->price,
            'name_override' => $request->name_override ?: null,
            'description_override' => $request->description_override ?: null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('thumbnail_override')) {
            if ($shopProduct->thumbnail_override && Storage::disk('public')->exists($shopProduct->thumbnail_override)) {
                Storage::disk('public')->delete($shopProduct->thumbnail_override);
            }
            $data['thumbnail_override'] = $request->file('thumbnail_override')->store('shops/overrides', 'public');
        }

        $shopProduct->update($data);

        return redirect()->route('owner.products.index')->with('success', 'Shop listing updated successfully.');
    }

    public function toggleStatus(Request $request, ShopProduct $shopProduct): RedirectResponse
    {
        $user = $request->user();
        $shop = $user->shop;

        abort_unless($shop && $shopProduct->shop_id === $shop->id, 403, 'Unauthorized access to this shop listing.');

        $shopProduct->update([
            'is_active' => ! $shopProduct->is_active,
        ]);

        $status = $shopProduct->is_active ? 'active' : 'inactive';

        return back()->with('success', "Listing status changed to {$status}.");
    }

    public function destroy(Request $request, ShopProduct $shopProduct): RedirectResponse
    {
        $user = $request->user();
        $shop = $user->shop;

        abort_unless($shop && $shopProduct->shop_id === $shop->id, 403, 'Unauthorized access to this shop listing.');

        if ($shopProduct->thumbnail_override && Storage::disk('public')->exists($shopProduct->thumbnail_override)) {
            Storage::disk('public')->delete($shopProduct->thumbnail_override);
        }

        $shopProduct->delete();

        return redirect()->route('owner.products.index')->with('success', 'Product unassigned and removed from your shop.');
    }
}
