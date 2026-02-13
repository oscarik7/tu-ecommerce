<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public $search       = '';
    public $filterDoc    = '';   // 'with' | 'without' | ''

    // Modal edición
    public ?int  $editingUserId = null;
    public string $editName      = '';
    public string $editPhone     = '';
    public string $editAddress   = '';
    public string $editDocType   = 'ci';
    public string $editDoc       = '';
    public string $editCompany   = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // MODAL
    // ==========================================

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->editingUserId = $userId;
        $this->editName      = $user->name;
        $this->editPhone     = $user->phone ?? '';
        $this->editAddress   = $user->address ?? '';
        $this->editDocType   = $user->document_type ?? 'ci';
        $this->editDoc       = $user->document ?? '';
        $this->editCompany   = $user->company_name ?? '';
    }

    public function closeEdit(): void
    {
        $this->editingUserId = null;
        $this->resetEditFields();
    }

    public function saveUser(): void
    {
        $this->validate([
            'editName'    => 'required|string|max:255',
            'editPhone'   => 'nullable|string|max:30',
            'editAddress' => 'nullable|string|max:500',
            'editDocType' => 'required|in:ci,ruc',
            'editDoc'     => 'nullable|string|max:20',
            'editCompany' => 'nullable|string|max:255',
        ], [
            'editName.required' => 'El nombre es obligatorio.',
            'editDoc.max'       => 'El documento no puede superar 20 caracteres.',
        ]);

        $user = User::findOrFail($this->editingUserId);

        $user->update([
            'name'          => $this->editName,
            'phone'         => $this->editPhone ?: null,
            'address'       => $this->editAddress ?: null,
            'document_type' => $this->editDocType,
            'document'      => $this->editDoc ?: null,
            'company_name'  => ($this->editDocType === 'ruc' && $this->editCompany)
                                ? $this->editCompany
                                : null,
        ]);

        session()->flash('message', "Cliente {$user->name} actualizado.");
        $this->closeEdit();
    }

    public function clearDocument(int $userId): void
    {
        User::findOrFail($userId)->update([
            'document'      => null,
            'document_type' => 'ci',
            'company_name'  => null,
        ]);
        session()->flash('message', 'Datos de facturación eliminados.');
    }

    private function resetEditFields(): void
    {
        $this->editName    = '';
        $this->editPhone   = '';
        $this->editAddress = '';
        $this->editDocType = 'ci';
        $this->editDoc     = '';
        $this->editCompany = '';
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $users = User::role('customer')
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('name',     'like', '%' . $this->search . '%')
                       ->orWhere('email',  'like', '%' . $this->search . '%')
                       ->orWhere('phone',  'like', '%' . $this->search . '%')
                       ->orWhere('document', 'like', '%' . $this->search . '%')
                )
            )
            ->when($this->filterDoc === 'with',    fn($q) => $q->whereNotNull('document'))
            ->when($this->filterDoc === 'without', fn($q) => $q->whereNull('document'))
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total'        => User::role('customer')->count(),
            'with_doc'     => User::role('customer')->whereNotNull('document')->count(),
            'with_ruc'     => User::role('customer')->where('document_type', 'ruc')->whereNotNull('document')->count(),
        ];

        return view('livewire.admin.users', [
            'users' => $users,
            'stats' => $stats,
        ])->layout('components.layouts.admin', ['title' => 'Clientes']);
    }
}