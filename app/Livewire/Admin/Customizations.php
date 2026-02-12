<?php

namespace App\Livewire\Admin;

use App\Models\CustomizationGroup;
use App\Models\CustomizationOption;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class Customizations extends Component
{
    // ── Vista activa ──────────────────────────────
    public string $view = 'groups'; // groups | options | assign

    // ── Grupo seleccionado para ver sus opciones ──
    public ?int $selectedGroupId = null;

    // ── Modal Grupo ───────────────────────────────
    public bool   $showGroupModal  = false;
    public ?int   $editingGroupId  = null;
    public string $groupName       = '';
    public string $groupDesc       = '';
    public bool   $groupRequired   = false;
    public bool   $groupMultiple   = true;
    public        $groupMax        = '';
    public        $groupMin        = 0;
    public int    $groupSort       = 0;

    // ── Modal Opción ──────────────────────────────
    public bool   $showOptionModal = false;
    public ?int   $editingOptionId = null;
    public string $optionName      = '';
    public        $optionPrice     = 0;
    public int    $optionSort      = 0;

    // ── Modal Asignación a productos ──────────────
    public bool   $showAssignModal    = false;
    public ?int   $assigningGroupId   = null;
    public array  $selectedProductIds = [];

    // ── Filtro de asignación ──────────────────────
    public ?int   $filterCategoryId   = null;

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(): void {}

    // ==========================================
    // NAVEGACIÓN
    // ==========================================

    public function selectGroup(int $id): void
    {
        $this->selectedGroupId = $id;
        $this->view = 'options';
    }

    public function backToGroups(): void
    {
        $this->selectedGroupId = null;
        $this->view = 'groups';
    }

    // ==========================================
    // MODAL GRUPO
    // ==========================================

    public function openGroupModal(?int $id = null): void
    {
        $this->resetGroupForm();
        if ($id) {
            $g = CustomizationGroup::findOrFail($id);
            $this->editingGroupId = $id;
            $this->groupName      = $g->name;
            $this->groupDesc      = $g->description ?? '';
            $this->groupRequired  = $g->required;
            $this->groupMultiple  = $g->multiple;
            $this->groupMax       = $g->max_selections ?? '';
            $this->groupMin       = $g->min_selections;
            $this->groupSort      = $g->sort_order;
        }
        $this->showGroupModal = true;
    }

    public function closeGroupModal(): void
    {
        $this->showGroupModal = false;
        $this->resetGroupForm();
    }

    private function resetGroupForm(): void
    {
        $this->editingGroupId = null;
        $this->groupName      = '';
        $this->groupDesc      = '';
        $this->groupRequired  = false;
        $this->groupMultiple  = true;
        $this->groupMax       = '';
        $this->groupMin       = 0;
        $this->groupSort      = 0;
        $this->resetValidation();
    }

    public function saveGroup(): void
    {
        $this->validate([
            'groupName' => 'required|string|min:2|max:80',
            'groupMin'  => 'required|integer|min:0',
            'groupMax'  => 'nullable|integer|min:1',
        ], [
            'groupName.required' => 'El nombre del grupo es obligatorio.',
        ]);

        $data = [
            'name'           => $this->groupName,
            'description'    => $this->groupDesc ?: null,
            'required'       => $this->groupRequired,
            'multiple'       => $this->groupMultiple,
            'max_selections' => $this->groupMax !== '' ? (int) $this->groupMax : null,
            'min_selections' => (int) $this->groupMin,
            'sort_order'     => $this->groupSort,
            'is_active'      => true,
        ];

        if ($this->editingGroupId) {
            CustomizationGroup::findOrFail($this->editingGroupId)->update($data);
            $msg = '✓ Grupo actualizado.';
        } else {
            CustomizationGroup::create($data);
            $msg = '✓ Grupo creado.';
        }

        $this->closeGroupModal();
        $this->dispatch('show-notification', ['type' => 'success', 'message' => $msg]);
    }

    public function toggleGroupActive(int $id): void
    {
        $g = CustomizationGroup::findOrFail($id);
        $g->update(['is_active' => !$g->is_active]);
        $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Estado actualizado.']);
    }

    public function deleteGroup(int $id): void
    {
        $g = CustomizationGroup::findOrFail($id);
        // Solo eliminar si no tiene opciones usadas en pedidos
        $hasOrders = $g->options()
            ->whereHas('orderItemCustomizations')
            ->exists();

        if ($hasOrders) {
            $this->dispatch('show-notification', [
                'type'    => 'error',
                'message' => 'No se puede eliminar: tiene opciones usadas en pedidos. Desactivalo en su lugar.',
            ]);
            return;
        }

        $g->delete();
        $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Grupo eliminado.']);
    }

    // ==========================================
    // MODAL OPCIÓN
    // ==========================================

    public function openOptionModal(?int $id = null): void
    {
        $this->resetOptionForm();
        if ($id) {
            $opt = CustomizationOption::findOrFail($id);
            $this->editingOptionId = $id;
            $this->optionName      = $opt->name;
            $this->optionPrice     = (float) $opt->price;
            $this->optionSort      = $opt->sort_order;
        } else {
            // Auto sort_order = último + 1
            $last = CustomizationOption::where('customization_group_id', $this->selectedGroupId)
                ->max('sort_order') ?? 0;
            $this->optionSort = $last + 1;
        }
        $this->showOptionModal = true;
    }

    public function closeOptionModal(): void
    {
        $this->showOptionModal = false;
        $this->resetOptionForm();
    }

    private function resetOptionForm(): void
    {
        $this->editingOptionId = null;
        $this->optionName      = '';
        $this->optionPrice     = 0;
        $this->optionSort      = 0;
        $this->resetValidation();
    }

    public function saveOption(): void
    {
        $this->validate([
            'optionName'  => 'required|string|min:2|max:80',
            'optionPrice' => 'required|numeric|min:0',
        ], [
            'optionName.required' => 'El nombre de la opción es obligatorio.',
            'optionPrice.min'     => 'El precio no puede ser negativo.',
        ]);

        $data = [
            'customization_group_id' => $this->selectedGroupId,
            'name'                   => $this->optionName,
            'price'                  => (float) $this->optionPrice,
            'sort_order'             => $this->optionSort,
            'is_active'              => true,
        ];

        if ($this->editingOptionId) {
            CustomizationOption::findOrFail($this->editingOptionId)->update($data);
            $msg = '✓ Opción actualizada.';
        } else {
            CustomizationOption::create($data);
            $msg = '✓ Opción agregada.';
        }

        $this->closeOptionModal();
        $this->dispatch('show-notification', ['type' => 'success', 'message' => $msg]);
    }

    public function toggleOptionActive(int $id): void
    {
        $opt = CustomizationOption::findOrFail($id);
        $opt->update(['is_active' => !$opt->is_active]);
        $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Estado actualizado.']);
    }

    public function deleteOption(int $id): void
    {
        $opt = CustomizationOption::findOrFail($id);

        if ($opt->orderItemCustomizations()->exists()) {
            $this->dispatch('show-notification', [
                'type'    => 'error',
                'message' => 'No se puede eliminar: fue usada en pedidos.',
            ]);
            return;
        }

        $opt->delete();
        $this->dispatch('show-notification', ['type' => 'success', 'message' => '✓ Opción eliminada.']);
    }

    // ==========================================
    // MODAL ASIGNACIÓN A PRODUCTOS
    // ==========================================

    public function openAssignModal(int $groupId): void
    {
        $this->assigningGroupId   = $groupId;
        $this->filterCategoryId   = null;
        // Cargar productos ya asignados
        $group = CustomizationGroup::with('products')->findOrFail($groupId);
        $this->selectedProductIds = $group->products->pluck('id')->toArray();
        $this->showAssignModal    = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal  = false;
        $this->assigningGroupId = null;
        $this->selectedProductIds = [];
    }

    public function toggleProductAssign(int $productId): void
    {
        if (in_array($productId, $this->selectedProductIds)) {
            $this->selectedProductIds = array_values(
                array_filter($this->selectedProductIds, fn($id) => $id !== $productId)
            );
        } else {
            $this->selectedProductIds[] = $productId;
        }
    }

    public function saveAssign(): void
    {
        $group = CustomizationGroup::findOrFail($this->assigningGroupId);

        // Sync con sort_order = 0 para todos
        $sync = collect($this->selectedProductIds)
            ->mapWithKeys(fn($id) => [$id => ['sort_order' => 0]])
            ->toArray();

        $group->products()->sync($sync);

        $count = count($this->selectedProductIds);
        $this->closeAssignModal();
        $this->dispatch('show-notification', [
            'type'    => 'success',
            'message' => "✓ Asignado a {$count} producto(s).",
        ]);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function getSelectedGroupProperty(): ?CustomizationGroup
    {
        if (!$this->selectedGroupId) return null;
        return CustomizationGroup::with(['options'])->find($this->selectedGroupId);
    }

    public function getAssigningGroupProperty(): ?CustomizationGroup
    {
        if (!$this->assigningGroupId) return null;
        return CustomizationGroup::find($this->assigningGroupId);
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $groups = CustomizationGroup::withCount(['options', 'products'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedGroup  = $this->selectedGroup;
        $assigningGroup = $this->assigningGroup;

        // Productos para el modal de asignación
        $assignProducts = collect();
        if ($this->showAssignModal) {
            $assignProducts = Product::with('category')
                ->where('is_active', true)
                ->when($this->filterCategoryId, fn($q) => $q->where('category_id', $this->filterCategoryId))
                ->orderBy('name')
                ->get();
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.customizations', [
            'groups'         => $groups,
            'selectedGroup'  => $selectedGroup,
            'assigningGroup' => $assigningGroup,
            'assignProducts' => $assignProducts,
            'categories'     => $categories,
        ])->layout('components.layouts.admin', ['title' => 'Complementos']);
    }
}