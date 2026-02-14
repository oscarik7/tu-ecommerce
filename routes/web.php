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
use App\Livewire\Admin\Inventory;
use App\Livewire\Admin\CashRegisters;
use App\Livewire\Admin\Expenses;
use App\Livewire\Admin\Employees;
use App\Livewire\Admin\Reports;
use App\Livewire\Admin\Customizations;
use App\Livewire\Pedidostv;
use App\Http\Controllers\Admin\PrintController;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Customer\OrderConfirmation;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;

// ============================================
// RUTAS PÚBLICAS
// ============================================
Route::get('/', Home::class)->name('home');
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/producto/{slug}', ProductDetail::class)->name('product.detail');
Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
// ============================================
// CLIENTES
// ============================================
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/carrito', Cart::class)->name('cart');
    Route::get('/checkout', Checkout::class)->name('checkout');
    Route::get('/mis-pedidos', MyOrders::class)->name('my-orders');
    Route::get('/order-confirmation/{orderId}', OrderConfirmation::class)
    ->name('order-confirmation');
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

    // u2500u2500 CAJA u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500
    Route::get('/caja', CashRegisters::class)
        ->middleware('permission:manage cash registers')
        ->name('cash-registers');

    // u2500u2500 EGRESOS u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500u2500
    Route::get('/egresos', Expenses::class)
        ->middleware('permission:manage expenses')
        ->name('expenses');

    Route::get('/empleados', Employees::class)
        ->middleware('permission:manage employees')
        ->name('employees');

    Route::get('/reportes', Reports::class)
        ->middleware('permission:view reports')
        ->name('reports');

    Route::get('/complementos', Customizations::class)
        ->middleware('permission:manage customizations')
        ->name('customizations');

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

    Route::get('users', \App\Livewire\Admin\Users::class)
    ->middleware('permission:manage users')
    ->name('users');
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
