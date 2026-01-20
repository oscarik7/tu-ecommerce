<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class Categories extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $categoryId;
    
    public $name;
    public $description;
    public $is_active = true;
    
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = $category->is_active;
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->editMode) {
            Category::findOrFail($this->categoryId)->update($data);
            session()->flash('message', 'Categoría actualizada correctamente.');
        } else {
            Category::create($data);
            session()->flash('message', 'Categoría creada correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        
        // Verificar si la categoría tiene productos asociados
        $productsCount = $category->products()->count();
        
        if ($productsCount > 0) {
            session()->flash('error', "No se puede eliminar la categoría '{$category->name}' porque tiene {$productsCount} producto(s) asociado(s). Puedes desactivarla en su lugar.");
            return;
        }
        
        // Si no tiene productos, se puede eliminar
        try {
            $category->delete();
            session()->flash('message', "Categoría '{$category->name}' eliminada correctamente.");
        } catch (\Exception $e) {
            \Log::error('Error al eliminar categoría: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar la categoría. Por favor intenta de nuevo.');
        }
    }

    /**
     * Desactivar categoría (alternativa a eliminar)
     */
    public function deactivateCategory($id)
    {
        $category = Category::findOrFail($id);
        
        $category->update(['is_active' => false]);
        
        // Opcional: También desactivar todos los productos de esta categoría
        // $category->products()->update(['is_active' => false]);
        
        session()->flash('message', "Categoría '{$category->name}' desactivada correctamente. Los productos de esta categoría seguirán visibles si están activos.");
    }

    public function toggleActive($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
        session()->flash('message', 'Estado de la categoría actualizado.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['name', 'description', 'is_active', 'categoryId']);
    }

    public function render()
    {
        $categories = Category::withCount('products')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.admin.categories', [
            'categories' => $categories,
        ])->layout('components.layouts.admin', ['title' => 'Gestión de Categorías']);
    }
}