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
use App\Livewire\Pedidostv;
use App\Http\Controllers\Admin\PrintController;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

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
// RUTAS DE ADMINISTRACIÓN (Requieren autenticación + permisos)
// ============================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard - requiere permiso 'view dashboard'
    Route::get('/dashboard', Dashboard::class)
        ->middleware('permission:view dashboard')
        ->name('dashboard');
    
    // POS - requiere permiso 'view pos'
    Route::get('/pos', Pos::class)
        ->middleware('permission:view pos')
        ->name('pos');
    
    // Pedidos - requiere permiso 'manage orders'
    Route::get('/pedidos', Orders::class)
        ->middleware('permission:manage orders')
        ->name('orders');
    
    // Imprimir ticket - requiere permiso 'manage orders'
    Route::get('/orders/{order}/print', [PrintController::class, 'showTicket'])
        ->middleware('permission:manage orders')
        ->name('orders.print');
    
    // Productos - requiere permiso 'manage products'
    Route::get('/productos', Products::class)
        ->middleware('permission:manage products')
        ->name('products');
    
    // Categorías - requiere permiso 'manage categories'
    Route::get('/categorias', Categories::class)
        ->middleware('permission:manage categories')
        ->name('categories');
    
    // Zonas de Delivery - requiere permiso 'manage delivery zones'
    Route::get('/zonas-delivery', DeliveryZones::class)
        ->middleware('permission:manage delivery zones')
        ->name('delivery-zones');
    
    // Métodos de Pago - requiere permiso 'manage payment methods'
    Route::get('/metodos-pago', PaymentMethods::class)
        ->middleware('permission:manage payment methods')
        ->name('payment-methods');
    
    // Roles y Permisos - requiere permiso 'manage users'
    Route::get('/roles-permisos', RolesPermissions::class)
        ->middleware('permission:manage users')
        ->name('roles');
    
    // Configuración del Sistema - requiere permiso 'manage users' (solo admin)
    Route::get('/configuracion', \App\Livewire\Admin\Settings::class)
        ->middleware('permission:manage users')
        ->name('settings');
});

// ============================================
// PANTALLA TV (Requiere permiso 'view pedidostv')
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
// IMAGEN DE PRODUCTO (Pública)
// ============================================
Route::get('/product/image/{hash}', function ($hash) {
    $product = Product::where('image_hash', $hash)->firstOrFail();
    
    if (!$product->image || !Storage::disk('public')->exists($product->image)) {
        abort(404, 'Imagen no encontrada');
    }
    
    $path = Storage::disk('public')->path($product->image);
    
    return response()->file($path, [
        'Content-Type' => 'image/webp',
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->name('product.image');