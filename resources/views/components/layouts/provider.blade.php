<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Monitor de Pedidos TV' }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Animación de entrada */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        [wire\\:poll] > * {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}
    
    @livewireScripts
    
    <script>
        // Reproducir sonido cuando llega un nuevo pedido (opcional)
        document.addEventListener('livewire:initialized', () => {
            let previousOrderCount = 0;
            
            Livewire.hook('morph.updated', ({ el, component }) => {
                // Detectar si hay nuevos pedidos
                const currentOrders = component.get('orders');
                if (currentOrders && currentOrders.length > previousOrderCount && previousOrderCount > 0) {
                    // Aquí puedes agregar un sonido de notificación
                    console.log('¡Nuevo pedido recibido!');
                }
                previousOrderCount = currentOrders ? currentOrders.length : 0;
            });
        });
    </script>
</body>
</html>