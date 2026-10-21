<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CspReportController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\GoogleFeedController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

// Instalador de un solo uso (hosting sin SSH). Solo existe con INSTALL_TOKEN en el .env y sin installed.lock.
if (InstallController::enabled()) {
    Route::get('/instalar', [InstallController::class, 'show']);
    Route::post('/instalar', [InstallController::class, 'run'])->middleware('throttle:5,1');
}

// Sitio público. Se registra dos veces: en español (/...) y en inglés (/en/..., rutas "en.*").
$site = function () {
    Route::controller(PageController::class)->group(function () {
        Route::get('/', 'home')->name('home');
        Route::get('/hosting', 'hosting')->name('hosting');
        Route::get('/diseno-web', 'design')->name('design');
        Route::get('/service', 'service')->name('service');
        Route::get('/contacto', 'contact')->name('contact');
        Route::get('/envios-y-devoluciones', 'policies')->name('policies');
        Route::get('/portfolio', 'portfolio')->name('portfolio');
        Route::get('/portfolio/descargar', 'portfolioPdf')->middleware('throttle:10,1')->name('portfolio.pdf');
    });

    Route::middleware('throttle:forms')->group(function () {
        Route::post('/contacto', [FormController::class, 'contact'])->name('contact.send');
        Route::post('/service', [FormController::class, 'service'])->name('service.send');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::post('/pedido/{order}/pagar', [CheckoutController::class, 'pay'])->name('orders.pay');
        Route::post('/pedido/{order}/cancelar', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/pedido/{order}/opinar', [ReviewController::class, 'store'])->name('orders.review');
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
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('webhooks.mercadopago');

// Reportes de la Content-Security-Policy (los manda el navegador, sin cookies ni CSRF).
Route::post('/csp-report', CspReportController::class)
    ->middleware('throttle:20,1')
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('csp.report');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/feed/google.xml', GoogleFeedController::class)->name('feed.google');

// URLs del sitio viejo: 301 para no perder posicionamiento ni links compartidos.
foreach ([
    'index.html' => 'home', 'hosting.html' => 'hosting', 'design.html' => 'design', 'hardware.html' => 'shop.index',
    'producto.html' => 'shop.index', 'carrito.html' => 'cart.index', 'service.html' => 'service',
    'contacto.html' => 'contact', 'portfolio.html' => 'portfolio',
    'hardware' => 'shop.index', 'design' => 'design', 'producto' => 'shop.index',
] as $old => $route) {
    Route::get($old, fn () => redirect()->route($route, status: 301));
}

// Panel de administración (solo en español)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'create'])->middleware('guest')->name('login');
    Route::post('login', [Admin\AuthController::class, 'store'])->middleware(['guest', 'throttle:login']);
    Route::get('verificacion', [Admin\AuthController::class, 'challenge'])->middleware('guest')->name('two-factor');
    Route::post('verificacion', [Admin\AuthController::class, 'verify'])->middleware(['guest', 'throttle:two-factor']);
    Route::post('logout', [Admin\AuthController::class, 'destroy'])->middleware('auth')->name('logout');

    // auth.session: al cambiar la contraseña se cierran las sesiones abiertas en otros dispositivos.
    Route::middleware(['auth', 'auth.session'])->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::get('seguridad', [Admin\SecurityController::class, 'show'])->name('security');
        Route::middleware('throttle:two-factor')->group(function () {
            Route::post('seguridad/2fa', [Admin\SecurityController::class, 'enable'])->name('security.enable');
            Route::delete('seguridad/2fa', [Admin\SecurityController::class, 'disable'])->name('security.disable');
            Route::post('seguridad/codigos', [Admin\SecurityController::class, 'recoveryCodes'])->name('security.codes');
            Route::put('seguridad/contrasena', [Admin\SecurityController::class, 'password'])->name('security.password');
        });
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
        Route::get('resenas', [Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::get('resenas/cartel', [Admin\ReviewController::class, 'poster'])->name('reviews.poster');
        Route::put('resenas/{review}', [Admin\ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('resenas/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::resource('mensajes', Admin\MessageController::class)->only(['index', 'show', 'destroy'])
            ->parameters(['mensajes' => 'message'])->names('messages');
    });
});
