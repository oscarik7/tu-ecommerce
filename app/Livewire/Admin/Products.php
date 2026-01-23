<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
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
    
    public $name;
    public $category_id;
    public $description;
    public $ingredients;
    public $is_active = true;
    public $image;
    public $currentImage;
    
    // 🆕 Tipo de venta
    public $sale_type = 'unit'; // 'unit', 'weight', 'both'
    public $price_per_kg = '';
    
    // Variantes (para productos unitarios)
    public $variants = [];
    
    public $search = '';
    public $filterCategory = '';
    public $filterSaleType = '';

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'ingredients' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:5120',
            'sale_type' => 'required|in:unit,weight,both',
        ];

        // Validar precio por kg si es venta por peso
        if (in_array($this->sale_type, ['weight', 'both'])) {
            $rules['price_per_kg'] = 'required|numeric|min:1';
        }

        // Validar variantes si es venta por unidad
        if (in_array($this->sale_type, ['unit', 'both'])) {
            $rules['variants.*.volume'] = 'required|integer|in:300,400,500,700,1000';
            $rules['variants.*.price'] = 'nullable|numeric|min:0';
            $rules['variants.*.stock'] = 'required|integer|min:0';
            $rules['variants.*.is_active'] = 'boolean';
        }

        return $rules;
    }

    protected $messages = [
        'price_per_kg.required' => 'El precio por kg es obligatorio para productos por peso.',
        'price_per_kg.min' => 'El precio por kg debe ser mayor a 0.',
    ];

    public function mount()
    {
        $this->initializeVariants();
    }

    private function initializeVariants()
    {
        $this->variants = [
            ['volume' => 300, 'price' => '', 'stock' => 0, 'is_active' => true],
            ['volume' => 400, 'price' => '', 'stock' => 0, 'is_active' => true],
            ['volume' => 500, 'price' => '', 'stock' => 0, 'is_active' => true],
            ['volume' => 700, 'price' => '', 'stock' => 0, 'is_active' => true],
            ['volume' => 1000, 'price' => '', 'stock' => 0, 'is_active' => true],
        ];
    }

    /**
     * Procesar y guardar imagen como WebP con hash
     */
    private function processImage(int $productId): array
    {
        if (!$this->image) {
            return ['path' => null, 'hash' => null];
        }

        $hash = hash('sha256', $productId . microtime() . Str::random(10));
        
        $directory = 'productos';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $filename = $hash . '.webp';
        $path = "{$directory}/{$filename}";
        $fullPath = Storage::disk('public')->path($path);

        $this->deleteOldImages($productId);

        $img = Image::read($this->image->getRealPath());
        $img->scale(width: 800);
        $img->toWebp(quality: 80)->save($fullPath);

        return [
            'path' => $path,
            'hash' => $hash,
        ];
    }

    private function deleteOldImages(int $productId): void
    {
        $product = Product::find($productId);
        
        if ($product && $product->image) {
            if (Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
        }
    }

    private function deleteProductImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function edit($id)
    {
        $product = Product::with('variants')->findOrFail($id);
        
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->ingredients = $product->ingredients;
        $this->is_active = $product->is_active;
        $this->currentImage = $product->image;
        $this->sale_type = $product->sale_type ?? 'unit';
        $this->price_per_kg = $product->price_per_kg ?? '';
        
        // Cargar variantes existentes
        $this->variants = [];
        foreach ([300, 400, 500, 700, 1000] as $volume) {
            $variant = $product->variants->firstWhere('volume', $volume);
            if ($variant) {
                $this->variants[] = [
                    'id' => $variant->id,
                    'volume' => $variant->volume,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'is_active' => $variant->is_active,
                ];
            } else {
                $this->variants[] = [
                    'volume' => $volume,
                    'price' => '',
                    'stock' => 0,
                    'is_active' => false,
                ];
            }
        }
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        // Validar según tipo de venta
        if (in_array($this->sale_type, ['unit', 'both'])) {
            $activeVariants = collect($this->variants)->filter(function ($variant) {
                return !empty($variant['price']) && $variant['price'] > 0;
            });

            if ($activeVariants->isEmpty()) {
                session()->flash('error', 'Debe agregar al menos una variante con precio para productos por unidad.');
                return;
            }
        }

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'is_active' => $this->is_active,
            'sale_type' => $this->sale_type,
            'price_per_kg' => in_array($this->sale_type, ['weight', 'both']) ? $this->price_per_kg : null,
        ];

        if ($this->editMode) {
            $product = Product::findOrFail($this->productId);
            
            if ($this->image) {
                $imageData = $this->processImage($product->id);
                $data['image'] = $imageData['path'];
                $data['image_hash'] = $imageData['hash'];
            }
            
            $product->update($data);
            
            // Actualizar variantes solo si el tipo permite unidades
            if (in_array($this->sale_type, ['unit', 'both'])) {
                foreach ($this->variants as $variantData) {
                    if (!empty($variantData['price']) && $variantData['price'] > 0) {
                        if (isset($variantData['id'])) {
                            ProductVariant::where('id', $variantData['id'])->update([
                                'price' => $variantData['price'],
                                'stock' => $variantData['stock'],
                                'is_active' => $variantData['is_active'] ?? true,
                            ]);
                        } else {
                            ProductVariant::create([
                                'product_id' => $product->id,
                                'volume' => $variantData['volume'],
                                'price' => $variantData['price'],
                                'stock' => $variantData['stock'],
                                'is_active' => $variantData['is_active'] ?? true,
                            ]);
                        }
                    } elseif (isset($variantData['id'])) {
                        // Si cambia a solo peso, eliminar variantes
                        if ($this->sale_type === 'weight') {
                            ProductVariant::where('id', $variantData['id'])->delete();
                        }
                    }
                }
            } else {
                // Si es solo por peso, eliminar todas las variantes
                $product->variants()->delete();
            }
            
            session()->flash('message', 'Producto actualizado correctamente.');
        } else {
            $product = Product::create($data);
            
            if ($this->image) {
                $imageData = $this->processImage($product->id);
                $product->update([
                    'image' => $imageData['path'],
                    'image_hash' => $imageData['hash'],
                ]);
            }
            
            // Crear variantes solo si el tipo permite unidades
            if (in_array($this->sale_type, ['unit', 'both'])) {
                foreach ($this->variants as $variantData) {
                    if (!empty($variantData['price']) && $variantData['price'] > 0) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'volume' => $variantData['volume'],
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                            'is_active' => $variantData['is_active'] ?? true,
                        ]);
                    }
                }
            }
            
            session()->flash('message', 'Producto creado correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        
        $hasSales = \App\Models\OrderItem::where('product_id', $product->id)->exists();
        
        if ($hasSales) {
            session()->flash('error', 'No se puede eliminar este producto porque tiene ventas asociadas. Puedes desactivarlo en su lugar.');
            return;
        }
        
        $inCarts = \App\Models\CartItem::where('product_id', $product->id)->exists();
        
        if ($inCarts) {
            session()->flash('error', 'No se puede eliminar este producto porque hay clientes que lo tienen en su carrito. Puedes desactivarlo en su lugar.');
            return;
        }
        
        try {
            $this->deleteProductImage($product->image);
            $product->delete();
            session()->flash('message', 'Producto eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    public function removeImage()
    {
        if ($this->editMode && $this->currentImage) {
            $product = Product::findOrFail($this->productId);
            
            $this->deleteProductImage($this->currentImage);
            
            $product->update([
                'image' => null,
                'image_hash' => null,
            ]);
            
            $this->currentImage = null;
            
            session()->flash('message', 'Imagen eliminada correctamente.');
        }
    }

    public function toggleActive($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        session()->flash('message', 'Estado del producto actualizado.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'name', 'category_id', 'description', 'ingredients', 
            'is_active', 'image', 'currentImage', 'productId',
            'sale_type', 'price_per_kg'
        ]);
        $this->sale_type = 'unit';
        $this->is_active = true;
        $this->initializeVariants();
    }

    public function render()
    {
        $products = Product::with(['category', 'variants'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('category_id', $this->filterCategory);
            })
            ->when($this->filterSaleType, function ($query) {
                $query->where('sale_type', $this->filterSaleType);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $categories = Category::where('is_active', true)->get();

        return view('livewire.admin.products', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('components.layouts.admin', ['title' => 'Gestión de Productos']);
    }

    public function deactivateProduct($id)
    {
        $product = Product::findOrFail($id);
        
        $product->update(['is_active' => false]);
        $product->variants()->update(['is_active' => false]);
        
        session()->flash('message', 'Producto desactivado correctamente. Ya no será visible para los clientes.');
    }
}