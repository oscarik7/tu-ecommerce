<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\StoreSetting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Variables globales para el layout app.blade.php ──
        View::composer('components.layouts.app', function ($view) {
            try {
                $storePhone     = StoreSetting::get('phone_whatsapp', '595986150627');
                $storePhoneShow = StoreSetting::get('phone_display', '+595 986 150627');
                $schedule       = StoreSetting::schedule();
            } catch (\Throwable $e) {
                $storePhone     = '595986150627';
                $storePhoneShow = '+595 986 150627';
                $schedule       = [];
            }

            $dayAbbr = [1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb',7=>'Dom'];

            if (empty($schedule)) {
                $schedule = [
                    1 => ['open' => false, 'from' => '13:00', 'to' => '21:00'],
                    2 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    3 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    4 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    5 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    6 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                    7 => ['open' => true,  'from' => '13:00', 'to' => '21:00'],
                ];
            }

            // Asegurar claves enteras
            $schedule = array_combine(
                array_map('intval', array_keys($schedule)),
                array_values($schedule)
            );

            // Construir label de horario para footer
            $groups = [];
            foreach ($schedule as $day => $cfg) {
                if (!($cfg['open'] ?? false)) continue;
                $slot = ($cfg['from'] ?? '13:00') . '-' . ($cfg['to'] ?? '21:00');
                $groups[$slot][] = (int) $day;
            }

            $footerScheduleParts = [];
            foreach ($groups as $slot => $days) {
                sort($days);
                $first = $days[0];
                $last  = end($days);
                $isConsec = ($last - $first + 1 === count($days));
                if ($isConsec && count($days) > 2) {
                    $lbl = $dayAbbr[$first] . ' a ' . $dayAbbr[$last];
                } elseif (count($days) === 1) {
                    $lbl = $dayAbbr[$first];
                } else {
                    $lbl = implode(', ', array_map(fn($d) => $dayAbbr[$d] ?? '', $days));
                }
                $footerScheduleParts[] = "{$lbl}: {$slot}";
            }
            $footerSchedule = implode(' · ', $footerScheduleParts) ?: 'Ver horarios';

            // Días cerrados
            $closedDays = collect($schedule)
                ->filter(fn($c) => !($c['open'] ?? true))
                ->keys()
                ->map(fn($d) => $dayAbbr[(int)$d] ?? '')
                ->filter()
                ->implode(', ');

            $view->with([
                'storePhone'     => $storePhone,
                'storePhoneShow' => $storePhoneShow,
                'footerSchedule' => $footerSchedule,
                'closedDays'     => $closedDays,
            ]);
        });
    }
}