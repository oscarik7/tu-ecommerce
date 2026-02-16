<?php

namespace App\Livewire\Admin;

use App\Models\StoreSetting;
use Livewire\Component;

class StoreSettings extends Component
{
    // ── Teléfono ──────────────────────────────────
    public string $phoneWhatsapp = '';
    public string $phoneDisplay  = '';

    // ── Horario ───────────────────────────────────
    // schedule[day] = ['open' => bool, 'from' => 'HH:MM', 'to' => 'HH:MM']
    public array $schedule = [];

    // Nombres de días para mostrar en UI
    public array $dayNames = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(): void
    {
        $this->phoneWhatsapp = StoreSetting::get('phone_whatsapp', '595986150627');
        $this->phoneDisplay  = StoreSetting::get('phone_display', '+595 986 150627');

        $saved = StoreSetting::schedule();

        // Inicializar los 7 días, usando valores guardados o defaults
        for ($d = 1; $d <= 7; $d++) {
            $this->schedule[$d] = [
                'open' => (bool) ($saved[$d]['open'] ?? ($d !== 1)),
                'from' => $saved[$d]['from'] ?? '13:00',
                'to'   => $saved[$d]['to']   ?? '21:00',
            ];
        }
    }

    // ==========================================
    // ACCIONES
    // ==========================================

    public function save(): void
    {
        $this->validate([
            'phoneWhatsapp'  => 'required|string|max:20',
            'phoneDisplay'   => 'required|string|max:30',
            'schedule.*.from' => 'required|date_format:H:i',
            'schedule.*.to'   => 'required|date_format:H:i',
        ], [
            'phoneWhatsapp.required'   => 'El número de WhatsApp es obligatorio.',
            'phoneDisplay.required'    => 'El número visible es obligatorio.',
            'schedule.*.from.date_format' => 'Formato inválido (HH:MM).',
            'schedule.*.to.date_format'   => 'Formato inválido (HH:MM).',
        ]);

        StoreSetting::set('phone_whatsapp', $this->phoneWhatsapp);
        StoreSetting::set('phone_display',  $this->phoneDisplay);
        StoreSetting::set('schedule', json_encode($this->schedule));

        $this->dispatch('show-notification', [
            'type'    => 'success',
            'message' => '✓ Configuración de tienda guardada.',
        ]);
    }

    // Copiar horario del día anterior a todos los días activos
    public function applyToAll(int $fromDay): void
    {
        $source = $this->schedule[$fromDay];
        for ($d = 1; $d <= 7; $d++) {
            $this->schedule[$d]['from'] = $source['from'];
            $this->schedule[$d]['to']   = $source['to'];
        }
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        return view('livewire.admin.store-settings')
            ->layout('components.layouts.admin', ['title' => 'Configuración de Tienda']);
    }
}   