<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $storeName }} - Próximamente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .bounce-slow {
            animation: bounce 2s infinite;
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4 overflow-hidden">
    
    {{-- Elementos decorativos de fondo --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        {{-- Círculos decorativos --}}
        <div class="absolute top-10 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-pink-500/10 rounded-full blur-3xl"></div>
        
        {{-- Emojis flotantes --}}
        <div class="absolute top-20 right-20 text-6xl float opacity-50" style="animation-delay: 0s;">🍇</div>
        <div class="absolute bottom-32 left-20 text-5xl float opacity-50" style="animation-delay: 1s;">🥣</div>
        <div class="absolute top-1/3 right-1/4 text-4xl float opacity-50" style="animation-delay: 2s;">✨</div>
        <div class="absolute bottom-1/4 right-32 text-5xl float opacity-50" style="animation-delay: 0.5s;">🍓</div>
        <div class="absolute top-1/4 left-1/3 text-4xl float opacity-50" style="animation-delay: 1.5s;">🫐</div>
    </div>
    
    {{-- Contenido principal --}}
    <div class="relative z-10 max-w-2xl w-full">
        <div class="glass rounded-3xl p-8 md:p-12 text-center text-white">
            
            {{-- Logo / Icono --}}
            <div class="mb-8">
                <div class="inline-block p-6 rounded-full bg-white/20 mb-4 pulse-slow">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ $storeName }}" class="h-32 w-32 object-contain">
                </div>
            </div>
            
            {{-- Mensaje principal --}}
            <div class="mb-8">
                <div class="inline-flex items-center gap-2 bg-white/20 rounded-full px-6 py-2 mb-6">
                    <span class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse"></span>
                    <span class="text-sm font-medium uppercase tracking-wider">En construcción</span>
                </div>
                
                <h2 class="text-2xl md:text-3xl font-bold mb-4 text-shadow">
                    {{ $message }}
                </h2>
                
                <p class="text-white/80 text-lg max-w-md mx-auto">
                    Estamos preparando una experiencia única con los mejores açaís de la ciudad. 
                    ¡Muy pronto podrás disfrutarla!
                </p>
            </div>
            
            {{-- Fecha estimada si existe --}}
            @if($date)
                <div class="mb-8 glass rounded-2xl p-6 inline-block">
                    <p class="text-white/60 text-sm uppercase tracking-wider mb-2">Fecha estimada de lanzamiento</p>
                    <p class="text-2xl md:text-3xl font-bold">{{ \Carbon\Carbon::parse($date)->format('d \d\e F, Y') }}</p>
                </div>
            @endif
            
            {{-- Features que vienen --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="glass rounded-xl p-4">
                    <span class="text-3xl mb-2 block">🛒</span>
                    <span class="text-sm">Pedidos Online</span>
                </div>
                <div class="glass rounded-xl p-4">
                    <span class="text-3xl mb-2 block">🚚</span>
                    <span class="text-sm">Delivery</span>
                </div>
                <div class="glass rounded-xl p-4">
                    <span class="text-3xl mb-2 block">💳</span>
                    <span class="text-sm">Pagos Fáciles</span>
                </div>
                <div class="glass rounded-xl p-4">
                    <span class="text-3xl mb-2 block">⭐</span>
                    <span class="text-sm">Promociones</span>
                </div>
            </div>
            
            {{-- Redes sociales / Contacto --}}
            <div class="pt-6 border-t border-white/20">
                <p class="text-white/60 text-sm mb-4">Síguenos para enterarte del lanzamiento</p>
                <div class="flex justify-center gap-4">
                    @php
                        $facebook = \App\Models\Setting::get('social_facebook');
                        $instagram = \App\Models\Setting::get('social_instagram');
                        $whatsapp = \App\Models\Setting::get('social_whatsapp');
                    @endphp
                    
                    @if($facebook)
                        <a href="{{ $facebook }}" target="_blank" class="w-12 h-12 glass rounded-full flex items-center justify-center hover:bg-white/30 transition-all hover:scale-110">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    @endif
                    
                    @if($instagram)
                        <a href="{{ $instagram }}" target="_blank" class="w-12 h-12 glass rounded-full flex items-center justify-center hover:bg-white/30 transition-all hover:scale-110">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                            </svg>
                        </a>
                    @endif
                    
                    @if($whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" class="w-12 h-12 glass rounded-full flex items-center justify-center hover:bg-white/30 transition-all hover:scale-110">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Link para admin --}}
        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-white/50 hover:text-white/80 text-sm transition">
                Acceso administrador →
            </a>
        </div>
    </div>
</body>
</html>