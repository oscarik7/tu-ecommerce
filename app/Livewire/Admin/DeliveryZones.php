<?php

namespace App\Livewire\Admin;

use App\Models\DeliveryZone;
use Livewire\Component;
use Livewire\WithPagination;

class DeliveryZones extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $zoneId;
    
    public $name;
    public $city = 'Ciudad del Este';
    public $delivery_cost;
    public $description;
    public $is_active = true;
    
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'delivery_cost' => 'required|numeric|min:0',
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
        $zone = DeliveryZone::findOrFail($id);
        
        $this->zoneId = $zone->id;
        $this->name = $zone->name;
        $this->city = $zone->city;
        $this->delivery_cost = $zone->delivery_cost;
        $this->description = $zone->description;
        $this->is_active = $zone->is_active;
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'city' => $this->city,
            'delivery_cost' => $this->delivery_cost,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->editMode) {
            DeliveryZone::findOrFail($this->zoneId)->update($data);
            session()->flash('message', 'Zona de delivery actualizada correctamente.');
        } else {
            DeliveryZone::create($data);
            session()->flash('message', 'Zona de delivery creada correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        DeliveryZone::findOrFail($id)->delete();
        session()->flash('message', 'Zona de delivery eliminada correctamente.');
    }

    public function toggleActive($id)
    {
        $zone = DeliveryZone::findOrFail($id);
        $zone->update(['is_active' => !$zone->is_active]);
        session()->flash('message', 'Estado de la zona actualizado.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['name', 'city', 'delivery_cost', 'description', 'is_active', 'zoneId']);
        $this->city = 'Ciudad del Este';
    }

    public function render()
    {
        $zones = DeliveryZone::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('city', 'like', '%' . $this->search . '%');
            })
            ->orderBy('delivery_cost', 'asc')
            ->paginate(15);

        return view('livewire.admin.delivery-zones', [
            'zones' => $zones,
        ])->layout('components.layouts.admin', ['title' => 'Zonas de Delivery']);
    }
}