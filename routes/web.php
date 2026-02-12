<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Customer\Home;
use App\Livewire\Customer\ProductDetail;
use App\Livewire\Customer\Cart;
use App\Livewire\Customer\Checkout;
use App\Livewire\Customer\MyOrders;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Orders;
use App\Livewire\Admin\DeliveryZones;
use App\Livewire\Admin\PaymentMethods;
use App\Livewire\Admin\Pos;
use App\Livewire\Admin\RolesPermissions;
use App\Livewire\Admin\Inventory;        // ← Nuevo
use App\Livewire\Pedidostv;
use App\Http\Controllers\Admin\PrintController;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

// ============================================
// RUTAS PÚBLICAS
// ============================================
Route::get('/', Home::class)->name('home');
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/producto/{slug}', ProductDetail::class)->name('product.detail');

// ============================================
// CLIENTES
// ============================================
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/carrito', Cart::class)->name('cart');
    Route::get('/checkout', Checkout::class)->name('checkout');
    Route::get('/mis-pedidos', MyOrders::class)->name('my-orders');
});

// ============================================
// ADMINISTRACIÓN
// ============================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', Dashboard::class)
        ->middleware('permission:view dashboard')
        ->name('dashboard');

    Route::get('/pos', Pos::class)
        ->middleware('permission:view pos')
        ->name('pos');

    Route::get('/pedidos', Orders::class)
        ->middleware('permission:manage orders')
        ->name('orders');

    Route::get('/orders/{order}/print', [PrintController::class, 'showTicket'])
        ->middleware('permission:manage orders')
        ->name('orders.print');

    Route::get('/productos', Products::class)
        ->middleware('permission:manage products')
        ->name('products');

    Route::get('/categorias', Categories::class)
        ->middleware('permission:manage categories')
        ->name('categories');

    // ── INVENTARIO ─────────────────────────────────────────
    Route::get('/inventario', Inventory::class)
        ->middleware('permission:manage inventory')
        ->name('inventory');

    Route::get('/zonas-delivery', DeliveryZones::class)
        ->middleware('permission:manage delivery zones')
        ->name('delivery-zones');

    Route::get('/metodos-pago', PaymentMethods::class)
        ->middleware('permission:manage payment methods')
        ->name('payment-methods');

    Route::get('/roles-permisos', RolesPermissions::class)
        ->middleware('permission:manage users')
        ->name('roles');

    Route::get('/configuracion', \App\Livewire\Admin\Settings::class)
        ->middleware('permission:manage users')
        ->name('settings');
});

// ============================================
// PANTALLA TV
// ============================================
Route::get('/pedidos-tv', Pedidostv::class)
    ->middleware(['auth', 'permission:view pedidostv'])
    ->name('pedidos.tv');

// ============================================
// LOGOUT
// ============================================
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->middleware('auth')->name('logout');

// ============================================
// IMAGEN DE PRODUCTO
// ============================================
Route::get('/product/image/{hash}', function ($hash) {
    $product = Product::where('image_hash', $hash)->firstOrFail();

    if (!$product->image || !Storage::disk('public')->exists($product->image)) {
        abort(404);
    }

    return response()->file(Storage::disk('public')->path($product->image), [
        'Content-Type'  => 'image/webp',
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->name('product.image');