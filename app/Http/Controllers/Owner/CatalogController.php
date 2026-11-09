<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AddProductToShopRequest;
use App\Models\Product;
use App\Models\ShopProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $shop = $user->shop;
        $search = $request->input('search');

        $products = Product::where('is_active', true)
            ->with('creator')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $addedProductIds = $shop ? $shop->shopProducts()->pluck('product_id')->toArray() : [];
        $existingListings = $shop ? $shop->shopProducts()->pluck('id', 'product_id')->toArray() : [];

        return Inertia::render('Owner/Catalog/Index', [
            'products' => $products,
            'addedProductIds' => $addedProductIds,
            'existingListings' => $existingListings,
            'shop' => $shop,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function add(AddProductToShopRequest $request, Product $product): RedirectResponse
    {
        $user = $request->user();
        $shop = $user->shop;

        if (! $shop) {
            return back()->with('error', 'You must have an assigned shop to add products.');
        }

        if (! $product->is_active) {
            return back()->with('error', 'This product is currently inactive and cannot be listed.');
        }

        $alreadyAdded = ShopProduct::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($alreadyAdded) {
            return back()->with('error', "Product '{$product->name}' is already listed in your shop.");
        }

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => $request->price,
            'name_override' => $request->name_override ?: null,
            'description_override' => $request->description_override ?: null,
            'is_active' => true,
        ]);

        return back()->with('success', "Successfully added '{$product->name}' to {$shop->name} listings at ৳" . number_format($request->price, 2) . ".");
    }
}
