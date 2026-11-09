<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShopOwnerRequest;
use App\Http\Requests\Admin\UpdateShopOwnerRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ShopOwnerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $owners = User::where('role', User::ROLE_SHOP_OWNER)
            ->with('shop')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/ShopOwners/Index', [
            'owners' => $owners,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        $shops = Shop::orderBy('name')->get();

        return Inertia::render('Admin/ShopOwners/Create', [
            'shops' => $shops,
        ]);
    }

    public function store(StoreShopOwnerRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => User::ROLE_SHOP_OWNER,
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($request->filled('shop_id')) {
                Shop::where('id', $request->shop_id)->update(['owner_id' => $user->id]);
            }
        });

        return redirect()->route('admin.shop-owners.index')->with('success', 'Shop owner account created successfully.');
    }

    public function edit(User $user): Response
    {
        abort_unless($user->isShopOwner(), 404);

        $user->load('shop');
        $shops = Shop::orderBy('name')->get();

        return Inertia::render('Admin/ShopOwners/Edit', [
            'owner' => $user,
            'shops' => $shops,
        ]);
    }

    public function update(UpdateShopOwnerRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->isShopOwner(), 404);

        DB::transaction(function () use ($request, $user) {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'is_active' => $request->boolean('is_active', true),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            if ($request->has('shop_id')) {
                // If shop changed or unassigned
                $currentShopId = $user->shop?->id;
                $newShopId = $request->shop_id ? (int) $request->shop_id : null;

                if ($currentShopId && $currentShopId !== $newShopId) {
                    Shop::where('id', $currentShopId)->update(['owner_id' => null]);
                }

                if ($newShopId) {
                    Shop::where('id', $newShopId)->update(['owner_id' => $user->id]);
                }
            }
        });

        return redirect()->route('admin.shop-owners.index')->with('success', 'Shop owner updated successfully.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_unless($user->isShopOwner(), 404);

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Owner account '{$user->name}' {$status} successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->isShopOwner(), 404);

        DB::transaction(function () use ($user) {
            Shop::where('owner_id', $user->id)->update(['owner_id' => null]);
            $user->delete();
        });

        return redirect()->route('admin.shop-owners.index')->with('success', 'Shop owner account deleted successfully.');
    }
}
