<x-mail::message>
{{-- Saludo --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# ¡Ups!
@else
# ¡Hola!
@endif
@endif

{{-- Cuerpo --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Botón --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Líneas de cierre --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Despedida --}}
@if (! empty($salutation))
{{ $salutation }}
@else
Saludos,
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-mail::subcopy>
Si tenés problemas para hacer clic en el botón "{{ $actionText }}", copiá y pegá la siguiente URL en tu navegador: [{{ $displayableActionUrl }}]({{ $actionUrl }})
</x-mail::subcopy>
@endisset
</x-mail::message>