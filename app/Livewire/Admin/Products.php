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
        'image' => 'nullable|image|max:5120',
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

    /**
     * Procesar y guardar imagen como WebP con hash
     * 
     * @param int $productId
     * @return array ['path' => string, 'hash' => string]
     */
    private function processImage(int $productId): array
    {
        if (!$this->image) {
            return ['path' => null, 'hash' => null];
        }

        // Generar hash único
        $hash = hash('sha256', $productId . microtime() . Str::random(10));
        
        // Crear directorio si no existe
        $directory = 'productos';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Nombre del archivo con hash
        $filename = $hash . '.webp';
        $path = "{$directory}/{$filename}";
        $fullPath = Storage::disk('public')->path($path);

        // Eliminar imagen anterior si existe
        $this->deleteOldImages($productId);

        // Convertir a WebP con Intervention Image
        $img = Image::read($this->image->getRealPath());
        
        // Redimensionar si es muy grande (max 800px de ancho manteniendo proporción)
        $img->scale(width: 800);
        
        // Guardar como WebP con calidad 80%
        $img->toWebp(quality: 80)->save($fullPath);

        return [
            'path' => $path,
            'hash' => $hash,
        ];
    }

    /**
     * Eliminar imágenes anteriores del producto
     */
    private function deleteOldImages(int $productId): void
    {
        $product = Product::find($productId);
        
        if ($product && $product->image) {
            if (Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
        }
    }

    /**
     * Eliminar imagen de un producto
     */
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

        if ($this->editMode) {
            $product = Product::findOrFail($this->productId);
            
            // Procesar nueva imagen si se subió
            if ($this->image) {
                $imageData = $this->processImage($product->id);
                $data['image'] = $imageData['path'];
                $data['image_hash'] = $imageData['hash'];
            }
            
            $product->update($data);
            
            // Actualizar o crear variantes
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
                    ProductVariant::where('id', $variantData['id'])->delete();
                }
            }
            
            session()->flash('message', 'Producto actualizado correctamente.');
        } else {
            // Crear producto primero (sin imagen)
            $product = Product::create($data);
            
            // Ahora procesar imagen con el ID del producto
            if ($this->image) {
                $imageData = $this->processImage($product->id);
                $product->update([
                    'image' => $imageData['path'],
                    'image_hash' => $imageData['hash'],
                ]);
            }
            
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
        $product = Product::findOrFail($id);
        
        // Verificar si el producto tiene ventas asociadas
        $hasSales = \App\Models\OrderItem::where('product_id', $product->id)->exists();
        
        if ($hasSales) {
            session()->flash('error', 'No se puede eliminar este producto porque tiene ventas asociadas. Puedes desactivarlo en su lugar.');
            return;
        }
        
        // Verificar si el producto está en carritos activos
        $inCarts = \App\Models\CartItem::where('product_id', $product->id)->exists();
        
        if ($inCarts) {
            session()->flash('error', 'No se puede eliminar este producto porque hay clientes que lo tienen en su carrito. Puedes desactivarlo en su lugar.');
            return;
        }
        
        // Si no tiene ventas ni está en carritos, se puede eliminar
        try {
            // Eliminar imagen del producto
            $this->deleteProductImage($product->image);
            
            // Eliminar producto (las variantes se eliminan por cascade)
            $product->delete();
            
            session()->flash('message', 'Producto eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar solo la imagen del producto
     */
    public function removeImage()
    {
        if ($this->editMode && $this->currentImage) {
            $product = Product::findOrFail($this->productId);
            
            // Eliminar archivo
            $this->deleteProductImage($this->currentImage);
            
            // Actualizar BD
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

    /**
     * Desactivar producto (alternativa a eliminar)
     */
    public function deactivateProduct($id)
    {
        $product = Product::findOrFail($id);
        
        $product->update(['is_active' => false]);
        
        // Desactivar también todas sus variantes
        $product->variants()->update(['is_active' => false]);
        
        session()->flash('message', 'Producto desactivado correctamente. Ya no será visible para los clientes.');
    }
}