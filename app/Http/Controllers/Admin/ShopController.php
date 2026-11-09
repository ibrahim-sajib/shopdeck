<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShopRequest;
use App\Http\Requests\Admin\UpdateShopRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $shops = Shop::with('owner')
            ->withCount('shopProducts')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Shops/Index', [
            'shops' => $shops,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        $owners = User::where('role', User::ROLE_SHOP_OWNER)
            ->where('is_active', true)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Shops/Create', [
            'owners' => $owners,
        ]);
    }

    public function store(StoreShopRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('shops', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        Shop::create($data);

        return redirect()->route('admin.shops.index')->with('success', 'Shop created successfully.');
    }

    public function edit(Shop $shop): Response
    {
        $owners = User::where('role', User::ROLE_SHOP_OWNER)
            ->where('is_active', true)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Shops/Edit', [
            'shop' => $shop,
            'owners' => $owners,
        ]);
    }

    public function update(UpdateShopRequest $request, Shop $shop): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($shop->logo && Storage::disk('public')->exists($shop->logo)) {
                Storage::disk('public')->delete($shop->logo);
            }
            $data['logo'] = $request->file('logo')->store('shops', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $shop->update($data);

        return redirect()->route('admin.shops.index')->with('success', 'Shop updated successfully.');
    }

    public function toggleStatus(Shop $shop): RedirectResponse
    {
        $shop->update([
            'is_active' => ! $shop->is_active,
        ]);

        $status = $shop->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Shop '{$shop->name}' {$status} successfully.");
    }

    public function destroy(Shop $shop): RedirectResponse
    {
        if ($shop->logo && Storage::disk('public')->exists($shop->logo)) {
            Storage::disk('public')->delete($shop->logo);
        }

        $shop->delete();

        return redirect()->route('admin.shops.index')->with('success', 'Shop deleted successfully.');
    }
}
