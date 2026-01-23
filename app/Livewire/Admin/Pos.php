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

    // Cliente
    public $customerSearch = '';
    public $selectedCustomer = null;
    public $customerName = '';
    public $customerPhone = '';
    
    // Tipo de venta
    public $saleType = 'counter';

    // Búsqueda de productos
    public $productSearch = '';
    public $selectedCategory = '';

    // Pago
    public $paymentMethodId = '';
    public $showPaymentModal = false;

    // Ticket
    public $showTicketModal = false;
    public $lastOrder = null;

    // Modal para productos por peso
    public $showWeightModal = false;
    public $selectedWeightProduct = null;
    public $weightInput = '';
    public $amountInput = ''; // 🆕 Input por monto (Gs)
    public $weightInputMode = 'weight'; // 'weight' o 'amount'

    public function mount()
    {
        $this->saleType = 'counter';
        $this->updateCartTotal();
    }

    public function setSaleType($type)
    {
        $this->saleType = $type;
        
        if ($type === 'counter') {
            $this->clearCustomer();
        }
    }

    /**
     * Abrir modal para ingresar peso/monto
     */
    public function openWeightModal($productId)
    {
        $product = Product::findOrFail($productId);
        
        // Verificar que el producto se pueda vender por peso
        if (!$product->can_sell_by_weight) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Este producto no se puede vender por peso.'
            ]);
            return;
        }

        $this->selectedWeightProduct = $product;
        $this->weightInput = '';
        $this->amountInput = '';
        $this->weightInputMode = 'amount'; // Por defecto empezamos con monto
        $this->showWeightModal = true;
    }

    /**
     * Cerrar modal de peso
     */
    public function closeWeightModal()
    {
        $this->showWeightModal = false;
        $this->selectedWeightProduct = null;
        $this->weightInput = '';
        $this->amountInput = '';
        $this->weightInputMode = 'amount';
    }

    /**
     * Cambiar modo de input (peso o monto)
     */
    public function setWeightInputMode($mode)
    {
        $this->weightInputMode = $mode;
        $this->weightInput = '';
        $this->amountInput = '';
    }

    /**
     * Calcular peso desde monto
     */
    public function getCalculatedWeightProperty()
    {
        if (!$this->selectedWeightProduct || !$this->amountInput) {
            return 0;
        }

        $amount = floatval(str_replace(['.', ','], ['', '.'], $this->amountInput));
        $pricePerKg = $this->selectedWeightProduct->price_per_kg;

        if ($pricePerKg <= 0) {
            return 0;
        }

        return $amount / $pricePerKg;
    }

    /**
     * Calcular monto desde peso
     */
    public function getCalculatedAmountProperty()
    {
        if (!$this->selectedWeightProduct || !$this->weightInput) {
            return 0;
        }

        $weight = floatval(str_replace(',', '.', $this->weightInput));
        $pricePerKg = $this->selectedWeightProduct->price_per_kg;

        return $pricePerKg * $weight;
    }

    /**
     * Agregar producto por peso al carrito
     */
    public function addWeightToCart()
    {
        if (!$this->selectedWeightProduct) {
            return;
        }

        $pricePerKg = $this->selectedWeightProduct->price_per_kg;
        $weight = 0;
        $totalPrice = 0;

        if ($this->weightInputMode === 'amount') {
            // Calcular desde monto
            $amount = floatval(str_replace(['.', ','], ['', '.'], $this->amountInput));
            
            if ($amount <= 0) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'message' => 'Ingrese un monto válido.'
                ]);
                return;
            }

            $weight = $amount / $pricePerKg;
            $totalPrice = $amount;
        } else {
            // Calcular desde peso
            $weight = floatval(str_replace(',', '.', $this->weightInput));
            
            if ($weight <= 0 || $weight > 50) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'message' => 'Peso inválido. Debe ser entre 0 y 50 kg.'
                ]);
                return;
            }

            $totalPrice = $pricePerKg * $weight;
        }

        // Redondear peso a 3 decimales
        $weight = round($weight, 3);
        
        // Redondear precio total sin decimales (guaraníes)
        $totalPrice = round($totalPrice, 0);

        // Agregar al carrito con key único
        $cartKey = 'weight_' . $this->selectedWeightProduct->id . '_' . time();

        $this->cart[$cartKey] = [
            'type' => 'weight',
            'product_id' => $this->selectedWeightProduct->id,
            'product_name' => $this->selectedWeightProduct->name,
            'weight' => $weight,
            'price_per_kg' => $pricePerKg,
            'price' => $totalPrice,
            'quantity' => 1,
            'image' => $this->selectedWeightProduct->image,
        ];

        $this->updateCartTotal();
        $this->closeWeightModal();

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => '✓ ' . number_format($weight, 3, ',', '.') . ' kg - ' . number_format($totalPrice, 0, ',', '.') . ' Gs'
        ]);
    }

    /**
     * Agregar producto unitario (con variantes) al carrito
     */
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
                    'type' => 'unit',
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
            
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => '✓ ' . $variant->product->name . ' ' . $variant->volume . 'ml'
            ]);
            
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

        // No se puede modificar cantidad de productos por peso
        if ($this->cart[$cartKey]['type'] === 'weight') {
            $this->dispatch('show-notification', [
                'type' => 'info',
                'message' => 'Para cambiar el peso, elimine y agregue nuevamente.'
            ]);
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
            if ($item['type'] === 'weight') {
                return $item['price'];
            }
            return $item['price'] * $item['quantity'];
        });
    }

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

            $customerData = $this->resolveCustomerData();

            $order = Order::create([
                'user_id' => $customerData['user_id'],
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

            // Crear items según tipo
            foreach ($this->cart as $item) {
                if ($item['type'] === 'weight') {
                    // Item por peso - pasar subtotal precalculado del carrito
                    OrderItem::createWeightItem([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'weight' => $item['weight'],
                        'price_per_kg' => $item['price_per_kg'],
                        'subtotal' => $item['price'], // El precio ya calculado (monto exacto del cliente)
                    ]);
                } else {
                    // Item unitario
                    $variant = ProductVariant::lockForUpdate()->find($item['variant_id']);
                    
                    if (!$variant || $variant->stock < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para {$item['product_name']}");
                    }

                    OrderItem::createUnitItem([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['variant_id'],
                        'product_name' => $item['product_name'],
                        'volume' => $item['volume'],
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                    ]);

                    $variant->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();

            $this->lastOrder = Order::with(['items', 'paymentMethod'])->find($order->id);
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

    private function resolveCustomerData(): array
    {
        if ($this->selectedCustomer) {
            return [
                'user_id' => $this->selectedCustomer->id,
                'name' => mb_strtoupper($this->selectedCustomer->name),
                'phone' => $this->selectedCustomer->phone ?? '',
                'email' => $this->selectedCustomer->email ?? '',
            ];
        }

        if ($this->saleType === 'counter' && empty($this->customerName)) {
            return [
                'user_id' => null,
                'name' => 'CONSUMIDOR FINAL',
                'phone' => '',
                'email' => '',
            ];
        }

        $existingUser = null;
        if (!empty($this->customerPhone)) {
            $existingUser = User::where('phone', $this->customerPhone)->first();
        }

        return [
            'user_id' => $existingUser?->id,
            'name' => mb_strtoupper($this->customerName ?: 'CONSUMIDOR FINAL'),
            'phone' => $this->customerPhone ?: '',
            'email' => $existingUser?->email ?? '',
        ];
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'POS';
        $date = date('Ymd');
        
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
        // Para POS, cargar todos los productos activos (incluyendo los de solo peso)
        $products = Product::query()
            ->forPos() // Scope que incluye todos los tipos
            ->when($this->productSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->productSearch . '%');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->with(['variants' => function ($query) {
                $query->where('is_active', true)->where('stock', '>', 0);
            }])
            ->orderBy('name')
            ->paginate(12);

        $categories = Category::where('is_active', true)->get();

        $customers = collect();
        if ($this->customerSearch && strlen($this->customerSearch) >= 2) {
            $query = User::where(function ($q) {
                    $q->where('name', 'like', '%' . $this->customerSearch . '%')
                      ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
                })
                ->where('is_active', true)
                ->limit(5);
            
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