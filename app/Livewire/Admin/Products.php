<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

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
    
    // Variantes
    public $variants = [];
    
    public $search = '';
    public $filterCategory = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'description' => 'nullable|string',
        'ingredients' => 'nullable|string',
        'is_active' => 'boolean',
        'image' => 'nullable|image|max:2048',
        'variants.*.volume' => 'required|integer|in:300,500,700,1000',
        'variants.*.price' => 'required|numeric|min:0',
        'variants.*.stock' => 'required|integer|min:0',
        'variants.*.is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->initializeVariants();
    }

    private function initializeVariants()
    {
        $this->variants = [
            ['volume' => 300, 'price' => '', 'stock' => 0, 'is_active' => true],
            ['volume' => 500, 'price' => '', 'stock' => 0, 'is_active' => true],
            ['volume' => 700, 'price' => '', 'stock' => 0, 'is_active' => true],
            ['volume' => 1000, 'price' => '', 'stock' => 0, 'is_active' => true],
        ];
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
        
        // Cargar variantes existentes
        $this->variants = [];
        foreach ([300, 500, 700, 1000] as $volume) {
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

        // Filtrar solo variantes con precio
        $activeVariants = collect($this->variants)->filter(function ($variant) {
            return !empty($variant['price']) && $variant['price'] > 0;
        });

        if ($activeVariants->isEmpty()) {
            session()->flash('error', 'Debe agregar al menos una variante con precio.');
            return;
        }

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'is_active' => $this->is_active,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('products', 'public');
        }

        if ($this->editMode) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
            
            // Actualizar o crear variantes
            foreach ($this->variants as $variantData) {
                if (!empty($variantData['price']) && $variantData['price'] > 0) {
                    if (isset($variantData['id'])) {
                        // Actualizar variante existente
                        ProductVariant::where('id', $variantData['id'])->update([
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                            'is_active' => $variantData['is_active'] ?? true,
                        ]);
                    } else {
                        // Crear nueva variante
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'volume' => $variantData['volume'],
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                            'is_active' => $variantData['is_active'] ?? true,
                        ]);
                    }
                } elseif (isset($variantData['id'])) {
                    // Eliminar variante si ya no tiene precio
                    ProductVariant::where('id', $variantData['id'])->delete();
                }
            }
            
            session()->flash('message', 'Producto actualizado correctamente.');
        } else {
            $product = Product::create($data);
            
            // Crear variantes
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
            
            session()->flash('message', 'Producto creado correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Product::findOrFail($id)->delete();
        session()->flash('message', 'Producto eliminado correctamente.');
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
        $this->reset(['name', 'category_id', 'description', 'ingredients', 'is_active', 'image', 'currentImage', 'productId']);
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
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $categories = Category::where('is_active', true)->get();

        return view('livewire.admin.products', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('components.layouts.admin', ['title' => 'Gestión de Productos']);
    }
}