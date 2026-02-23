<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\CupSize;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class Products extends Component
{
    use WithPagination, WithFileUploads;

    public $showModal = false;
    public $editMode = false;
    public $productId;

    // Campos básicos del producto
    public $name;
    public $category_id;
    public $description;
    public $ingredients;
    public $is_active = true;
    public $image;
    public $currentImage;

    // Tipo de venta
    public $sale_type = 'unit'; // 'unit', 'weight', 'both'

    // Precios por kg (3 canales)
    public $price_per_kg         = ''; // Web/ecommerce
    public $price_per_kg_pos     = ''; // Tienda física
    public $price_per_kg_delivery_app = ''; // Pedidos Ya

    // Variantes (para productos unitarios)
    // Cada variante ahora tiene 3 precios: web, pos, delivery_app
    public $variants = [];

    // Filtros
    public $search = '';
    public $filterCategory = '';
    public $filterSaleType = '';

    // ==========================================
    // VALIDACIÓN
    // ==========================================

    protected function rules(): array
    {
        $rules = [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'ingredients' => 'nullable|string',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|max:5120',
            'sale_type'   => 'required|in:unit,weight,both',
        ];

        // Precio por kg web es obligatorio si vende por peso
        if (in_array($this->sale_type, ['weight', 'both'])) {
            $rules['price_per_kg'] = 'required|numeric|min:1';
            // POS y Pedidos Ya son opcionales (caen al precio web si están vacíos)
            $rules['price_per_kg_pos']          = 'nullable|numeric|min:1';
            $rules['price_per_kg_delivery_app'] = 'nullable|numeric|min:1';
        }

        // Variantes si vende por unidad
        if (in_array($this->sale_type, ['unit', 'both'])) {
            $rules['variants.*.volume']    = 'required|integer|min:0';
            $rules['variants.*.price']     = 'nullable|numeric|min:0';
            $rules['variants.*.price_pos'] = 'nullable|numeric|min:0';
            $rules['variants.*.price_delivery_app'] = 'nullable|numeric|min:0';
            $rules['variants.*.is_active'] = 'boolean';
        }

        return $rules;
    }

    protected $messages = [
        'price_per_kg.required' => 'El precio web por kg es obligatorio.',
        'price_per_kg.min'      => 'El precio por kg debe ser mayor a 0.',
    ];


    // ==========================================
    // MOUNT
    // ==========================================

    public function mount(): void
    {
        $this->initializeVariants();
    }

    // ==========================================
    // INICIALIZACIÓN DE VARIANTES
    // ==========================================

    private function initializeVariants(): void
    {
        $cupVolumes = CupSize::orderBy('volume_ml')->pluck('volume_ml')->toArray();
        $volumes = array_merge([0], $cupVolumes);

        $this->variants = collect($volumes)->map(fn($vol) => [
            'volume'              => $vol,
            'price'               => '',
            'price_pos'           => '',
            'price_delivery_app'  => '',
            'is_active'           => true,
            'visible_web'         => true,  // ← NUEVO
            'visible_pos'         => true,  // ← NUEVO
            'visible_app'         => true,  // ← NUEVO
        ])->toArray();
}
    // ==========================================
    // CRUD
    // ==========================================

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function edit(int $id): void
    {
        $product = Product::with('variants.cupSize')->findOrFail($id);

        $this->productId   = $product->id;
        $this->name        = $product->name;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->ingredients = $product->ingredients;
        $this->is_active   = $product->is_active;
        $this->currentImage = $product->image;
        $this->sale_type   = $product->sale_type ?? 'unit';

        // Precios por kg (3 canales)
        $this->price_per_kg              = $product->price_per_kg ?? '';
        $this->price_per_kg_pos          = $product->price_per_kg_pos ?? '';
        $this->price_per_kg_delivery_app = $product->price_per_kg_delivery_app ?? '';

        // Cargar volúmenes dinámicamente
        $cupVolumes = CupSize::orderBy('volume_ml')->pluck('volume_ml')->toArray();
        $volumes = array_merge([0], $cupVolumes);

        // Cargar variantes (todos los volúmenes, con datos si existen)
        $this->variants = collect($volumes)->map(function ($volume) use ($product) {
            $variant = $product->variants->firstWhere('volume', $volume);

            if ($variant) {
                return [
                    'id'                  => $variant->id,
                    'volume'              => $variant->volume,
                    'price'               => $variant->price,
                    'price_pos'           => $variant->price_pos ?? '',
                    'price_delivery_app'  => $variant->price_delivery_app ?? '',
                    'is_active'           => $variant->is_active,
                    'visible_web'         => $variant->visible_web ?? true,  // ← NUEVO
                    'visible_pos'         => $variant->visible_pos ?? true,  // ← NUEVO
                    'visible_app'         => $variant->visible_app ?? true,  // ← NUEVO
                    'cup_size_name'       => $variant->cupSize?->name ?? ($volume == 0 ? 'Unidad' : "{$volume}ml"),
                    'cup_stock'           => $variant->cupSize?->stock ?? 0,
                ];
            }

            return [
                'volume'              => $volume,
                'price'               => '',
                'price_pos'           => '',
                'price_delivery_app'  => '',
                'is_active'           => false,
                'visible_web'         => true,  // ← NUEVO
                'visible_pos'         => true,  // ← NUEVO
                'visible_app'         => true,  // ← NUEVO
                'cup_size_name'       => $volume == 0 ? 'Unidad' : "{$volume}ml",
                'cup_stock'           => CupSize::findByVolume($volume)?->stock ?? 0,
            ];
        })->toArray();

        $this->showModal = true;
        $this->editMode  = true;
    }

    public function save(): void
    {
        $this->validate();

        // Al menos una variante con precio web si vende por unidad
        if (in_array($this->sale_type, ['unit', 'both'])) {
            $hasActive = collect($this->variants)->contains(
                fn($v) => !empty($v['price']) && $v['price'] > 0
            );

            if (!$hasActive) {
                session()->flash('error', 'Debe agregar al menos una variante con precio web.');
                return;
            }
        }

        $data = [
            'name'        => $this->name,
            'slug'        => Str::slug($this->name),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'is_active'   => $this->is_active,
            'sale_type'   => $this->sale_type,
        ];

        // Precios por kg según tipo
        if (in_array($this->sale_type, ['weight', 'both'])) {
            $data['price_per_kg']              = $this->price_per_kg;
            $data['price_per_kg_pos']          = $this->price_per_kg_pos ?: null;
            $data['price_per_kg_delivery_app'] = $this->price_per_kg_delivery_app ?: null;
        } else {
            $data['price_per_kg']              = null;
            $data['price_per_kg_pos']          = null;
            $data['price_per_kg_delivery_app'] = null;
        }

        if ($this->editMode) {
            $product = Product::findOrFail($this->productId);

            if ($this->image) {
                $imageData = $this->processImage($product->id);
                $data['image']      = $imageData['path'];
                $data['image_hash'] = $imageData['hash'];
            }

            $product->update($data);
            $this->saveVariants($product);

            session()->flash('message', 'Producto actualizado correctamente.');
        } else {
            $product = Product::create($data);

            if ($this->image) {
                $imageData = $this->processImage($product->id);
                $product->update([
                    'image'      => $imageData['path'],
                    'image_hash' => $imageData['hash'],
                ]);
            }

            $this->saveVariants($product);

            session()->flash('message', 'Producto creado correctamente.');
        }

        $this->closeModal();
    }

    /**
     * Guardar/actualizar variantes del producto.
     * Solo crea/actualiza variantes que tengan precio web definido.
     * Si el sale_type pasa a 'weight', elimina todas las variantes.
     */
    private function saveVariants(Product $product): void
    {
        // Si es solo por peso, eliminar variantes
        if ($this->sale_type === 'weight') {
            $product->variants()->delete();
            return;
        }

        foreach ($this->variants as $variantData) {
            $hasPrice = !empty($variantData['price']) && $variantData['price'] > 0;

            if ($hasPrice) {
                // Buscar cup_size_id para este volumen
                $cupSize    = CupSize::findByVolume($variantData['volume']);
                $cupSizeId  = $cupSize?->id;

                $variantPayload = [
                    'price'              => $variantData['price'],
                    'price_pos'          => $variantData['price_pos'] ?: null,
                    'price_delivery_app' => $variantData['price_delivery_app'] ?: null,
                    'cup_size_id'        => $cupSizeId,
                    'is_active'          => $variantData['is_active'] ?? true,
                    'visible_web'        => $variantData['visible_web'] ?? true,  // ← NUEVO
                    'visible_pos'        => $variantData['visible_pos'] ?? true,  // ← NUEVO
                    'visible_app'        => $variantData['visible_app'] ?? true,  // ← NUEVO
                ];

                if (isset($variantData['id'])) {
                    // Actualizar existente
                    ProductVariant::where('id', $variantData['id'])
                        ->update($variantPayload);
                } else {
                    // Crear nueva
                    ProductVariant::create(array_merge($variantPayload, [
                        'product_id' => $product->id,
                        'volume'     => $variantData['volume'],
                        'stock'      => 0, // stock legacy = 0, el real está en cup_sizes
                    ]));
                }
            } elseif (isset($variantData['id'])) {
                // Tenía precio antes y ahora no → desactivar (no eliminar por historial)
                ProductVariant::where('id', $variantData['id'])
                    ->update(['is_active' => false]);
            }
        }
    }

    // ==========================================
    // ELIMINAR / DESACTIVAR
    // ==========================================

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);

        if (\App\Models\OrderItem::where('product_id', $id)->exists()) {
            session()->flash('error', 'No se puede eliminar: tiene ventas. Podés desactivarlo.');
            return;
        }

        if (\App\Models\CartItem::where('product_id', $id)->exists()) {
            session()->flash('error', 'No se puede eliminar: está en carritos activos. Podés desactivarlo.');
            return;
        }

        try {
            $this->deleteProductImage($product->image);
            $product->delete();
            session()->flash('message', 'Producto eliminado.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function deactivateProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => false]);
        $product->variants()->update(['is_active' => false]);
        session()->flash('message', 'Producto desactivado. Ya no es visible para clientes.');
    }

    public function toggleActive(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        session()->flash('message', 'Estado actualizado.');
    }

    // ==========================================
    // IMAGEN
    // ==========================================

    private function processImage(int $productId): array
    {
        if (!$this->image) {
            return ['path' => null, 'hash' => null];
        }

        $hash      = hash('sha256', $productId . microtime() . Str::random(10));
        $directory = 'productos';

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $path     = "{$directory}/{$hash}.webp";
        $fullPath = Storage::disk('public')->path($path);

        $this->deleteOldImages($productId);

        Image::read($this->image->getRealPath())
            ->scale(width: 800)
            ->toWebp(quality: 80)
            ->save($fullPath);

        return ['path' => $path, 'hash' => $hash];
    }

    private function deleteOldImages(int $productId): void
    {
        $product = Product::find($productId);
        if ($product?->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
    }

    private function deleteProductImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    public function removeImage(): void
    {
        if ($this->editMode && $this->currentImage) {
            $product = Product::findOrFail($this->productId);
            $this->deleteProductImage($this->currentImage);
            $product->update(['image' => null, 'image_hash' => null]);
            $this->currentImage = null;
            session()->flash('message', 'Imagen eliminada.');
        }
    }

    // ==========================================
    // MODAL
    // ==========================================

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'name', 'category_id', 'description', 'ingredients',
            'is_active', 'image', 'currentImage', 'productId',
            'sale_type',
            'price_per_kg', 'price_per_kg_pos', 'price_per_kg_delivery_app',
        ]);
        $this->sale_type = 'unit';
        $this->is_active = true;
        $this->initializeVariants();
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $products = Product::with(['category', 'variants.cupSize'])
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->when($this->filterCategory, fn($q) =>
                $q->where('category_id', $this->filterCategory)
            )
            ->when($this->filterSaleType, fn($q) =>
                $q->where('sale_type', $this->filterSaleType)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $categories = Category::where('is_active', true)->get();
        $cupSizes = CupSize::active()->orderBy('volume_ml')->get();

        // Volúmenes dinámicos
        $cupVolumes = $cupSizes->pluck('volume_ml')->toArray();
        $volumes = array_merge([0], $cupVolumes);

        return view('livewire.admin.products', [
            'products'  => $products,
            'categories'=> $categories,
            'cupSizes'  => $cupSizes,
            'volumes'   => $volumes, // ← Pasar a la vista
        ])->layout('components.layouts.admin', ['title' => 'Gestión de Productos']);
    }
}
