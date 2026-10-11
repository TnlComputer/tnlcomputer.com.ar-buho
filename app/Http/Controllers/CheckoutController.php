<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\MercadoPago;
use App\Support\Cart;
use App\Support\PostalCodeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart, private MercadoPago $mp) {}

    public function create(): View|RedirectResponse
    {
        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            return redirect(lroute('cart.index'));
        }

        return view('shop.checkout', [
            'lines' => $lines,
            'subtotal' => $this->cart->total($lines),
            'pickups' => ShippingZone::active()->where('is_pickup', true)->get(),
            'paymentsEnabled' => $this->mp->configured(),
        ]);
    }

    /** Cotiza el envío para un CP (lo usa el formulario mientras el cliente escribe). */
    public function quote(Request $request, PostalCodeResolver $resolver): JsonResponse
    {
        $cp = $resolver->resolve($request->query('cp'), $request->query('address'), $request->query('city'), $request->query('province'));
        if (! $cp) {
            return response()->json(['ok' => false, 'message' => __('No pudimos ubicar la dirección: completá el código postal.')], 422);
        }
        $zone = ShippingZone::forPostalCode($cp);
        if (! $zone) {
            return response()->json(['ok' => false, 'message' => __('No hacemos envíos a ese código postal. Consultanos por WhatsApp.')], 422);
        }

        $subtotal = $this->cart->total($this->cart->lines());
        $cost = $zone->costFor($subtotal);

        return response()->json([
            'ok' => true,
            'postal_code' => $cp,
            'zone' => $zone->tr('name'),
            'eta' => $zone->tr('eta'),
            'cost' => $cost,
            'cost_label' => $cost > 0 ? money($cost) : __('Gratis'),
            'total_label' => money($subtotal + $cost),
            'free_from' => $zone->free_from && $cost > 0
                ? __('Envío gratis comprando desde :amount', ['amount' => money($zone->free_from)])
                : null,
        ]);
    }

    public function store(Request $request, PostalCodeResolver $resolver): RedirectResponse
    {
        if ($request->filled('website')) { // honeypot
            return redirect(lroute('cart.index'));
        }

        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            return redirect(lroute('cart.index'));
        }

        $delivery = $request->input('delivery') === 'shipping';
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9 +()\-]{6,}$/'],
            'dni' => ['nullable', 'string', 'max:20', 'regex:/^[0-9.\-]{6,12}$/'],
            'delivery' => ['required', Rule::in(['pickup', 'shipping'])],
            'pickup_zone' => [Rule::requiredIf(! $delivery), 'nullable', Rule::exists('shipping_zones', 'id')->where('is_pickup', true)->where('active', true)],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => [Rule::requiredIf($delivery), 'nullable', 'string', 'max:200'],
            'city' => [Rule::requiredIf($delivery), 'nullable', 'string', 'max:100'],
            'province' => [Rule::requiredIf($delivery), 'nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['accepted'],
        ]);

        // Todo se recalcula acá, con los precios de la base: nada del navegador define montos.
        $subtotal = $this->cart->total($lines);
        if ($delivery) {
            $data['postal_code'] = $resolver->resolve($data['postal_code'] ?? null, $data['address'], $data['city'], $data['province']);
            if (! $data['postal_code']) {
                return back()->withInput()->withErrors(['postal_code' => __('No pudimos ubicar la dirección: completá el código postal.')]);
            }
        }
        $zone = $delivery
            ? ShippingZone::forPostalCode($data['postal_code'])
            : ShippingZone::find($data['pickup_zone']);

        if (! $zone) {
            return back()->withInput()->withErrors(['postal_code' => __('No hacemos envíos a ese código postal. Consultanos por WhatsApp.')]);
        }

        $shipping = $zone->costFor($subtotal);

        $order = DB::transaction(function () use ($data, $delivery, $zone, $subtotal, $shipping, $lines, $request) {
            $order = Order::create([
                'customer_name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'dni' => $data['dni'] ?? null,
                'shipping_zone_id' => $zone->id,
                'shipping_zone_name' => $zone->name,
                'postal_code' => $delivery ? $data['postal_code'] : null,
                'address' => $delivery ? $data['address'] : null,
                'city' => $delivery ? $data['city'] : null,
                'province' => $delivery ? $data['province'] : null,
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'total' => $subtotal + $shipping,
                'locale' => app()->getLocale(),
                'ip' => $request->ip(),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line->product->id,
                    'code' => $line->product->code,
                    'name' => $line->product->name,
                    'unit_price' => $line->product->price,
                    'qty' => $line->qty,
                    'subtotal' => $line->subtotal,
                ]);
            }

            return $order;
        });

        $this->cart->clear();

        return $this->redirectToPayment($order);
    }

    /** Reintentar el pago de un pedido existente (ej. tarjeta rechazada). */
    public function pay(Order $order): RedirectResponse
    {
        abort_unless($order->canPay(), 403);

        return $this->redirectToPayment($order);
    }

    private function redirectToPayment(Order $order): RedirectResponse
    {
        try {
            return redirect()->away($this->mp->createPreference($order));
        } catch (\Throwable $e) {
            Log::error("No se pudo iniciar el pago del pedido {$order->number()}", ['error' => $e->getMessage()]);

            return redirect(lroute('orders.show', $order))
                ->with('payment_error', __('No pudimos conectar con Mercado Pago. Tu pedido quedó guardado: probá pagar de nuevo en unos minutos o escribinos.'));
        }
    }
}
