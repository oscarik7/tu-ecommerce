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
    public $customerEmail = '';
    public $isGuestSale = false;

    // Búsqueda de productos
    public $productSearch = '';
    public $selectedCategory = '';

    // Pago
    public $paymentMethodId = '';
    public $showPaymentModal = false;

    // Ticket
    public $showTicketModal = false;
    public $lastOrder = null;

    protected $rules = [
        'customerName' => 'required|string|max:255',
        'customerPhone' => 'required|string|max:20',
        'customerEmail' => 'nullable|email',
        'paymentMethodId' => 'required|exists:payment_methods,id',
    ];

    protected $messages = [
        'customerName.required' => 'El nombre del cliente es obligatorio.',
        'customerPhone.required' => 'El teléfono del cliente es obligatorio.',
        'customerEmail.email' => 'El email debe ser una dirección válida.',
        'paymentMethodId.required' => 'Debe seleccionar un método de pago.',
        'paymentMethodId.exists' => 'El método de pago seleccionado no es válido.',
    ];

    public function mount()
    {
        $this->updateCartTotal();
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
                // Verificar que no exceda el stock
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
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Error al agregar producto al carrito.'
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
                    'message' => 'No hay suficiente stock disponible.'
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

    public function selectCustomer($userId)
    {
        $user = User::findOrFail($userId);
        $this->selectedCustomer = $user;
        $this->customerName = $user->name;
        $this->customerPhone = $user->phone ?? '';
        $this->customerEmail = $user->email;
        $this->customerSearch = '';
        $this->isGuestSale = false;
    }

    public function clearCustomer()
    {
        $this->selectedCustomer = null;
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->isGuestSale = false;
        $this->customerSearch = '';
    }

    public function setGuestSale()
    {
        $this->clearCustomer();
        $this->isGuestSale = true;
        $this->customerName = 'Cliente Mostrador';
        $this->customerPhone = '0000000000';
        $this->customerEmail = '';
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

        if (!$this->isGuestSale && !$this->selectedCustomer) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Seleccione un cliente o venta de mostrador.'
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

        // Validar método de pago primero
        if (empty($this->paymentMethodId)) {
            $this->addError('paymentMethodId', 'Debe seleccionar un método de pago.');
            return;
        }

        // Validar datos del cliente solo si no es venta de mostrador y no hay cliente seleccionado
        if (!$this->isGuestSale && !$this->selectedCustomer) {
            $this->validate([
                'customerName' => 'required|string|max:255',
                'customerPhone' => 'required|string|max:20',
                'customerEmail' => 'nullable|email',
            ]);
        }

        try {
            DB::beginTransaction();

            // Crear o buscar usuario
            if ($this->isGuestSale) {
                $user = User::firstOrCreate(
                    ['email' => 'mostrador@pos.local'],
                    [
                        'name' => 'Cliente Mostrador',
                        'phone' => '0000000000',
                        'password' => bcrypt(Str::random(16)),
                    ]
                );
                
                // Asignar rol si no lo tiene
                if (!$user->hasRole('customer')) {
                    $user->assignRole('customer');
                }
            } elseif ($this->selectedCustomer) {
                $user = $this->selectedCustomer;
            } else {
                // Buscar o crear usuario
                $user = User::where('email', $this->customerEmail)
                    ->orWhere('phone', $this->customerPhone)
                    ->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $this->customerName,
                        'email' => $this->customerEmail ?? 'cliente_' . time() . '@pos.local',
                        'phone' => $this->customerPhone,
                        'password' => bcrypt(Str::random(16)),
                    ]);
                    $user->assignRole('customer');
                }
            }

            // Crear orden
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'POS-' . strtoupper(Str::random(8)),
                'customer_name' => $this->customerName ?: $user->name,
                'customer_phone' => $this->customerPhone ?: $user->phone,
                'customer_email' => $this->customerEmail ?: $user->email,
                'delivery_type' => 'pickup',
                'payment_method_id' => $this->paymentMethodId,
                'subtotal' => $this->cartTotal,
                'delivery_cost' => 0,
                'total' => $this->cartTotal,
                'status' => 'delivered',
                'notes' => 'Venta en tienda (POS)',
            ]);

            // Crear items y actualizar stock
            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['product_name'] . ' - ' . $item['volume'] . 'ml',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Reducir stock
                ProductVariant::where('id', $item['variant_id'])
                    ->decrement('stock', $item['quantity']);
            }

            DB::commit();

            // Guardar orden para el ticket
            $this->lastOrder = $order->load(['items', 'paymentMethod']);

            // Limpiar todo
            $this->resetAfterSale();

            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Venta procesada exitosamente. Ticket #' . $order->order_number
            ]);

            $this->showPaymentModal = false;
            $this->showTicketModal = true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ]);
        }
    }

    private function resetAfterSale()
    {
        $this->cart = [];
        $this->cartTotal = 0;
        $this->clearCustomer();
        $this->paymentMethodId = '';
    }

    public function closeTicketModal()
    {
        $this->showTicketModal = false;
        $this->lastOrder = null;
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

        $customers = collect();
        if ($this->customerSearch && strlen($this->customerSearch) >= 2) {
            $customers = User::role('customer')
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
                })
                ->where('email', '!=', 'mostrador@pos.local') // Excluir usuario de mostrador
                ->limit(5)
                ->get();
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