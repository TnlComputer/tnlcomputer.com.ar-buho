<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'Pagados para preparar' => [Order::where('status', Order::PAID)->count(), route('admin.orders.index', ['estado' => Order::PAID])],
                'Ventas del mes ($)' => [(int) Order::where('paid_at', '>=', now()->startOfMonth())->whereNotIn('status', [Order::CANCELLED, Order::REFUNDED])->sum('total'), route('admin.orders.index')],
                'Productos activos' => [Product::where('active', true)->count(), route('admin.products.index')],
                'En oferta' => [Product::where('active', true)->where('featured', true)->count(), route('admin.products.index', ['ofertas' => 1])],
            ],
            'unread' => Message::whereNull('read_at')->count(),
            'messages' => Message::latest()->take(8)->get(),
        ]);
    }
}
