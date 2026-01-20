<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Pos extends Component
{
    use WithPagination;

    // Carrito
    public $cart = [];
    public $cartTotal = 0;

    // Cliente - TODOS OPCIONALES para POS
    public $customerSearch = '';
    public $selectedCustomer = null;
    public $customerName = '';
    public $customerPhone = '';
    
    // Tipo de venta
    public $saleType = 'counter'; // 'counter' = mostrador rápido, 'customer' = con cliente

    // Búsqueda de productos
    public $productSearch = '';
    public $selectedCategory = '';

    // Pago
    public $paymentMethodId = '';
    public $showPaymentModal = false;

    // Ticket
    public $showTicketModal = false;
    public $lastOrder = null;

    public function mount()
    {
        $this->saleType = 'counter';
        $this->updateCartTotal();
    }

    /**
     * Cambiar tipo de venta
     */
    public function setSaleType($type)
    {
        $this->saleType = $type;
        
        if ($type === 'counter') {
            $this->clearCustomer();
        }
    }

    public function addToCart($variantId)
    {
        try {
            $variant = ProductVariant::with('product')->findOrFail($variantId);

            if ($variant->stock <= 0) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'message' => 'Producto sin stock disponible.'
                ]);
                return;
            }

            $cartKey = 'variant_' . $variantId;

            if (isset($this->cart[$cartKey])) {
                if ($this->cart[$cartKey]['quantity'] >= $variant->stock) {
                    $this->dispatch('show-notification', [
                        'type' => 'error',
                        'message' => 'No hay suficiente stock disponible.'
                    ]);
                    return;
                }
                $this->cart[$cartKey]['quantity']++;
            } else {
                $this->cart[$cartKey] = [
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'product_name' => $variant->product->name,
                    'volume' => $variant->volume,
                    'price' => $variant->price,
                    'quantity' => 1,
                    'stock' => $variant->stock,
                    'image' => $variant->product->image,
                ];
            }

            $this->updateCartTotal();
            
        } catch (\Exception $e) {
            Log::error('Error al agregar al carrito: ' . $e->getMessage());
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Error al agregar producto.'
            ]);
        }
    }

    public function updateQuantity($cartKey, $action)
    {
        if (!isset($this->cart[$cartKey])) {
            return;
        }

        if ($action === 'increment') {
            if ($this->cart[$cartKey]['quantity'] >= $this->cart[$cartKey]['stock']) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'message' => 'Stock insuficiente.'
                ]);
                return;
            }
            $this->cart[$cartKey]['quantity']++;
        } elseif ($action === 'decrement') {
            if ($this->cart[$cartKey]['quantity'] > 1) {
                $this->cart[$cartKey]['quantity']--;
            } else {
                $this->removeFromCart($cartKey);
                return;
            }
        }

        $this->updateCartTotal();
    }

    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
        $this->updateCartTotal();
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->updateCartTotal();
    }

    private function updateCartTotal()
    {
        $this->cartTotal = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    /**
     * Seleccionar cliente existente
     */
    public function selectCustomer($userId)
    {
        $user = User::findOrFail($userId);
        $this->selectedCustomer = $user;
        $this->customerName = $user->name;
        $this->customerPhone = $user->phone ?? '';
        $this->customerSearch = '';
        $this->saleType = 'customer';
    }

    public function clearCustomer()
    {
        $this->selectedCustomer = null;
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerSearch = '';
    }

    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'El carrito está vacío.'
            ]);
            return;
        }

        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentMethodId = '';
    }

    /**
     * PROCESAR PAGO - SIN CREAR USUARIOS
     * 
     * - Venta mostrador: user_id = NULL, nombre = "Consumidor Final"
     * - Venta con cliente: user_id del cliente seleccionado
     * - Venta con datos manuales: user_id = NULL, guarda nombre/teléfono
     */
    public function processPayment()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'El carrito está vacío.'
            ]);
            return;
        }

        if (empty($this->paymentMethodId)) {
            $this->addError('paymentMethodId', 'Seleccione un método de pago.');
            return;
        }

        try {
            DB::beginTransaction();

            // Resolver datos del cliente SIN CREAR USUARIO
            $customerData = $this->resolveCustomerData();

            // Crear orden
            $order = Order::create([
                'user_id' => $customerData['user_id'], // Puede ser NULL
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $customerData['name'],
                'customer_phone' => $customerData['phone'],
                'customer_email' => $customerData['email'],
                'customer_address' => null,
                'customer_city' => null,
                'delivery_type' => 'pickup',
                'delivery_zone_id' => null,
                'payment_method_id' => $this->paymentMethodId,
                'subtotal' => $this->cartTotal,
                'delivery_cost' => 0,
                'total' => $this->cartTotal,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'notes' => $this->saleType === 'counter' ? 'Venta mostrador' : null,
                'source' => 'pos',
                'confirmed_at' => now(),
                'delivered_at' => now(),
            ]);

            // Crear items y actualizar stock
            foreach ($this->cart as $item) {
                $variant = ProductVariant::lockForUpdate()->find($item['variant_id']);
                
                if (!$variant || $variant->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para {$item['product_name']}");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['product_name'],
                    'volume' => $item['volume'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                $variant->decrement('stock', $item['quantity']);
            }

            DB::commit();

            // Guardar orden para ticket
            $this->lastOrder = Order::with(['items', 'paymentMethod'])->find($order->id);

            // Limpiar
            $this->resetAfterSale();

            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => '✓ Venta #' . $order->order_number
            ]);

            $this->showPaymentModal = false;
            $this->showTicketModal = true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error en POS: ' . $e->getMessage());
            
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Resolver datos del cliente SIN CREAR USUARIOS
     * 
     * El nombre SIEMPRE se guarda en customer_name para el ticket
     * user_id es opcional (solo si hay cliente registrado)
     * Los nombres se guardan en MAYÚSCULAS
     * 
     * @return array
     */
    private function resolveCustomerData(): array
    {
        // CASO 1: Cliente existente seleccionado
        if ($this->selectedCustomer) {
            return [
                'user_id' => $this->selectedCustomer->id,
                'name' => mb_strtoupper($this->selectedCustomer->name),
                'phone' => $this->selectedCustomer->phone ?? '',
                'email' => $this->selectedCustomer->email ?? '',
            ];
        }

        // CASO 2: Venta de mostrador SIN nombre ingresado
        if ($this->saleType === 'counter' && empty($this->customerName)) {
            return [
                'user_id' => null,
                'name' => 'CONSUMIDOR FINAL',
                'phone' => '',
                'email' => '',
            ];
        }

        // CASO 3: Venta con nombre ingresado (mostrador o con cliente)
        // Buscar si existe un usuario con ese teléfono (para vincular, no crear)
        $existingUser = null;
        if (!empty($this->customerPhone)) {
            $existingUser = User::where('phone', $this->customerPhone)->first();
        }

        return [
            'user_id' => $existingUser?->id, // NULL si no existe, ID si existe
            'name' => mb_strtoupper($this->customerName ?: 'CONSUMIDOR FINAL'), // En mayúsculas
            'phone' => $this->customerPhone ?: '',
            'email' => $existingUser?->email ?? '',
        ];
    }

    /**
     * Generar número de orden único
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'POS';
        $date = date('Ymd');
        
        // Contador del día
        $todayCount = Order::whereDate('created_at', today())
            ->where('order_number', 'like', "POS-{$date}%")
            ->count() + 1;
        
        return "{$prefix}-{$date}-" . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }

    private function resetAfterSale()
    {
        $this->cart = [];
        $this->cartTotal = 0;
        $this->clearCustomer();
        $this->paymentMethodId = '';
        $this->saleType = 'counter';
    }

    public function closeTicketModal()
    {
        $this->showTicketModal = false;
        $this->lastOrder = null;
    }

    /**
     * Venta rápida - Un clic para procesar
     */
    public function quickSale($paymentMethodId)
    {
        if (empty($this->cart)) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Carrito vacío'
            ]);
            return;
        }

        $this->paymentMethodId = $paymentMethodId;
        $this->processPayment();
    }

    public function render()
    {
        $products = Product::with(['variants' => function ($query) {
            $query->where('is_active', true)->where('stock', '>', 0);
        }])
            ->where('is_active', true)
            ->when($this->productSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->productSearch . '%');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->orderBy('name')
            ->paginate(12);

        $categories = Category::where('is_active', true)->get();

        // Buscar clientes existentes (solo si hay búsqueda)
        $customers = collect();
        if ($this->customerSearch && strlen($this->customerSearch) >= 2) {
            $query = User::where(function ($q) {
                    $q->where('name', 'like', '%' . $this->customerSearch . '%')
                      ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
                })
                ->where('is_active', true)
                ->limit(5);
            
            // Si tiene Spatie Permission, filtrar por rol
            if (method_exists(User::class, 'role')) {
                $query->role('customer');
            }
            
            $customers = $query->get();
        }

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('livewire.admin.pos', [
            'products' => $products,
            'categories' => $categories,
            'customers' => $customers,
            'paymentMethods' => $paymentMethods,
        ])->layout('components.layouts.admin', ['title' => 'Punto de Venta']);
    }
}