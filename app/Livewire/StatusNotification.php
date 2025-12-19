<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class StatusNotification extends Component
{
    public $show = true; // Siempre mostrar

    public function getShopStatus()
    {
        $now = Carbon::now('America/Asuncion');
        $diaSemana = $now->dayOfWeekIso;
        $horaActual = $now->format('H:i');

        $horaApertura = '13:00';
        $horaCierre = '21:00';

        $estaAbierto = ($diaSemana != 1) &&
                       ($horaActual >= $horaApertura) &&
                       ($horaActual < $horaCierre);

        return [
            'is_open' => $estaAbierto,
            'label' => $estaAbierto ? 'Abierto ahora' : 'Cerrado ahora',
            'color' => $estaAbierto ? 'bg-green-500' : 'bg-red-500',
            'hours' => ($diaSemana == 1) ? 'Cerrado los lunes' : "Mar. a Dom.: $horaApertura - $horaCierre"
        ];
    }

    public function render()
    {
        return view('livewire.status-notification', [
            'shopStatus' => $this->getShopStatus()
        ]);
    }
}
