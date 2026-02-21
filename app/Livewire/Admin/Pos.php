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
use App\Models\CustomizationGroup;
use App\Models\OrderItemCustomization;
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

    // Calculadora de vuelto
    public bool   $showChangeCalculator = false;
    public        $amountReceived       = '';
    public string $changeCalculatorCurrency = 'PYG'; // 'PYG' o 'BRL'

    // Ticket (solo ID del último pedido)
    public $showTicketModal   = false;
    public ?int $lastOrderId  = null;

    // Modal complementos POS
    public bool  $showCustomizationsModal  = false;
    public ?int  $pendingVariantId         = null;   // variante esperando confirmación
    public array $customizationGroups      = [];     // grupos cargados
    public array $selectedCustomizations   = [];     // [group_id => [option_id, ...]]

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

            if (!$variant->hasStock(1)) {
                $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Sin stock disponible.']);
                return;
            }

            // Verificar si el producto tiene grupos de complementos activos
            $groups = CustomizationGroup::whereHas('products', fn($q) => $q->where('product_id', $variant->product_id))
                ->where('is_active', true)
                ->with(['options' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get()
                ->map(function($group) {
                    $arr = $group->toArray();
                    // CORRECCIÓN: Determinar si es múltiple basándose en max_selections
                    // Si max_selections es > 1 o null (sin límite), entonces permite selección múltiple
                    $arr['is_multiple'] = ($group->max_selections ?? 99) > 1;
                    return $arr;
                })
                ->toArray();

            if (!empty($groups)) {
                // Tiene complementos → abrir modal antes de agregar
                $this->pendingVariantId        = $variantId;
                $this->customizationGroups     = $groups;
                $this->selectedCustomizations  = [];
                $this->showCustomizationsModal = true;
                return;
            }

            // Sin complementos → agregar directo
            $this->doAddToCart($variantId, []);

        } catch (\Exception $e) {
            Log::error('POS addToCart: ' . $e->getMessage());
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Error al agregar producto.']);
        }
    }
    

    // ==========================================
    // MODAL DE COMPLEMENTOS POS
    // ==========================================

    public function toggleCustomization(int $groupId, int $optionId, bool $isMultiple): void
    {
        $current = $this->selectedCustomizations[$groupId] ?? [];

        if ($isMultiple) {
            if (in_array($optionId, $current)) {
                $this->selectedCustomizations[$groupId] = array_values(array_diff($current, [$optionId]));
            } else {
                // Respetar max_selections
                $group = collect($this->customizationGroups)->firstWhere('id', $groupId);
                $max   = $group['max_selections'] ?? null;
                if ($max && count($current) >= $max) return;
                $this->selectedCustomizations[$groupId][] = $optionId;
            }
        } else {
            // Radio: selección única (toggle si ya está seleccionado)
            $this->selectedCustomizations[$groupId] = in_array($optionId, $current) ? [] : [$optionId];
        }
    }

    public function confirmCustomizationsPOS(): void
    {
        if (!$this->pendingVariantId) return;

        // Validar grupos obligatorios
        foreach ($this->customizationGroups as $group) {
            if ($group['required'] ?? false) {
                $selected = $this->selectedCustomizations[$group['id']] ?? [];
                if (empty($selected)) {
                    $this->dispatch('show-notification', [
                        'type'    => 'error',
                        'message' => "Seleccioná al menos una opción en: {$group['name']}",
                    ]);
                    return;
                }
            }
        }

        // Construir snapshot de customizations
        $snapshot = [];
        foreach ($this->customizationGroups as $group) {
            foreach ($group['options'] as $option) {
                if (in_array($option['id'], $this->selectedCustomizations[$group['id']] ?? [])) {
                    $snapshot[] = [
                        'option_id'  => $option['id'],
                        'group_id'   => $group['id'],
                        'group_name' => $group['name'],
                        'name'       => $option['name'],
                        'price'      => (float) $option['price'],
                    ];
                }
            }
        }

        $this->doAddToCart($this->pendingVariantId, $snapshot);
        $this->closeCustomizationsModalPOS();
    }

    public function closeCustomizationsModalPOS(): void
    {
        $this->showCustomizationsModal = false;
        $this->pendingVariantId        = null;
        $this->customizationGroups     = [];
        $this->selectedCustomizations  = [];
    }

    // Método interno que realmente agrega al carrito (con o sin complementos)
    private function doAddToCart(int $variantId, array $customizations): void
    {
        $variant = ProductVariant::with('product', 'cupSize')->findOrFail($variantId);
        $price   = $variant->getPriceForChannel($this->getPriceChannel());
        $extras  = collect($customizations)->sum('price');

        // Cada combinación de complementos es un ítem separado en el carrito del POS
        // (así el operador puede vender 2 del mismo producto con complementos distintos)
        $cartKey = 'variant_' . $variantId . '_' . md5(json_encode(array_column($customizations, 'option_id')));

        if (isset($this->cart[$cartKey])) {
            $currentQty = $this->cart[$cartKey]['quantity'];
            if (!$variant->hasStock($currentQty + 1)) {
                $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Stock insuficiente.']);
                return;
            }
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'type'            => 'unit',
                'variant_id'      => $variant->id,
                'product_id'      => $variant->product_id,
                'product_name'    => $variant->product->name,
                'volume'          => $variant->volume,
                'price'           => $price,           // precio base
                'extras'          => $extras,           // suma de complementos
                'customizations'  => $customizations,  // snapshot completo
                'price_channel'   => $this->getPriceChannel(),
                'quantity'        => 1,
                'available_stock' => $variant->available_stock,
                'image'           => $variant->product->image,
            ];
        }

        $this->updateCartTotal();
        $totalUnitario = $price + $extras;
        $this->dispatch('show-notification', [
            'type'    => 'success',
            'message' => '✓ ' . $variant->product->name . ' ' . $variant->volume . 'ml — ' . number_format($totalUnitario, 0, ',', '.') . ' Gs',
        ]);
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
            if ($item['type'] === 'weight') return $item['price'];
            $extras = $item['extras'] ?? 0;
            return ($item['price'] + $extras) * $item['quantity'];
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
        
        // Detectar si es pago en efectivo para mostrar calculadora
        $method = PaymentMethod::find($paymentMethodId);
        
        if ($method && $method->type === 'cash') {
            $this->changeCalculatorCurrency = 'PYG';
            $this->amountReceived = '';
            $this->showChangeCalculator = true;
            $this->showPaymentModal = false;
        } elseif ($method && $method->type === 'foreign_currency') {
            $this->changeCalculatorCurrency = 'BRL';
            $this->amountReceived = '';
            $this->showChangeCalculator = true;
            $this->showPaymentModal = false;
        } else {
            // Otros métodos: procesar directo
            $this->processPayment();
        }
    }

    public function closeChangeCalculator(): void
    {
        $this->showChangeCalculator = false;
        $this->amountReceived = '';
        $this->paymentMethodId = '';
    }

    public function confirmPaymentWithChange(): void
    {
        $received = (float) str_replace(['.', ','], ['', '.'], $this->amountReceived);
        
        if ($received <= 0) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Ingrese el monto recibido.']);
            return;
        }

        $total = $this->cartTotal;
        
        // Convertir si es BRL
        if ($this->changeCalculatorCurrency === 'BRL') {
            $rate = \App\Models\StoreSetting::exchangeRateBrl();
            $receivedInGs = $received * $rate;
            
            if ($receivedInGs < $total) {
                $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Monto insuficiente.']);
                return;
            }
        } else {
            if ($received < $total) {
                $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Monto insuficiente.']);
                return;
            }
        }

        // Cerrar calculadora y procesar
        $this->showChangeCalculator = false;
        $this->processPayment();
    }

    public function getCalculatedChangeProperty(): array
    {
        $received = (float) str_replace(['.', ','], ['', '.'], $this->amountReceived ?: '0');
        $total = $this->cartTotal;
        $rate = \App\Models\StoreSetting::exchangeRateBrl();

        if ($this->changeCalculatorCurrency === 'BRL') {
            $receivedInGs = $received * $rate;
            $changeInGs = $receivedInGs - $total;
            $changeInBrl = $changeInGs / $rate;
            
            return [
                'received' => $received,
                'receivedInGs' => $receivedInGs,
                'changeInGs' => $changeInGs,
                'changeInBrl' => $changeInBrl,
                'rate' => $rate,
            ];
        } else {
            $changeInGs = $received - $total;
            
            return [
                'received' => $received,
                'receivedInGs' => $received,
                'changeInGs' => $changeInGs,
                'changeInBrl' => 0,
                'rate' => 1,
            ];
        }
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

        // Validar que la caja siga abierta realmente antes de guardar
        $register = CashRegister::where('id', $this->openRegisterId)
            ->where('status', 'open') // O la columna/valor que uses para cajas abiertas
            ->first() ?? CashRegister::getOpenRegister();

        if (!$register) {
            $this->dispatch('show-notification', [
                'type' => 'error', 
                'message' => 'La sesión de caja expiró o fue cerrada. Debe abrir una nueva.'
            ]);
            return;
        }
        $this->openRegisterId = $register->id;

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
                'subtotal'          => $this->cartTotal, // cartTotal ya incluye extras
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

                    $customizations      = $item['customizations'] ?? [];
                    $extrasUnit          = $item['extras'] ?? 0;
                    $itemSubtotal        = ($item['price'] + $extrasUnit) * $item['quantity'];

                    $orderItem = OrderItem::createUnitItem([
                        'order_id'                => $order->id,
                        'product_id'              => $item['product_id'],
                        'product_variant_id'      => $item['variant_id'],
                        'product_name'            => $item['product_name'],
                        'volume'                  => $item['volume'],
                        'price'                   => $item['price'],
                        'quantity'                => $item['quantity'],
                        'subtotal'                => $itemSubtotal,
                        'customizations_subtotal' => $extrasUnit * $item['quantity'],
                        'price_channel'           => $item['price_channel'] ?? 'pos',
                    ]);

                    // Guardar detalle de complementos
                    foreach ($customizations as $c) {
                        if (class_exists(OrderItemCustomization::class)) {
                            OrderItemCustomization::create([
                                'order_item_id'           => $orderItem->id,
                                'customization_option_id' => $c['option_id'] ?? null,
                                'quantity'                => $item['quantity'],
                                'price'                   => $c['price'],
                                'option_name'             => $c['name'],
                            ]);
                        }
                    }

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
            'products'              => $products,
            'categories'            => $categories,
            'customers'             => $customers,
            'paymentMethods'        => $paymentMethods,
            'openRegister'          => $openRegister,
            'priceChannel'          => $channel,
            'lastOrder'             => $lastOrder,
            'selectedWeightProduct' => $selectedWeightProduct,
            'selectedCustomer'      => $selectedCustomer,
            'posCustomizationGroups'=> collect($this->customizationGroups),
            'calculatedChange'      => $this->calculatedChange,
        ])->layout('components.layouts.admin', ['title' => 'Punto de Venta']);
    }
}