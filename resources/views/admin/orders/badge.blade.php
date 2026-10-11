<span @class([
    'rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap',
    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' => in_array($order->status, ['paid', 'shipped', 'delivered']),
    'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' => in_array($order->status, ['rejected', 'cancelled', 'refunded']),
    'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => in_array($order->status, ['pending', 'in_process']),
])>{{ \App\Models\Order::STATUSES[$order->status] ?? $order->status }}</span>
