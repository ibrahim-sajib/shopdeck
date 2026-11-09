<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $shopsCount = Schema::hasTable('shops') ? DB::table('shops')->count() : 0;
        $ownersCount = User::where('role', User::ROLE_SHOP_OWNER)->count();
        $productsCount = Schema::hasTable('products') ? DB::table('products')->count() : 0;

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'shopsCount' => $shopsCount,
                'ownersCount' => $ownersCount,
                'productsCount' => $productsCount,
            ],
        ]);
    }
}
