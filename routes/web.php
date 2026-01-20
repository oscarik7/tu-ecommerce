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
Route::get('/pedidos-tv', Pedidostv::class)->name('pedidos.tv');

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
    Route::get('/pos', Pos::class)->name('pos');
    
    // ✅ Ruta de impresión de tickets
    Route::get('/orders/{order}/print', [PrintController::class, 'showTicket'])->name('orders.print');
});

// ============================================
// RUTAS COMPARTIDAS (Admin y Trabajador/Worker)
// ============================================
Route::middleware(['auth', 'role:admin|worker'])->prefix('admin')->name('admin.')->group(function () {
    // El trabajador solo puede ver estas dos vistas
    Route::get('/pos', Pos::class)->name('pos');
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

Route::get('/product/image/{hash}', function ($hash) {
    // Buscar producto por hash
    $product = Product::where('image_hash', $hash)->firstOrFail();
    
    // Verificar que la imagen existe
    if (!$product->image || !Storage::disk('public')->exists($product->image)) {
        abort(404, 'Imagen no encontrada');
    }
    
    // Obtener el path completo
    $path = Storage::disk('public')->path($product->image);
    
    // Servir la imagen con headers apropiados
    return response()->file($path, [
        'Content-Type' => 'image/webp',
        'Cache-Control' => 'public, max-age=31536000', // Cache por 1 año
    ]);
})->name('product.image');