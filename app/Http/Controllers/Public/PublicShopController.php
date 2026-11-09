<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicShopController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        $shop = Shop::where('slug', $slug)->firstOrFail();

        abort_unless($shop->is_active, 404, 'This shop is currently inactive or unavailable.');

        $search = $request->input('search');

        $listings = $shop->shopProducts()
            ->where('is_active', true)
            ->whereHas('product', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['product'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_override', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Shop', [
            'shop' => [
                'id' => $shop->id,
                'name' => $shop->name,
                'slug' => $shop->slug,
                'description' => $shop->description,
                'logo' => $shop->logo,
            ],
            'listings' => $listings,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
