<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\CashRegister;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Pos extends Component
{
    use WithPagination;

    // Carrito
    public $cart      = [];
    public $cartTotal = 0;

    // Cliente (solo ID, no el modelo)
    public $customerSearch    = '';
    public ?int $selectedCustomerId = null;
    public $customerName      = '';
    public $customerPhone     = '';

    // Tipo de venta: 'counter' | 'customer' | 'delivery_app'
    public $saleType = 'counter';

    // Pedidos Ya / app
    public $deliveryAppName       = 'Pedidos Ya';
    public $deliveryAppOrderId    = '';
    public $deliveryAppCommission = '';

    // Búsqueda de productos
    public $productSearch    = '';
    public $selectedCategory = '';

    // Pago
    public $paymentMethodId  = '';
    public $showPaymentModal = false;

    // Ticket (solo ID del último pedido)
    public $showTicketModal   = false;
    public ?int $lastOrderId  = null;

    // Modal peso (solo ID del producto)
    public $showWeightModal          = false;
    public ?int $selectedWeightProductId = null;
    public $weightInput              = '';
    public $amountInput              = '';
    public $weightInputMode          = 'amount';

    // Caja actual (solo ID)
    public ?int $openRegisterId = null;

    // ==========================================
    // MOUNT
    // ==========================================

    public function mount(): void
    {
        $this->saleType = 'counter';
        $register = CashRegister::getOpenRegister();
        $this->openRegisterId = $register?->id;
        $this->updateCartTotal();
    }

    // ==========================================
    // TIPO DE VENTA
    // ==========================================

    public function setSaleType(string $type): void
    {
        $this->saleType = $type;

        if ($type === 'counter') {
            $this->clearCustomer();
            $this->resetDeliveryApp();
        } elseif ($type === 'delivery_app') {
            $this->clearCustomer();
        }
    }

    private function resetDeliveryApp(): void
    {
        $this->deliveryAppName       = 'Pedidos Ya';
        $this->deliveryAppOrderId    = '';
        $this->deliveryAppCommission = '';
    }

    // ==========================================
    // CANAL DE PRECIO SEGÚN TIPO DE VENTA
    // ==========================================

    private function getPriceChannel(): string
    {
        return match($this->saleType) {
            'delivery_app' => 'delivery_app',
            default        => 'pos',
        };
    }

    // ==========================================
    // MODAL DE PESO
    // ==========================================

    public function openWeightModal(int $productId): void
    {
        $product = Product::findOrFail($productId);

        if (!$product->can_sell_by_weight) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Este producto no se vende por peso.']);
            return;
        }

        $this->selectedWeightProductId = $productId;
        $this->weightInput             = '';
        $this->amountInput             = '';
        $this->weightInputMode         = 'amount';
        $this->showWeightModal         = true;
    }

    public function closeWeightModal(): void
    {
        $this->showWeightModal         = false;
        $this->selectedWeightProductId = null;
        $this->weightInput             = '';
        $this->amountInput             = '';
        $this->weightInputMode         = 'amount';
    }

    public function setWeightInputMode(string $mode): void
    {
        $this->weightInputMode = $mode;
        $this->weightInput     = '';
        $this->amountInput     = '';
    }

    // ==========================================
    // PROPIEDADES CALCULADAS (peso modal)
    // ==========================================

    // Helper: resolver modelo del producto de peso
    private function getSelectedWeightProduct(): ?Product
    {
        if (!$this->selectedWeightProductId) return null;
        return Product::find($this->selectedWeightProductId);
    }

    public function getCalculatedWeightProperty(): float
    {
        $product = $this->getSelectedWeightProduct();
        if (!$product || !$this->amountInput) return 0;

        $amount     = floatval(str_replace(['.', ','], ['', '.'], $this->amountInput));
        $pricePerKg = $this->getPricePerKgForChannel();

        return $pricePerKg > 0 ? $amount / $pricePerKg : 0;
    }

    public function getCalculatedAmountProperty(): float
    {
        $product = $this->getSelectedWeightProduct();
        if (!$product || !$this->weightInput) return 0;

        $weight     = floatval(str_replace(',', '.', $this->weightInput));
        $pricePerKg = $this->getPricePerKgForChannel();

        return $pricePerKg * $weight;
    }

    private function getPricePerKgForChannel(): float
    {
        $product = $this->getSelectedWeightProduct();
        if (!$product) return 0;

        $channel = $this->getPriceChannel();

        if ($channel === 'delivery_app' && $product->price_per_kg_delivery_app) {
            return (float) $product->price_per_kg_delivery_app;
        }

        if ($channel === 'pos' && $product->price_per_kg_pos) {
            return (float) $product->price_per_kg_pos;
        }

        return (float) $product->price_per_kg;
    }

    // ==========================================
    // AGREGAR PESO AL CARRITO
    // ==========================================

    public function addWeightToCart(): void
    {
        $product = $this->getSelectedWeightProduct();
        if (!$product) return;

        $pricePerKg = $this->getPricePerKgForChannel();
        $weight     = 0;
        $totalPrice = 0;

        if ($this->weightInputMode === 'amount') {
            $amount = floatval(str_replace(['.', ','], ['', '.'], $this->amountInput));
            if ($amount <= 0) {
                $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Ingrese un monto válido.']);
                return;
            }
            $weight     = $amount / $pricePerKg;
            $totalPrice = $amount;
        } else {
            $weight = floatval(str_replace(',', '.', $this->weightInput));
            if ($weight <= 0 || $weight > 50) {
                $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Peso inválido (0 a 50 kg).']);
                return;
            }
            $totalPrice = $pricePerKg * $weight;
        }

        $weight     = round($weight, 3);
        $totalPrice = round($totalPrice);

        $cartKey = 'weight_' . $product->id . '_' . time();

        $this->cart[$cartKey] = [
            'type'         => 'weight',
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'weight'       => $weight,
            'price_per_kg' => $pricePerKg,
            'price'        => $totalPrice,
            'price_channel'=> $this->getPriceChannel(),
            'quantity'     => 1,
            'image'        => $product->image,
        ];

        $this->updateCartTotal();
        $this->closeWeightModal();
        $this->dispatch('show-notification', [
            'type'    => 'success',
            'message' => '✓ ' . number_format($weight, 3, ',', '.') . ' kg — ' . number_format($totalPrice, 0, ',', '.') . ' Gs',
        ]);
    }

    // ==========================================
    // AGREGAR VARIANTE (UNIDAD) AL CARRITO
    // ==========================================

    public function addToCart(int $variantId): void
    {
        try {
            $variant = ProductVariant::with('product', 'cupSize')->findOrFail($variantId);

            // Verificar stock: primero cup_size, fallback stock legacy
            if (!$variant->hasStock(1)) {
                $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Sin stock disponible.']);
                return;
            }

            // Precio según canal
            $price   = $variant->getPriceForChannel($this->getPriceChannel());
            $cartKey = 'variant_' . $variantId;

            if (isset($this->cart[$cartKey])) {
                // Verificar que hay stock para uno más
                $currentQty = $this->cart[$cartKey]['quantity'];
                if (!$variant->hasStock($currentQty + 1)) {
                    $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Stock insuficiente.']);
                    return;
                }
                $this->cart[$cartKey]['quantity']++;
            } else {
                $this->cart[$cartKey] = [
                    'type'          => 'unit',
                    'variant_id'    => $variant->id,
                    'product_id'    => $variant->product_id,
                    'product_name'  => $variant->product->name,
                    'volume'        => $variant->volume,
                    'price'         => $price,
                    'price_channel' => $this->getPriceChannel(),
                    'quantity'      => 1,
                    // Para validación de stock en UI (usa cup stock si existe)
                    'available_stock' => $variant->available_stock,
                    'image'         => $variant->product->image,
                ];
            }

            $this->updateCartTotal();
            $this->dispatch('show-notification', [
                'type'    => 'success',
                'message' => '✓ ' . $variant->product->name . ' ' . $variant->volume . 'ml — ' . number_format($price, 0, ',', '.') . ' Gs',
            ]);

        } catch (\Exception $e) {
            Log::error('POS addToCart: ' . $e->getMessage());
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Error al agregar producto.']);
        }
    }

    // ==========================================
    // CARRITO: CANTIDAD / ELIMINAR / VACIAR
    // ==========================================

    public function updateQuantity(string $cartKey, string $action): void
    {
        if (!isset($this->cart[$cartKey])) return;

        if ($this->cart[$cartKey]['type'] === 'weight') {
            $this->dispatch('show-notification', ['type' => 'info', 'message' => 'Para cambiar el peso, eliminá y agregá de nuevo.']);
            return;
        }

        if ($action === 'increment') {
            $qty       = $this->cart[$cartKey]['quantity'];
            $available = $this->cart[$cartKey]['available_stock'];

            if ($qty >= $available) {
                $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Stock insuficiente.']);
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

    public function removeFromCart(string $cartKey): void
    {
        unset($this->cart[$cartKey]);
        $this->updateCartTotal();
    }

    public function clearCart(): void
    {
        $this->cart      = [];
        $this->cartTotal = 0;
    }

    private function updateCartTotal(): void
    {
        $this->cartTotal = collect($this->cart)->sum(function ($item) {
            return $item['type'] === 'weight'
                ? $item['price']
                : $item['price'] * $item['quantity'];
        });
    }

    // ==========================================
    // CLIENTE
    // ==========================================

    public function selectCustomer(int $userId): void
    {
        $user                      = User::findOrFail($userId);
        $this->selectedCustomerId  = $userId;
        $this->customerName        = $user->name;
        $this->customerPhone       = $user->phone ?? '';
        $this->customerSearch      = '';
        $this->saleType            = 'customer';
    }

    public function clearCustomer(): void
    {
        $this->selectedCustomerId = null;
        $this->customerName       = '';
        $this->customerPhone      = '';
        $this->customerSearch     = '';
    }

    // ==========================================
    // PAGO
    // ==========================================

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'El carrito está vacío.']);
            return;
        }
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentMethodId  = '';
    }

    public function quickSale(int $paymentMethodId): void
    {
        if (empty($this->cart)) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Carrito vacío.']);
            return;
        }
        $this->paymentMethodId = $paymentMethodId;
        $this->processPayment();
    }

    // ==========================================
    // PROCESAR VENTA
    // ==========================================

    public function processPayment(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Carrito vacío.']);
            return;
        }
        if (empty($this->paymentMethodId)) {
            $this->addError('paymentMethodId', 'Seleccioná un método de pago.');
            return;
        }

        try {
            DB::beginTransaction();

            // Refrescar referencia a la caja abierta
            $register = $this->openRegisterId
                ? CashRegister::find($this->openRegisterId)
                : CashRegister::getOpenRegister();
            $this->openRegisterId = $register?->id;

            $customerData = $this->resolveCustomerData();

            // Datos base de la orden
            $orderData = [
                'user_id'           => $customerData['user_id'],
                'order_number'      => $this->generateOrderNumber(),
                'customer_name'     => $customerData['name'],
                'customer_phone'    => $customerData['phone'],
                'customer_email'    => $customerData['email'],
                'customer_address'  => null,
                'customer_city'     => null,
                'delivery_type'     => 'pickup',
                'delivery_zone_id'  => null,
                'payment_method_id' => $this->paymentMethodId,
                'subtotal'          => $this->cartTotal,
                'delivery_cost'     => 0,
                'total'             => $this->cartTotal,
                'status'            => 'delivered',
                'payment_status'    => 'paid',
                'source'            => $this->saleType === 'delivery_app' ? 'delivery_app' : 'pos',
                'cash_register_id'  => $register?->id,
                'confirmed_at'      => now(),
                'delivered_at'      => now(),
            ];

            // Datos extras si es pedido de app de delivery
            if ($this->saleType === 'delivery_app') {
                $orderData['delivery_app_name']       = $this->deliveryAppName;
                $orderData['delivery_app_order_id']   = $this->deliveryAppOrderId ?: null;
                $orderData['delivery_app_commission'] = $this->deliveryAppCommission ?: null;
                $orderData['notes']                   = 'Pedido via ' . $this->deliveryAppName;
            } elseif ($this->saleType === 'counter') {
                $orderData['notes'] = 'Venta mostrador';
            }

            $order = Order::create($orderData);

            // Crear ítems
            foreach ($this->cart as $item) {
                if ($item['type'] === 'weight') {
                    OrderItem::createWeightItem([
                        'order_id'      => $order->id,
                        'product_id'    => $item['product_id'],
                        'product_name'  => $item['product_name'],
                        'weight'        => $item['weight'],
                        'price_per_kg'  => $item['price_per_kg'],
                        'subtotal'      => $item['price'],
                        'price_channel' => $item['price_channel'] ?? 'pos',
                    ]);
                } else {
                    $variant = ProductVariant::with('cupSize')->lockForUpdate()->find($item['variant_id']);

                    if (!$variant || !$variant->hasStock($item['quantity'])) {
                        throw new \Exception("Stock insuficiente para {$item['product_name']}");
                    }

                    OrderItem::createUnitItem([
                        'order_id'            => $order->id,
                        'product_id'          => $item['product_id'],
                        'product_variant_id'  => $item['variant_id'],
                        'product_name'        => $item['product_name'],
                        'volume'              => $item['volume'],
                        'price'               => $item['price'],
                        'quantity'            => $item['quantity'],
                        'price_channel'       => $item['price_channel'] ?? 'pos',
                    ]);

                    // Descontar del stock compartido de vasitos (cup_size)
                    $variant->decrementStock($item['quantity']);
                }
            }

            DB::commit();

            $this->lastOrderId = $order->id;
            $this->resetAfterSale();

            $this->dispatch('show-notification', [
                'type'    => 'success',
                'message' => '✓ Venta #' . $order->order_number,
            ]);
            $this->showPaymentModal = false;
            $this->showTicketModal  = true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error POS processPayment: ' . $e->getMessage());
            $this->dispatch('show-notification', [
                'type'    => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    // ==========================================
    // HELPERS
    // ==========================================

    private function resolveCustomerData(): array
    {
        if ($this->selectedCustomerId) {
            $user = User::find($this->selectedCustomerId);
            if ($user) {
                return [
                    'user_id' => $user->id,
                    'name'    => mb_strtoupper($user->name),
                    'phone'   => $user->phone ?? '',
                    'email'   => $user->email ?? '',
                ];
            }
        }

        if ($this->saleType === 'counter' && empty($this->customerName)) {
            return ['user_id' => null, 'name' => 'CONSUMIDOR FINAL', 'phone' => '', 'email' => ''];
        }

        $existingUser = !empty($this->customerPhone)
            ? User::where('phone', $this->customerPhone)->first()
            : null;

        return [
            'user_id' => $existingUser?->id,
            'name'    => mb_strtoupper($this->customerName ?: 'CONSUMIDOR FINAL'),
            'phone'   => $this->customerPhone ?: '',
            'email'   => $existingUser?->email ?? '',
        ];
    }

    private function generateOrderNumber(): string
    {
        $prefix = match($this->saleType) {
            'delivery_app' => 'APP',
            default        => 'POS',
        };
        $date       = date('Ymd');
        $todayCount = Order::whereDate('created_at', today())
            ->where('order_number', 'like', "{$prefix}-{$date}%")
            ->count() + 1;

        return "{$prefix}-{$date}-" . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
    }

    private function resetAfterSale(): void
    {
        $this->cart            = [];
        $this->cartTotal       = 0;
        $this->paymentMethodId = '';
        $this->saleType        = 'counter';
        $this->clearCustomer();
        $this->resetDeliveryApp();
    }

    public function closeTicketModal(): void
    {
        $this->showTicketModal = false;
        $this->lastOrderId     = null;
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $channel = $this->getPriceChannel();

        $products = Product::query()
            ->forPos()
            ->when($this->productSearch, fn($q) =>
                $q->where('name', 'like', '%' . $this->productSearch . '%')
            )
            ->when($this->selectedCategory, fn($q) =>
                $q->where('category_id', $this->selectedCategory)
            )
            ->with(['variants' => fn($q) =>
                // Solo variantes activas con stock disponible en cup_size o stock legacy
                $q->where('is_active', true)
                  ->with('cupSize')
                  ->get()
                  ->filter(fn($v) => $v->hasStock(1))
            ])
            ->orderBy('name')
            ->paginate(12);

        $categories = Category::where('is_active', true)->get();

        $customers = collect();
        if ($this->customerSearch && strlen($this->customerSearch) >= 2) {
            $customers = User::where(function ($q) {
                    $q->where('name', 'like', '%' . $this->customerSearch . '%')
                      ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
                })
                ->where('is_active', true)
                ->role('customer')
                ->limit(5)
                ->get();
        }

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        $selectedCustomer = $this->selectedCustomerId
            ? User::find($this->selectedCustomerId)
            : null;

        // Resolver modelos frescos desde IDs (nunca desde propiedades Eloquent)
        $openRegister = $this->openRegisterId
            ? CashRegister::find($this->openRegisterId)
            : CashRegister::getOpenRegister();
        $this->openRegisterId = $openRegister?->id;

        $lastOrder = $this->lastOrderId
            ? Order::with(['items', 'paymentMethod'])->find($this->lastOrderId)
            : null;

        $selectedWeightProduct = $this->getSelectedWeightProduct();

        return view('livewire.admin.pos', [
            'products'             => $products,
            'categories'           => $categories,
            'customers'            => $customers,
            'paymentMethods'       => $paymentMethods,
            'openRegister'         => $openRegister,
            'priceChannel'         => $channel,
            'lastOrder'            => $lastOrder,
            'selectedWeightProduct'=> $selectedWeightProduct,
            'selectedCustomer'     => $selectedCustomer,
        ])->layout('components.layouts.admin', ['title' => 'Punto de Venta']);
    }
}
