<?php

namespace App\Livewire\Admin;

use App\Models\PaymentMethod;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentMethods extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $methodId;
    
    public $name;
    public $type = 'bank_transfer';
    public $description;
    public $instructions;
    public $bank_name;
    public $account_number;
    public $account_holder;
    public $ruc;
    public $is_active = true;
    
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string',
        'description' => 'nullable|string',
        'instructions' => 'nullable|string',
        'bank_name' => 'nullable|string',
        'account_number' => 'nullable|string',
        'account_holder' => 'nullable|string',
        'ruc' => 'nullable|string',
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
        $method = PaymentMethod::findOrFail($id);
        
        $this->methodId = $method->id;
        $this->name = $method->name;
        $this->type = $method->type;
        $this->description = $method->description;
        $this->instructions = $method->instructions;
        $this->is_active = $method->is_active;
        
        if ($method->bank_details) {
            $this->bank_name = $method->bank_details['bank'] ?? '';
            $this->account_number = $method->bank_details['account_number'] ?? '';
            $this->account_holder = $method->bank_details['account_holder'] ?? '';
            $this->ruc = $method->bank_details['ruc'] ?? '';
        }
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        $this->validate();

        $bankDetails = null;
        if ($this->type == 'bank_transfer' && $this->bank_name) {
            $bankDetails = [
                'bank' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_holder' => $this->account_holder,
                'ruc' => $this->ruc,
            ];
        }

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'bank_details' => $bankDetails,
            'is_active' => $this->is_active,
        ];

        if ($this->editMode) {
            PaymentMethod::findOrFail($this->methodId)->update($data);
            session()->flash('message', 'Método de pago actualizado correctamente.');
        } else {
            PaymentMethod::create($data);
            session()->flash('message', 'Método de pago creado correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        PaymentMethod::findOrFail($id)->delete();
        session()->flash('message', 'Método de pago eliminado correctamente.');
    }

    public function toggleActive($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->update(['is_active' => !$method->is_active]);
        session()->flash('message', 'Estado del método de pago actualizado.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['name', 'type', 'description', 'instructions', 'bank_name', 'account_number', 'account_holder', 'ruc', 'is_active', 'methodId']);
        $this->type = 'bank_transfer';
    }

    public function render()
    {
        $methods = PaymentMethod::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.admin.payment-methods', [
            'methods' => $methods,
        ])->layout('components.layouts.admin', ['title' => 'Métodos de Pago']);
    }
}