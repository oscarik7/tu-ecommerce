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
use App\Models\StoreSetting;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Pos extends Component
{
    use WithPagination;

    // ──────────────────────────────────────────────────────────────────────────
    // CARRITO
    // ──────────────────────────────────────────────────────────────────────────
    public array $cart      = [];
    public float $cartTotal = 0;

    // ──────────────────────────────────────────────────────────────────────────
    // TIPO DE VENTA: 'counter' | 'customer' | 'delivery_app'
    // ──────────────────────────────────────────────────────────────────────────
    public string $saleType = 'counter';

    // ──────────────────────────────────────────────────────────────────────────
    // CLIENTE
    // ──────────────────────────────────────────────────────────────────────────
    public string  $customerSearch   = '';
    public ?int    $selectedCustomerId = null;
    public string  $customerName     = '';
    public string  $customerPhone    = '';

    // ──────────────────────────────────────────────────────────────────────────
    // DELIVERY APP
    // ──────────────────────────────────────────────────────────────────────────
    public string $deliveryAppName       = 'Pedidos Ya';
    public string $deliveryAppOrderId    = '';
    public string $deliveryAppCommission = '';

    // ──────────────────────────────────────────────────────────────────────────
    // BÚSQUEDA DE PRODUCTOS
    // ──────────────────────────────────────────────────────────────────────────
    public string $productSearch    = '';
    public string $selectedCategory = '';

    // ──────────────────────────────────────────────────────────────────────────
    // CAJA ACTUAL
    // ──────────────────────────────────────────────────────────────────────────
    public ?int $openRegisterId = null;

    // ──────────────────────────────────────────────────────────────────────────
    // MODAL: PAGO (lista de métodos)
    // ──────────────────────────────────────────────────────────────────────────
    public bool $showPaymentModal = false;
    public int|string $paymentMethodId = '';

    // ──────────────────────────────────────────────────────────────────────────
    // MODAL: CALCULADORA DE VUELTO (efectivo / BRL)
    // ──────────────────────────────────────────────────────────────────────────
    public bool   $showChangeCalculator     = false;
    public string $amountReceived           = '';
    public string $changeCalculatorCurrency = 'PYG'; // 'PYG' | 'BRL'

    // ──────────────────────────────────────────────────────────────────────────
    // MODAL: VUELTO CONFIRMADO (muestra cuánto devolver)
    // ──────────────────────────────────────────────────────────────────────────
    public bool  $showChangeModal  = false;
    public array $changeSnapshot   = []; // snapshot de calculatedChange al confirmar

    // ──────────────────────────────────────────────────────────────────────────
    // MODAL: TICKET
    // ──────────────────────────────────────────────────────────────────────────
    public bool $showTicketModal = false;
    public ?int $lastOrderId     = null;

    // ──────────────────────────────────────────────────────────────────────────
    // MODAL: COMPLEMENTOS POS
    // ──────────────────────────────────────────────────────────────────────────
    public bool  $showCustomizationsModal = false;
    public ?int  $pendingVariantId        = null;
    public array $customizationGroups     = [];
    public array $selectedCustomizations  = [];

    // ──────────────────────────────────────────────────────────────────────────
    // MODAL: VENTA POR PESO
    // ──────────────────────────────────────────────────────────────────────────
    public bool  $showWeightModal          = false;
    public ?int  $selectedWeightProductId  = null;
    public string $weightInput             = '';
    public string $amountInput             = '';
    public string $weightInputMode         = 'amount'; // 'amount' | 'weight'

    // ──────────────────────────────────────────────────────────────────────────
    // PAGO DIVIDIDO
    // ──────────────────────────────────────────────────────────────────────────
    public bool  $useSplitPayment      = false;
    public array $splitPayments        = [];
    public ?int  $pendingSplitMethodId = null;
    public bool  $showSplitAmountModal = false; // modal para tarjeta/transfer en dividido

    // ══════════════════════════════════════════════════════════════════════════
    // LIFECYCLE
    // ══════════════════════════════════════════════════════════════════════════

    public function mount(): void
    {
        $register             = CashRegister::getOpenRegister();
        $this->openRegisterId = $register?->id;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TIPO DE VENTA
    // ══════════════════════════════════════════════════════════════════════════

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

    private function getPriceChannel(): string
    {
        return $this->saleType === 'delivery_app' ? 'delivery_app' : 'pos';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MODAL: VENTA POR PESO
    // ══════════════════════════════════════════════════════════════════════════

    public function openWeightModal(int $productId): void
    {
        $product = Product::findOrFail($productId);

        if (!$product->can_sell_by_weight) {
            $this->notify('error', 'Este producto no se vende por peso.');
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

    private function getSelectedWeightProduct(): ?Product
    {
        return $this->selectedWeightProductId
            ? Product::find($this->selectedWeightProductId)
            : null;
    }

    private function getPricePerKgForChannel(): float
    {
        $product = $this->getSelectedWeightProduct();
        if (!$product) return 0.0;

        $channel = $this->getPriceChannel();

        if ($channel === 'delivery_app' && $product->price_per_kg_delivery_app) {
            return (float) $product->price_per_kg_delivery_app;
        }
        if ($channel === 'pos' && $product->price_per_kg_pos) {
            return (float) $product->price_per_kg_pos;
        }

        return (float) $product->price_per_kg;
    }

    public function addWeightToCart(): void
    {
        $product = $this->getSelectedWeightProduct();
        if (!$product) return;

        $pricePerKg = $this->getPricePerKgForChannel();
        $weight     = 0.0;
        $totalPrice = 0.0;

        if ($this->weightInputMode === 'amount') {
            $amount = (float) str_replace(['.', ','], ['', '.'], $this->amountInput);
            if ($amount <= 0) {
                $this->notify('error', 'Ingrese un monto válido.');
                return;
            }
            $weight     = $pricePerKg > 0 ? $amount / $pricePerKg : 0;
            $totalPrice = $amount;
        } else {
            $weight = (float) str_replace(',', '.', $this->weightInput);
            if ($weight <= 0 || $weight > 50) {
                $this->notify('error', 'Peso inválido (0 a 50 kg).');
                return;
            }
            $totalPrice = $pricePerKg * $weight;
        }

        $weight     = round($weight, 3);
        $totalPrice = round($totalPrice);

        $cartKey = 'weight_' . $product->id . '_' . time();

        $this->cart[$cartKey] = [
            'type'          => 'weight',
            'product_id'    => $product->id,
            'product_name'  => $product->name,
            'weight'        => $weight,
            'price_per_kg'  => $pricePerKg,
            'price'         => $totalPrice,
            'price_channel' => $this->getPriceChannel(),
            'quantity'      => 1,
            'image'         => $product->image,
        ];

        $this->updateCartTotal();
        $this->closeWeightModal();

        $this->notify('success',
            '✓ ' . number_format($weight, 3, ',', '.') . ' kg — ' . number_format($totalPrice, 0, ',', '.') . ' Gs'
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CARRITO: AGREGAR VARIANTE (UNIDAD)
    // ══════════════════════════════════════════════════════════════════════════

    public function addToCart(int $variantId): void
    {
        try {
            $variant = ProductVariant::with('product', 'cupSize')->findOrFail($variantId);

            if (!$variant->hasStock(1)) {
                $this->notify('error', 'Sin stock disponible.');
                return;
            }

            $groups = CustomizationGroup::whereHas('products', fn($q) => $q->where('product_id', $variant->product_id))
                ->where('is_active', true)
                ->with(['options' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get()
                ->map(function ($group) {
                    $arr               = $group->toArray();
                    $arr['is_multiple'] = ($group->max_selections ?? 99) > 1;
                    return $arr;
                })
                ->toArray();

            if (!empty($groups)) {
                $this->pendingVariantId        = $variantId;
                $this->customizationGroups     = $groups;
                $this->selectedCustomizations  = [];
                $this->showCustomizationsModal = true;
                return;
            }

            $this->doAddToCart($variantId, []);

        } catch (\Exception $e) {
            Log::error('POS addToCart: ' . $e->getMessage());
            $this->notify('error', 'Error al agregar producto.');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MODAL: COMPLEMENTOS POS
    // ══════════════════════════════════════════════════════════════════════════

    public function toggleCustomization(int $groupId, int $optionId, bool $isMultiple): void
    {
        $current = $this->selectedCustomizations[$groupId] ?? [];

        if ($isMultiple) {
            if (in_array($optionId, $current)) {
                $this->selectedCustomizations[$groupId] = array_values(array_diff($current, [$optionId]));
            } else {
                $group = collect($this->customizationGroups)->firstWhere('id', $groupId);
                $max   = $group['max_selections'] ?? null;
                if ($max && count($current) >= $max) return;
                $this->selectedCustomizations[$groupId][] = $optionId;
            }
        } else {
            $this->selectedCustomizations[$groupId] = in_array($optionId, $current) ? [] : [$optionId];
        }
    }

    public function confirmCustomizationsPOS(): void
    {
        if (!$this->pendingVariantId) return;

        foreach ($this->customizationGroups as $group) {
            if ($group['required'] ?? false) {
                $selected = $this->selectedCustomizations[$group['id']] ?? [];
                if (empty($selected)) {
                    $this->notify('error', "Seleccioná al menos una opción en: {$group['name']}");
                    return;
                }
            }
        }

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

    private function doAddToCart(int $variantId, array $customizations): void
    {
        $variant = ProductVariant::with('product', 'cupSize')->findOrFail($variantId);
        $price   = $variant->getPriceForChannel($this->getPriceChannel());
        $extras  = collect($customizations)->sum('price');

        $cartKey = 'variant_' . $variantId . '_' . md5(json_encode(array_column($customizations, 'option_id')));

        if (isset($this->cart[$cartKey])) {
            $currentQty = $this->cart[$cartKey]['quantity'];
            if (!$variant->hasStock($currentQty + 1)) {
                $this->notify('error', 'Stock insuficiente.');
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
                'price'           => $price,
                'extras'          => $extras,
                'customizations'  => $customizations,
                'price_channel'   => $this->getPriceChannel(),
                'quantity'        => 1,
                'available_stock' => $variant->available_stock,
                'image'           => $variant->product->image,
            ];
        }

        $this->updateCartTotal();
        $unitTotal = $price + $extras;
        $this->notify('success',
            '✓ ' . $variant->product->name . ' ' . $variant->volume . 'ml — ' . number_format($unitTotal, 0, ',', '.') . ' Gs'
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CARRITO: CANTIDAD / ELIMINAR / VACIAR
    // ══════════════════════════════════════════════════════════════════════════

    public function updateQuantity(string $cartKey, string $action): void
    {
        if (!isset($this->cart[$cartKey])) return;

        if ($this->cart[$cartKey]['type'] === 'weight') {
            $this->notify('info', 'Para cambiar el peso, eliminá y agregá de nuevo.');
            return;
        }

        if ($action === 'increment') {
            $qty       = $this->cart[$cartKey]['quantity'];
            $available = $this->cart[$cartKey]['available_stock'];

            if ($qty >= $available) {
                $this->notify('error', 'Stock insuficiente.');
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
            if ($item['type'] === 'weight') {
                return $item['price'];
            }
            return ($item['price'] + ($item['extras'] ?? 0)) * $item['quantity'];
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CLIENTE
    // ══════════════════════════════════════════════════════════════════════════

    public function selectCustomer(int $userId): void
    {
        $user                     = User::findOrFail($userId);
        $this->selectedCustomerId = $userId;
        $this->customerName       = $user->name;
        $this->customerPhone      = $user->phone ?? '';
        $this->customerSearch     = '';
        $this->saleType           = 'customer';
    }

    public function clearCustomer(): void
    {
        $this->selectedCustomerId = null;
        $this->customerName       = '';
        $this->customerPhone      = '';
        $this->customerSearch     = '';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAGO: ABRIR / CERRAR MODAL DE MÉTODOS
    // ══════════════════════════════════════════════════════════════════════════

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->notify('error', 'El carrito está vacío.');
            return;
        }
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentMethodId  = '';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAGO: SELECCIONAR MÉTODO (botón rápido o desde modal)
    // ══════════════════════════════════════════════════════════════════════════

    public function quickSale(int $paymentMethodId): void
    {
        if (empty($this->cart)) {
            $this->notify('error', 'Carrito vacío.');
            return;
        }

        if ($this->useSplitPayment) {
            $this->initSplitPaymentEntry($paymentMethodId);
            return;
        }

        // Pago único
        $this->paymentMethodId  = $paymentMethodId;
        $this->showPaymentModal = false;

        $method = PaymentMethod::find($paymentMethodId);

        if ($method && in_array($method->type, ['cash', 'foreign_currency'])) {
            $this->changeCalculatorCurrency = $method->type === 'foreign_currency' ? 'BRL' : 'PYG';
            $this->amountReceived           = '';
            $this->showChangeCalculator     = true;
        } else {
            $this->processPayment();
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAGO DIVIDIDO
    // ══════════════════════════════════════════════════════════════════════════

    public function toggleSplitPayment(): void
    {
        $this->useSplitPayment = !$this->useSplitPayment;

        if (!$this->useSplitPayment) {
            $this->resetSplitState();
        }
    }

    private function initSplitPaymentEntry(int $methodId): void
    {
        $method = PaymentMethod::find($methodId);
        if (!$method) return;

        if ($this->getRemainingAmount() <= 0) {
            $this->notify('error', 'Ya se cubrió el total.');
            return;
        }

        $this->pendingSplitMethodId = $methodId;
        $this->amountReceived       = '';
        $this->showPaymentModal     = false;

        if (in_array($method->type, ['cash', 'foreign_currency'])) {
            $this->changeCalculatorCurrency = $method->type === 'foreign_currency' ? 'BRL' : 'PYG';
            $this->showChangeCalculator     = true;
        } else {
            $this->showSplitAmountModal = true;
        }
    }

    /**
     * Confirmar monto ingresado en la calculadora de vuelto.
     * Maneja tanto pago único como pago dividido.
     */
    public function confirmPaymentWithChange(): void
    {
        $received = $this->parseAmount($this->amountReceived);

        if ($received <= 0) {
            $this->notify('error', 'Ingrese un monto válido.');
            return;
        }

        // ── PAGO DIVIDIDO ──
        if ($this->useSplitPayment && $this->pendingSplitMethodId) {
            $this->processSplitCashEntry($received);
            return;
        }

        // ── PAGO ÚNICO ──
        $calc = $this->computeChange($received);

        // Tolerancia: hasta 50 Gs de diferencia (cubre redondeo de cotización BRL y billetes)
        // Ejemplo: 16.21 R$ × 3700 = 59.977 Gs para un total de 60.000 — diferencia 23 Gs → aceptar
        $tolerance = 50;

        if ($calc['changeInGs'] < -$tolerance) {
            $this->notify('error', 'Falta: ' . number_format(abs($calc['changeInGs']), 0, ',', '.') . ' Gs');
            return;
        }

        // Si la diferencia negativa es ≤ tolerancia, ajustar el cálculo para mostrar vuelto = 0
        // (el cliente pagó "casi exacto" — no hay vuelto real que devolver)
        if ($calc['changeInGs'] < 0) {
            $calc['changeInGs']  = 0;
            $calc['changeInBrl'] = 0;
        }

        $this->showChangeCalculator = false;
        $this->processPayment($calc);
    }

    /**
     * Procesar entrada de efectivo en modo pago dividido.
     */
    private function processSplitCashEntry(float $received): void
    {
        $rate      = StoreSetting::exchangeRateBrl();
        $remaining = $this->getRemainingAmount();

        $amountInGs = $this->changeCalculatorCurrency === 'BRL'
            ? round($received * $rate)
            : $received;

        if ($amountInGs <= 0) {
            $this->notify('error', 'Ingrese un monto válido.');
            return;
        }

        $difference = $amountInGs - $remaining;

        // No puede exceder el remaining por más de 50 Gs
        if ($difference > 50) {
            $this->notify('error',
                'El monto excede lo que falta por ' . number_format($difference, 0, ',', '.') . ' Gs'
            );
            return;
        }

        // Ajuste automático si la diferencia es pequeña (± 50 Gs)
        $finalAmountGs = (abs($difference) <= 50) ? $remaining : $amountInGs;

        $this->splitPayments[] = [
            'method_id' => $this->pendingSplitMethodId,
            'amount'    => $received,
            'currency'  => $this->changeCalculatorCurrency,
            'amount_gs' => $finalAmountGs,
        ];

        $this->closeSplitEntry();
        $this->afterSplitPaymentAdded();
    }

    /**
     * Confirmar monto para tarjeta / transferencia en pago dividido.
     */
    public function confirmSplitAmount(): void
    {
        if (!$this->pendingSplitMethodId) return;

        $received  = $this->parseAmount($this->amountReceived);
        $remaining = $this->getRemainingAmount();

        if ($received <= 0) {
            $this->notify('error', 'Ingrese un monto válido.');
            return;
        }

        $difference = $received - $remaining;

        if ($difference > 50) {
            $this->notify('error',
                'El monto excede lo que falta por ' . number_format($difference, 0, ',', '.') . ' Gs'
            );
            return;
        }

        $finalAmount = (abs($difference) <= 50) ? $remaining : $received;

        $this->splitPayments[] = [
            'method_id' => $this->pendingSplitMethodId,
            'amount'    => $finalAmount,
            'currency'  => 'PYG',
            'amount_gs' => $finalAmount,
        ];

        $this->showSplitAmountModal = false;
        $this->pendingSplitMethodId = null;
        $this->amountReceived       = '';

        $this->afterSplitPaymentAdded();
    }

    public function removeSplitPayment(int $index): void
    {
        unset($this->splitPayments[$index]);
        $this->splitPayments = array_values($this->splitPayments);
    }

    private function afterSplitPaymentAdded(): void
    {
        $newRemaining = $this->getRemainingAmount();

        if ($newRemaining <= 0) {
            $this->notify('success', 'Total cubierto. Puede confirmar la venta.');
        } else {
            $this->notify('success',
                'Pago agregado. Falta: ' . number_format($newRemaining, 0, ',', '.') . ' Gs'
            );
        }
    }

    private function closeSplitEntry(): void
    {
        $this->showChangeCalculator = false;
        $this->showSplitAmountModal = false;
        $this->pendingSplitMethodId = null;
        $this->amountReceived       = '';
    }

    public function closeChangeCalculator(): void
    {
        $this->showChangeCalculator     = false;
        $this->amountReceived           = '';
        $this->paymentMethodId          = '';
        $this->pendingSplitMethodId     = null;
        $this->changeCalculatorCurrency = 'PYG';
    }

    public function getRemainingAmount(): float
    {
        $paid = collect($this->splitPayments)->sum('amount_gs');
        return max(0, $this->cartTotal - $paid);
    }

    public function getTotalSplitPaid(): float
    {
        return collect($this->splitPayments)->sum('amount_gs');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // COMPUTED: CALCULADORA DE VUELTO
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Calcula el vuelto para el monto ingresado actualmente.
     * Usa $cartTotal si es pago único, $remainingAmount si es dividido.
     */
    public function getCalculatedChangeProperty(): array
    {
        $received = $this->parseAmount($this->amountReceived);
        $rate     = StoreSetting::exchangeRateBrl();
        $base     = $this->useSplitPayment ? $this->getRemainingAmount() : $this->cartTotal;

        if ($this->changeCalculatorCurrency === 'BRL') {
            $receivedInGs = $received * $rate;
            $changeInGs   = $receivedInGs - $base;
            $changeInBrl  = $rate > 0 ? $changeInGs / $rate : 0;

            return compact('received', 'receivedInGs', 'changeInGs', 'changeInBrl', 'rate', 'base');
        }

        $changeInGs = $received - $base;
        return [
            'received'     => $received,
            'receivedInGs' => $received,
            'changeInGs'   => $changeInGs,
            'changeInBrl'  => 0,
            'rate'         => 1,
            'base'         => $base,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PROCESAR VENTA — PAGO ÚNICO
    // ══════════════════════════════════════════════════════════════════════════

    public function processPayment(?array $changeData = null): void
    {
        if ($this->useSplitPayment) {
            $this->processSplitPayment();
            return;
        }

        if (empty($this->cart)) {
            $this->notify('error', 'Carrito vacío.');
            return;
        }

        if (empty($this->paymentMethodId)) {
            $this->addError('paymentMethodId', 'Seleccioná un método de pago.');
            return;
        }

        $register = $this->resolveOpenRegister();
        if (!$register) return;

        try {
            DB::beginTransaction();

            $customerData = $this->resolveCustomerData();
            $order        = Order::create($this->buildOrderData($customerData, $this->paymentMethodId, $register->id));

            $this->createOrderItems($order);

            DB::commit();

            $this->lastOrderId = $order->id;

            // Si hay vuelto real, mostrar modal de vuelto antes del ticket
            $hasChange = $changeData && ($changeData['changeInGs'] ?? 0) > 0;

            if ($hasChange) {
                $this->changeSnapshot = $changeData; // guardar snapshot ANTES de resetear
                $this->resetAfterSale();
                $this->showChangeModal = true;
            } else {
                $this->resetAfterSale();
                $this->showTicketModal = true;
            }

            $this->notify('success', '✓ Venta #' . $order->order_number);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS processPayment: ' . $e->getMessage());
            $this->notify('error', 'Error: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PROCESAR VENTA — PAGO DIVIDIDO
    // ══════════════════════════════════════════════════════════════════════════

    public function processSplitPayment(): void
    {
        if (empty($this->cart)) {
            $this->notify('error', 'Carrito vacío.');
            return;
        }

        if (empty($this->splitPayments)) {
            $this->notify('error', 'Agregue al menos un método de pago.');
            return;
        }

        if ($this->getRemainingAmount() > 0) {
            $this->notify('error',
                'Falta cubrir ' . number_format($this->getRemainingAmount(), 0, ',', '.') . ' Gs'
            );
            return;
        }

        $register = $this->resolveOpenRegister();
        if (!$register) return;

        try {
            DB::beginTransaction();

            $customerData = $this->resolveCustomerData();
            $orderData    = $this->buildOrderData($customerData, null, $register->id);
            $orderData['is_split_payment'] = true;
            if ($this->saleType === 'counter') {
                $orderData['notes'] = 'Venta mostrador - Pago dividido';
            }

            $order = Order::create($orderData);
            $this->createOrderItems($order);

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
            $this->showTicketModal = true;
            $this->notify('success', '✓ Venta #' . $order->order_number . ' (Pago dividido)');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS processSplitPayment: ' . $e->getMessage());
            $this->notify('error', 'Error: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS: CONSTRUCCIÓN DE ORDEN
    // ══════════════════════════════════════════════════════════════════════════

    private function buildOrderData(array $customerData, ?int $paymentMethodId, ?int $registerId): array
    {
        $data = [
            'user_id'           => $customerData['user_id'],
            'order_number'      => $this->generateOrderNumber(),
            'customer_name'     => $customerData['name'],
            'customer_phone'    => $customerData['phone'],
            'customer_email'    => $customerData['email'],
            'customer_address'  => null,
            'customer_city'     => null,
            'delivery_type'     => 'pickup',
            'delivery_zone_id'  => null,
            'payment_method_id' => $paymentMethodId,
            'subtotal'          => $this->cartTotal,
            'delivery_cost'     => 0,
            'total'             => $this->cartTotal,
            'status'            => 'delivered',
            'payment_status'    => 'paid',
            'source'            => $this->saleType === 'delivery_app' ? 'delivery_app' : 'pos',
            'cash_register_id'  => $registerId,
            'confirmed_at'      => now(),
            'delivered_at'      => now(),
        ];

        if ($this->saleType === 'delivery_app') {
            $data['delivery_app_name']       = $this->deliveryAppName;
            $data['delivery_app_order_id']   = $this->deliveryAppOrderId ?: null;
            $data['delivery_app_commission'] = $this->deliveryAppCommission ?: null;
            $data['notes']                   = 'Pedido via ' . $this->deliveryAppName;
        } elseif ($this->saleType === 'counter') {
            $data['notes'] = 'Venta mostrador';
        }

        return $data;
    }

    private function createOrderItems(Order $order): void
    {
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
                $extrasUnit     = $item['extras'] ?? 0;
                $itemSubtotal   = ($item['price'] + $extrasUnit) * $item['quantity'];

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
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TICKET / VUELTO MODALES
    // ══════════════════════════════════════════════════════════════════════════

    public function closeChangeModal(): void
    {
        $this->showChangeModal  = false;
        $this->changeSnapshot   = [];
        $this->showTicketModal  = true;
    }

    public function closeTicketModal(): void
    {
        $this->showTicketModal = false;
        $this->lastOrderId     = null;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS INTERNOS
    // ══════════════════════════════════════════════════════════════════════════

    private function resolveOpenRegister(): ?CashRegister
    {
        $register = $this->openRegisterId
            ? CashRegister::where('id', $this->openRegisterId)->where('status', 'open')->first()
            : null;

        $register = $register ?? CashRegister::getOpenRegister();

        if (!$register) {
            $this->notify('error', 'La caja está cerrada. Debe abrir una nueva sesión.');
            return null;
        }

        $this->openRegisterId = $register->id;
        return $register;
    }

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
        $prefix     = $this->saleType === 'delivery_app' ? 'APP' : 'POS';
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
        $this->resetSplitState();
        $this->showPaymentModal = false;
    }

    private function resetSplitState(): void
    {
        $this->useSplitPayment      = false;
        $this->splitPayments        = [];
        $this->pendingSplitMethodId = null;
        $this->showSplitAmountModal = false;
    }

    /**
     * Parsear un string de monto con separadores de miles/decimales mixtos.
     * Formato esperado: "10.000" (miles) o "10,50" (decimales) o "10000"
     */
    private function parseAmount(string $value): float
    {
        $clean = str_replace('.', '', $value);       // quitar separadores de miles
        $clean = str_replace(',', '.', $clean);      // normalizar decimal
        return max(0, (float) $clean);
    }

    /**
     * Calcular el cambio dado un monto recibido.
     * El "base" es el total del carrito (pago único) o el remaining (dividido).
     */
    private function computeChange(float $received): array
    {
        $rate = StoreSetting::exchangeRateBrl();
        $base = $this->useSplitPayment ? $this->getRemainingAmount() : $this->cartTotal;

        if ($this->changeCalculatorCurrency === 'BRL') {
            $receivedInGs = $received * $rate;
            $changeInGs   = $receivedInGs - $base;
            $changeInBrl  = $rate > 0 ? $changeInGs / $rate : 0;
            return compact('received', 'receivedInGs', 'changeInGs', 'changeInBrl', 'rate', 'base');
        }

        $changeInGs = $received - $base;
        return [
            'received'     => $received,
            'receivedInGs' => $received,
            'changeInGs'   => $changeInGs,
            'changeInBrl'  => 0,
            'rate'         => 1,
            'base'         => $base,
        ];
    }

    /**
     * Helper centralizado para disparar notificaciones al frontend.
     */
    private function notify(string $type, string $message): void
    {
        $this->dispatch('show-notification', ['type' => $type, 'message' => $message]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════════════════════

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
            ->with(['variants' => function ($query) {
                $query->where('is_active', true)
                    ->where('visible_pos', true)
                    ->with('cupSize')
                    ->orderBy('volume');
            }])
            ->orderBy('name')
            ->paginate(12);

        $products->each(function ($product) {
            $product->setRelation('variants',
                $product->variants->filter(fn($v) => $v->hasStock(1))
            );
        });

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

        $paymentMethods  = PaymentMethod::where('is_active', true)->get();
        $selectedCustomer = $this->selectedCustomerId ? User::find($this->selectedCustomerId) : null;

        $openRegister = $this->openRegisterId
            ? CashRegister::find($this->openRegisterId)
            : CashRegister::getOpenRegister();
        $this->openRegisterId = $openRegister?->id;

        $lastOrder = $this->lastOrderId
            ? Order::with(['items', 'paymentMethod', 'payments.paymentMethod'])->find($this->lastOrderId)
            : null;

        $selectedWeightProduct = $this->getSelectedWeightProduct();

        return view('livewire.admin.pos', [
            'products'               => $products,
            'categories'             => $categories,
            'customers'              => $customers,
            'paymentMethods'         => $paymentMethods,
            'openRegister'           => $openRegister,
            'priceChannel'           => $channel,
            'lastOrder'              => $lastOrder,
            'selectedWeightProduct'  => $selectedWeightProduct,
            'selectedCustomer'       => $selectedCustomer,
            'posCustomizationGroups' => collect($this->customizationGroups),
            'calculatedChange'       => $this->calculatedChange,
            'remainingAmount'        => $this->getRemainingAmount(),
            'totalSplitPaid'         => $this->getTotalSplitPaid(),
        ])->layout('components.layouts.admin', ['title' => 'Punto de Venta']);
    }
}