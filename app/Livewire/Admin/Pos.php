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
    public $paymentMethodId;
    public bool $showSplitAmountModal = false;

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

    // Pagos divididos
    public bool $useSplitPayment = false;
    public array $splitPayments = [];
    public ?int $pendingSplitMethodId = null;

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
        
        // SI HAY PAGO DIVIDIDO ACTIVO
        if ($this->useSplitPayment) {
            $this->addSplitPayment($paymentMethodId);
            return; // ← IMPORTANTE: salir aquí
        }
        
        // PAGO ÚNICO NORMAL (solo si NO es dividido)
        $this->paymentMethodId = $paymentMethodId;
        
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
            $this->processPayment();
        }
    }

    public function addSplitPayment(int $methodId): void
    {
        $method = PaymentMethod::find($methodId);
        if (!$method) return;
        
        $remaining = $this->getRemainingAmount();
        
        if ($remaining <= 0) {
            $this->dispatch('show-notification', [
                'type' => 'error', 
                'message' => 'Ya se cubrió el total.'
            ]);
            return;
        }
        
        // SIEMPRE abrir calculadora/modal para TODOS los métodos
        if ($method->type === 'cash' || $method->type === 'foreign_currency') {
            // Efectivo: abrir calculadora
            $this->pendingSplitMethodId = $methodId;
            $this->changeCalculatorCurrency = $method->type === 'cash' ? 'PYG' : 'BRL';
            $this->amountReceived = '';
            $this->showChangeCalculator = true;
            $this->showPaymentModal = false;
        } else {
            // Tarjeta/Transferencia/Otros: abrir modal simple para ingresar monto
            $this->pendingSplitMethodId = $methodId;
            $this->amountReceived = '';
            $this->showSplitAmountModal = true; // ← Nuevo modal
            $this->showPaymentModal = false;
        }
    }

    public function toggleSplitPayment(): void
    {
        $this->useSplitPayment = !$this->useSplitPayment;
        
        if (!$this->useSplitPayment) {
            $this->splitPayments = [];
        }
    }


    public function confirmSplitPaymentAmount(float $amount, string $currency = 'PYG'): void
    {
        if (!$this->pendingSplitMethodId) return;
        
        $remaining = $this->getRemainingAmount();
        
        // Convertir a Gs si es BRL
        $amountInGs = $currency === 'BRL' 
            ? $amount * \App\Models\StoreSetting::exchangeRateBrl()
            : $amount;
        
        if ($amountInGs > $remaining) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'El monto excede lo que falta.']);
            return;
        }
        
        $this->splitPayments[] = [
            'method_id' => $this->pendingSplitMethodId,
            'amount'    => $amount,
            'currency'  => $currency,
            'amount_gs' => $amountInGs,
        ];
        
        $this->pendingSplitMethodId = null;
        $this->showSplitAmountModal = false;
        $this->showChangeCalculator = false;
        
        // Si ya se cubrió el total, procesar
        if ($this->getRemainingAmount() <= 0) {
            $this->processSplitPayment();
        }
    }

    public function removeSplitPayment(int $index): void
    {
        unset($this->splitPayments[$index]);
        $this->splitPayments = array_values($this->splitPayments);
    }

    public function getRemainingAmount(): float
    {
        $total = $this->cartTotal;
        $paid = collect($this->splitPayments)->sum('amount_gs');
        return $total - $paid;
    }

    public function getTotalSplitPaid(): float
    {
        return collect($this->splitPayments)->sum('amount_gs');
    }

    public function closeChangeCalculator(): void
    {
        $this->showChangeCalculator = false;
        $this->amountReceived = '';
        $this->paymentMethodId = '';
    }

    public function confirmPaymentWithChange(): void
    {
        \Log::info('=== CONFIRM PAYMENT WITH CHANGE ===');
        \Log::info('amountReceived RAW: ' . $this->amountReceived);
        \Log::info('useSplitPayment: ' . ($this->useSplitPayment ? 'true' : 'false'));
        \Log::info('pendingSplitMethodId: ' . $this->pendingSplitMethodId);
        \Log::info('changeCalculatorCurrency: ' . $this->changeCalculatorCurrency);
        
        if (!$this->amountReceived || floatval(str_replace(['.', ','], ['', '.'], $this->amountReceived)) <= 0) {
            \Log::warning('❌ Validación inicial falló');
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Ingrese un monto válido.']);
            return;
        }

        // Normalizar input
        $cleanInput = str_replace('.', '', $this->amountReceived ?: '0');
        $cleanInput = str_replace(',', '.', $cleanInput);
        $received = (float) $cleanInput;
        
        \Log::info('cleanInput: ' . $cleanInput);
        \Log::info('received (parsed): ' . $received);
        
        if ($received <= 0) {
            \Log::warning('❌ Received <= 0');
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Ingrese un monto válido.']);
            return;
        }
        
        // SI ES PAGO DIVIDIDO
        if ($this->useSplitPayment && $this->pendingSplitMethodId) {
            \Log::info('🔄 MODO PAGO DIVIDIDO');
            
            $remaining = $this->getRemainingAmount();
            $rate = \App\Models\StoreSetting::exchangeRateBrl();
            
            \Log::info('remaining: ' . $remaining);
            \Log::info('rate: ' . $rate);
            
            $amountInGs = $this->changeCalculatorCurrency === 'BRL' 
                ? round($received * $rate)
                : $received;
            
            \Log::info('amountInGs: ' . $amountInGs);
            
            $difference = $amountInGs - $remaining;
            \Log::info('difference: ' . $difference);
            
            // VALIDACIÓN 1: El monto debe ser positivo
            if ($amountInGs <= 0) {
                \Log::warning('❌ Monto <= 0');
                $this->dispatch('show-notification', [
                    'type' => 'error', 
                    'message' => 'Ingrese un monto válido'
                ]);
                return;
            }
            
            // VALIDACIÓN 2: No puede EXCEDER el remaining por más de 50 Gs (tolerancia)
            if ($difference > 50) {
                \Log::warning('❌ Excede por más de 50 Gs: ' . $difference);
                $this->dispatch('show-notification', [
                    'type' => 'error', 
                    'message' => 'El monto excede lo que falta por ' . number_format($difference, 0, ',', '.') . ' Gs'
                ]);
                return;
            }
            
            // AJUSTE AUTOMÁTICO:
            // - Si excede ligeramente (diferencia positiva ≤ 50 Gs): ajustar al remaining exacto
            // - Si falta poco (diferencia negativa ≥ -50 Gs): completar el remaining automáticamente
            if ($difference > 0 && $difference <= 50) {
                // Excede por poco → ajustar al monto exacto
                $finalAmount = $remaining;
                \Log::info('🔧 Ajuste automático: Excedía por ' . $difference . ' Gs, ajustado a ' . $finalAmount);
            } elseif ($difference < 0 && $difference >= -50) {
                // Falta poco → completar automáticamente
                $finalAmount = $remaining;
                \Log::info('🔧 Ajuste automático: Faltaban ' . abs($difference) . ' Gs, completado a ' . $finalAmount);
            } else {
                // Diferencia normal → usar el monto ingresado
                $finalAmount = $amountInGs;
                \Log::info('✅ finalAmount (sin ajuste): ' . $finalAmount);
            }
            
            // AGREGAR PAGO (ya sea parcial o completo)
            $this->splitPayments[] = [
                'method_id' => $this->pendingSplitMethodId,
                'amount'    => $received,
                'currency'  => $this->changeCalculatorCurrency,
                'amount_gs' => $finalAmount,
            ];
            
            \Log::info('✅ Pago agregado a splitPayments');
            \Log::info('Total pagos divididos: ' . count($this->splitPayments));
            
            $this->pendingSplitMethodId = null;
            $this->showChangeCalculator = false;
            $this->amountReceived = '';
            
            $newRemaining = $this->getRemainingAmount();
            
            if ($newRemaining <= 0) {
                // Ya se cubrió el total completo
                \Log::info('✅ Total cubierto, procesando venta...');
                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'message' => 'Total cubierto. Puede confirmar la venta.'
                ]);
            } else {
                // Aún falta por pagar
                \Log::info('📝 Falta por pagar: ' . $newRemaining . ' Gs');
                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'message' => 'Pago agregado: ' . number_format($finalAmount, 0, ',', '.') . ' Gs. Falta: ' . number_format($newRemaining, 0, ',', '.') . ' Gs'
                ]);
            }
            
            \Log::info('=== FIN CONFIRM PAYMENT WITH CHANGE ===');
            return;
        }
        
        // PAGO ÚNICO NORMAL
        \Log::info('💰 MODO PAGO ÚNICO');
        $calc = $this->calculatedChange;
        
        \Log::info('calculatedChange: ', $calc);
        
        // TOLERANCIA: aceptar hasta 10 Gs de diferencia
        if ($calc['changeInGs'] < -10) {
            \Log::warning('❌ Falta más de 10 Gs: ' . abs($calc['changeInGs']));
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Falta: ' . number_format(abs($calc['changeInGs']), 0, ',', '.') . ' Gs'
            ]);
            return;
        }
        
        \Log::info('✅ Procesando pago único...');
        $this->processPayment();
        \Log::info('=== FIN CONFIRM PAYMENT WITH CHANGE ===');
    }

    public function confirmSplitAmount(): void
    {
        \Log::info('=== CONFIRM SPLIT AMOUNT ===');
        \Log::info('pendingSplitMethodId: ' . $this->pendingSplitMethodId);
        
        if (!$this->pendingSplitMethodId) {
            \Log::warning('❌ No hay pendingSplitMethodId');
            return;
        }
        
        \Log::info('amountReceived RAW: ' . $this->amountReceived);
        
        // Normalizar input
        $cleanInput = str_replace('.', '', $this->amountReceived ?: '0');
        $cleanInput = str_replace(',', '.', $cleanInput);
        $received = (float) $cleanInput;
        
        \Log::info('cleanInput: ' . $cleanInput);
        \Log::info('received (parsed): ' . $received);
        
        if ($received <= 0) {
            \Log::warning('❌ Received <= 0');
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Ingrese un monto válido.']);
            return;
        }
        
        $remaining = $this->getRemainingAmount();
        \Log::info('remaining: ' . $remaining);
        
        $difference = $received - $remaining;
        \Log::info('difference: ' . $difference);
        
        // VALIDACIÓN: Solo validar que NO EXCEDA por más de 50 Gs
        if ($difference > 50) {
            \Log::warning('❌ Excede por más de 50 Gs: ' . $difference);
            $this->dispatch('show-notification', [
                'type' => 'error', 
                'message' => 'El monto excede lo que falta por ' . number_format($difference, 0, ',', '.') . ' Gs'
            ]);
            return;
        }
        
        // AJUSTE AUTOMÁTICO:
        if ($difference > 0 && $difference <= 50) {
            // Excede por poco → ajustar al monto exacto
            $finalAmount = $remaining;
            \Log::info('🔧 Ajuste automático: Excedía por ' . $difference . ' Gs, ajustado a ' . $finalAmount);
        } elseif ($difference < 0 && $difference >= -50) {
            // Falta poco → completar automáticamente
            $finalAmount = $remaining;
            \Log::info('🔧 Ajuste automático: Faltaban ' . abs($difference) . ' Gs, completado a ' . $finalAmount);
        } else {
            // Diferencia normal → usar el monto ingresado
            $finalAmount = $received;
            \Log::info('✅ finalAmount (sin ajuste): ' . $finalAmount);
        }
        
        $this->splitPayments[] = [
            'method_id' => $this->pendingSplitMethodId,
            'amount'    => $finalAmount,
            'currency'  => 'PYG',
            'amount_gs' => $finalAmount,
        ];
        
        \Log::info('✅ Pago agregado a splitPayments');
        \Log::info('Total pagos divididos: ' . count($this->splitPayments));
        
        $this->pendingSplitMethodId = null;
        $this->showSplitAmountModal = false;
        $this->amountReceived = '';
        
        $newRemaining = $this->getRemainingAmount();
        
        if ($newRemaining <= 0) {
            // Ya se cubrió el total completo
            \Log::info('✅ Total cubierto');
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Total cubierto. Puede confirmar la venta.'
            ]);
        } else {
            // Aún falta por pagar
            \Log::info('📝 Falta por pagar: ' . $newRemaining . ' Gs');
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Pago agregado: ' . number_format($finalAmount, 0, ',', '.') . ' Gs. Falta: ' . number_format($newRemaining, 0, ',', '.') . ' Gs'
            ]);
        }
        
        \Log::info('=== FIN CONFIRM SPLIT AMOUNT ===');
    }

    public function getCalculatedChangeProperty(): array
    {
        // Normalizar input
        $cleanInput = str_replace('.', '', $this->amountReceived ?: '0');
        $cleanInput = str_replace(',', '.', $cleanInput);
        $received = (float) $cleanInput;
        
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
        if ($this->useSplitPayment) {
            $this->processSplitPayment();
            return;
        }

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

    public function processSplitPayment(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Carrito vacío.']);
            return;
        }
        
        if (empty($this->splitPayments)) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Agregue al menos un método de pago.']);
            return;
        }
        
        if ($this->getRemainingAmount() > 0) {
            $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Falta cubrir ' . number_format($this->getRemainingAmount(), 0, ',', '.') . ' Gs']);
            return;
        }

        try {
            DB::beginTransaction();

            $register = $this->openRegisterId
                ? CashRegister::find($this->openRegisterId)
                : CashRegister::getOpenRegister();
            $this->openRegisterId = $register?->id;

            $customerData = $this->resolveCustomerData();

            // Crear orden SIN payment_method_id (es pago dividido)
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
                'payment_method_id' => null, // NULL porque es dividido
                'is_split_payment'  => true,
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

            if ($this->saleType === 'delivery_app') {
                $orderData['delivery_app_name']       = $this->deliveryAppName;
                $orderData['delivery_app_order_id']   = $this->deliveryAppOrderId ?: null;
                $orderData['delivery_app_commission'] = $this->deliveryAppCommission ?: null;
                $orderData['notes']                   = 'Pedido via ' . $this->deliveryAppName;
            } elseif ($this->saleType === 'counter') {
                $orderData['notes'] = 'Venta mostrador - Pago dividido';
            }

            $order = Order::create($orderData);

            // Crear ítems (igual que antes)
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

                    $customizations = $item['customizations'] ?? [];
                    $extrasUnit = $item['extras'] ?? 0;
                    $itemSubtotal = ($item['price'] + $extrasUnit) * $item['quantity'];

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

                    $variant->decrementStock($item['quantity']);
                }
            }

            // Crear los pagos
            foreach ($this->splitPayments as $payment) {
                \App\Models\OrderPayment::create([
                    'order_id'          => $order->id,
                    'payment_method_id' => $payment['method_id'],
                    'amount'            => $payment['amount_gs'],
                    'details'           => [
                        'original_amount'   => $payment['amount'],
                        'original_currency' => $payment['currency'],
                    ],
                ]);
            }

            DB::commit();

            $this->lastOrderId = $order->id;
            $this->resetAfterSale();

            $this->dispatch('show-notification', [
                'type'    => 'success',
                'message' => '✓ Venta #' . $order->order_number . ' (Pago dividido)',
            ]);
            $this->showPaymentModal = false;
            $this->showTicketModal  = true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error POS processSplitPayment: ' . $e->getMessage());
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
        $this->useSplitPayment = false;
        $this->splitPayments = [];
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
            'remainingAmount' => $this->getRemainingAmount(),
            'totalSplitPaid'  => $this->getTotalSplitPaid(),
        ])->layout('components.layouts.admin', ['title' => 'Punto de Venta']);
    }
}