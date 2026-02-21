<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\StoreSetting;

class ExchangeRateSettings extends Component
{
    public $exchangeRateBrl;

    public function mount(): void
    {
        $this->exchangeRateBrl = StoreSetting::exchangeRateBrl();
    }

    public function save(): void
    {
        $this->validate([
            'exchangeRateBrl' => 'required|numeric|min:1|max:10000',
        ], [
            'exchangeRateBrl.required' => 'Ingresá la cotización.',
            'exchangeRateBrl.min' => 'La cotización debe ser mayor a 0.',
            'exchangeRateBrl.max' => 'La cotización no puede ser mayor a 10.000.',
        ]);

        try {
            StoreSetting::set('exchange_rate_brl', $this->exchangeRateBrl, 'integer');
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => '✓ Cotización actualizada correctamente.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.exchange-rate-settings')
            ->layout('components.layouts.admin', ['title' => 'Cotización']);
    }
}