<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

// Instalador de un solo uso (hosting sin SSH). Solo existe con INSTALL_TOKEN en el .env y sin installed.lock.
if (\App\Http\Controllers\InstallController::enabled()) {
    Route::get('/instalar', [\App\Http\Controllers\InstallController::class, 'show']);
    Route::post('/instalar', [\App\Http\Controllers\InstallController::class, 'run'])->middleware('throttle:5,1');
}

// Sitio público. Se registra dos veces: en español (/...) y en inglés (/en/..., rutas "en.*").
$site = function () {
    Route::controller(PageController::class)->group(function () {
        Route::get('/', 'home')->name('home');
        Route::get('/hosting', 'hosting')->name('hosting');
        Route::get('/diseno-web', 'design')->name('design');
        Route::get('/service', 'service')->name('service');
        Route::get('/contacto', 'contact')->name('contact');
        Route::get('/portfolio', 'portfolio')->name('portfolio');
    });

    Route::middleware('throttle:forms')->group(function () {
        Route::post('/contacto', [FormController::class, 'contact'])->name('contact.send');
        Route::post('/service', [FormController::class, 'service'])->name('service.send');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::post('/pedido/{order}/pagar', [CheckoutController::class, 'pay'])->name('orders.pay');
    });

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
    Route::get('/envio/cotizar', [CheckoutController::class, 'quote'])->middleware('throttle:30,1')->name('shipping.quote');
    Route::get('/pedido/{order}', [OrderController::class, 'show'])->middleware('throttle:30,1')->name('orders.show');

    Route::get('/tienda', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/tienda/rubro/{category}', [ShopController::class, 'index'])->name('shop.category');
    Route::get('/tienda/producto/{product}', [ShopController::class, 'show'])->name('shop.show');

    Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrito/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/carrito/{product}', [CartController::class, 'update'])->name('cart.update');
};

Route::group([], $site);
Route::prefix('en')->name('en.')->group($site);

// Avisos de pago de Mercado Pago (sin CSRF: los valida MercadoPago::validSignature y la consulta a la API).
Route::post('/webhooks/mercadopago', MercadoPagoWebhookController::class)
    ->middleware('throttle:120,1')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
    ->name('webhooks.mercadopago');

// URLs del sitio viejo: 301 para no perder posicionamiento ni links compartidos.
foreach ([
    'index.html' => 'home', 'hosting.html' => 'hosting', 'design.html' => 'design', 'hardware.html' => 'shop.index',
    'producto.html' => 'shop.index', 'carrito.html' => 'cart.index', 'service.html' => 'service',
    'contacto.html' => 'contact', 'portfolio.html' => 'portfolio',
] as $old => $route) {
    Route::get($old, fn () => redirect()->route($route, status: 301));
}

// Panel de administración (solo en español)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'create'])->middleware('guest')->name('login');
    Route::post('login', [Admin\AuthController::class, 'store'])->middleware(['guest', 'throttle:login']);
    Route::post('logout', [Admin\AuthController::class, 'destroy'])->middleware('auth')->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::get('pedidos', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('pedidos/{order:id}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::put('pedidos/{order:id}', [Admin\OrderController::class, 'update'])->name('orders.update');
        Route::post('pedidos/{order:id}/consultar-pago', [Admin\OrderController::class, 'refresh'])->name('orders.refresh');
        Route::resource('zonas-envio', Admin\ShippingZoneController::class)->except('show')
            ->parameters(['zonas-envio' => 'zone'])->names('zones');
        Route::get('productos/importar', [Admin\ProductImportController::class, 'create'])->name('products.import');
        Route::post('productos/importar', [Admin\ProductImportController::class, 'store'])->name('products.import.store');
        Route::resource('productos', Admin\ProductController::class)->except('show')
            ->parameters(['productos' => 'product'])->names('products');
        Route::resource('rubros', Admin\CategoryController::class)->except('show')
            ->parameters(['rubros' => 'category'])->names('categories');
        Route::resource('hosting', Admin\HostingPlanController::class)->except('show')
            ->parameters(['hosting' => 'plan'])->names('plans');
        Route::resource('proyectos', Admin\ProjectController::class)->except('show')
            ->parameters(['proyectos' => 'project'])->names('projects');
        Route::resource('skills', Admin\SkillController::class)->except('show')->names('skills');
        Route::resource('certificaciones', Admin\CertificationController::class)->except('show')
            ->parameters(['certificaciones' => 'certification'])->names('certifications');
        Route::resource('mensajes', Admin\MessageController::class)->only(['index', 'show', 'destroy'])
            ->parameters(['mensajes' => 'message'])->names('messages');
    });
});
