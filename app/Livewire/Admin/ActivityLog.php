<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $filterLog = '';   // 'pedidos' | 'productos' | 'caja' | 'egresos' | 'empleados' | 'inventario'
    public string $filterDate = '';  // 'today' | 'week' | 'month'

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingFilterLog(): void { $this->resetPage(); }
    public function updatingFilterDate(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search     = '';
        $this->filterLog  = '';
        $this->filterDate = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Activity::with('causer')
            ->when($this->filterLog, fn($q) => $q->inLog($this->filterLog))
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('description', 'like', '%' . $this->search . '%')
                       ->orWhereHas('causer', fn($q3) =>
                           $q3->where('name', 'like', '%' . $this->search . '%')
                       )
                )
            )
            ->when($this->filterDate === 'today', fn($q) => $q->whereDate('created_at', today()))
            ->when($this->filterDate === 'week',  fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->filterDate === 'month', fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->latest()
            ->paginate(30);

        // Stats rápidas
        $stats = [
            'today'  => Activity::whereDate('created_at', today())->count(),
            'week'   => Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total'  => Activity::count(),
        ];

        return view('livewire.admin.activity-log', [
            'activities' => $query,
            'stats'      => $stats,
        ])->layout('components.layouts.admin', ['title' => 'Historial de Actividad']);
    }
}
