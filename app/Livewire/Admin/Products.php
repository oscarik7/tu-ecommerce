<?php

namespace App\Livewire\Admin;

use App\Models\Product;
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
    public $price;
    public $stock;
    public $is_active = true;
    public $image;
    public $currentImage;
    
    public $search = '';
    public $filterCategory = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'description' => 'nullable|string',
        'ingredients' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'image' => 'nullable|image|max:2048',
    ];

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->ingredients = $product->ingredients;
        $this->price = $product->price;
        $this->stock = $product->stock;
        $this->is_active = $product->is_active;
        $this->currentImage = $product->image;
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('products', 'public');
        }

        if ($this->editMode) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
            session()->flash('message', 'Producto actualizado correctamente.');
        } else {
            Product::create($data);
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
        $this->reset(['name', 'category_id', 'description', 'ingredients', 'price', 'stock', 'is_active', 'image', 'currentImage', 'productId']);
    }

    public function render()
    {
        $products = Product::with('category')
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