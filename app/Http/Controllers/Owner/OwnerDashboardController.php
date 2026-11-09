<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class OwnerDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $shop = null;

        if (Schema::hasTable('shops')) {
            $shop = $user->shop;
        }

        $activeListingsCount = 0;
        $catalogCount = 0;

        if (Schema::hasTable('shop_products') && $shop) {
            $activeListingsCount = DB::table('shop_products')
                ->where('shop_id', $shop->id)
                ->where('is_active', true)
                ->count();
        }

        if (Schema::hasTable('products')) {
            $catalogCount = DB::table('products')
                ->where('is_active', true)
                ->count();
        }

        return Inertia::render('Owner/Dashboard', [
            'shop' => $shop,
            'stats' => [
                'activeListingsCount' => $activeListingsCount,
                'catalogCount' => $catalogCount,
            ],
        ]);
    }
}
