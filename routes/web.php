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

// ============================================
// RUTAS PÚBLICAS (No requieren autenticación)
// ============================================
Route::get('/', Home::class)->name('home');
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/producto/{slug}', ProductDetail::class)->name('product.detail');

// ============================================
// RUTAS PARA CLIENTES (Requieren autenticación y rol customer)
// ============================================
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/carrito', Cart::class)->name('cart');
    Route::get('/checkout', Checkout::class)->name('checkout');
    Route::get('/mis-pedidos', MyOrders::class)->name('my-orders');
});

// ============================================
// RUTAS PARA ADMINISTRADORES (Requieren autenticación y rol admin)
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/productos', Products::class)->name('products');
    Route::get('/categorias', Categories::class)->name('categories');
    Route::get('/pedidos', Orders::class)->name('orders');
    Route::get('/zonas-delivery', DeliveryZones::class)->name('delivery-zones');
    Route::get('/metodos-pago', PaymentMethods::class)->name('payment-methods');
});

// ============================================
// LOGOUT (Disponible para usuarios autenticados)
// ============================================
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->middleware('auth')->name('logout');