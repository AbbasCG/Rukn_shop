<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'orders' => Order::count(),
            'revenue' => Order::with('orderItems')->get()->sum(function($order) {
                return $order->orderItems->sum(function($item) {
                    return $item->quantity * $item->price;
                });
            }),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
