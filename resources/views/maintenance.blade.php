<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $storeName }} - Próximamente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #1e0a36;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1, h2, h3 { font-family: 'Bricolage Grotesque', sans-serif; }

        /* fondo gradiente fijo */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 0%,   #7c3aed33 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 90% 100%, #ec489922 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 50% 50%,  #fb923c11 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.15;
            pointer-events: none;
            z-index: 0;
            animation: drift 20s ease-in-out infinite;
        }
        .blob-1 { width: 500px; height: 500px; background: #7c3aed; top: -150px; left: -150px; animation-delay: 0s; }
        .blob-2 { width: 400px; height: 400px; background: #ec4899; bottom: -100px; right: -100px; animation-delay: -7s; }
        .blob-3 { width: 300px; height: 300px; background: #fb923c; top: 40%;   left: 55%;  animation-delay: -14s; }

        @keyframes drift {
            0%, 100% { transform: translate(0,0) scale(1); }
            33%       { transform: translate(25px,-25px) scale(1.04); }
            66%       { transform: translate(-15px,20px) scale(0.96); }
        }

        /* emojis decorativos (solo desktop) */
        .deco {
            position: fixed;
            pointer-events: none;
            z-index: 0;
            opacity: 0.1;
            animation: floatDeco 9s ease-in-out infinite;
        }
        .deco-1 { font-size: 72px; top: 8%;   left: 4%;   animation-delay: 0s; }
        .deco-2 { font-size: 60px; top: 12%;  right: 5%;  animation-delay: -2s; }
        .deco-3 { font-size: 64px; bottom: 18%; left: 6%;  animation-delay: -4s; }
        .deco-4 { font-size: 56px; bottom: 10%; right: 4%; animation-delay: -6s; }
        .deco-5 { font-size: 48px; top: 48%;  left: 2%;   animation-delay: -3s; }

        @keyframes floatDeco {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50%       { transform: translateY(-22px) rotate(6deg); }
        }

        /* contenido */
        .wrapper {
            position: relative;
            z-index: 1;
            max-width: 640px;
            margin: 0 auto;
            padding: 52px 20px 72px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* logo */
        .logo-ring {
            width: 116px;
            height: 116px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            animation: breathe 4s ease-in-out infinite;
        }
        .logo-ring img { width: 76px; height: 76px; object-fit: contain; }

        @keyframes breathe {
            0%, 100% { box-shadow: 0 0 0 0   rgba(124,58,237,0.35); }
            50%       { box-shadow: 0 0 0 18px rgba(124,58,237,0); }
        }

        /* badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 999px;
            padding: 5px 16px;
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            margin-bottom: 22px;
        }
        .badge-dot {
            width: 7px; height: 7px;
            background: #fbbf24;
            border-radius: 50%;
            animation: pulse-dot 2s cubic-bezier(0.4,0,0.6,1) infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }

        /* titular */
        .headline {
            font-size: clamp(1.9rem, 6vw, 3rem);
            font-weight: 800;
            line-height: 1.15;
            text-align: center;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fff 25%, #c4b5fd 60%, #f9a8d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtext {
            font-size: clamp(0.9rem, 2.5vw, 1.05rem);
            color: rgba(255,255,255,0.6);
            text-align: center;
            line-height: 1.7;
            max-width: 460px;
            margin-bottom: 36px;
        }

        /* fecha */
        .date-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.11);
            border-radius: 18px;
            padding: 18px 40px;
            text-align: center;
            margin-bottom: 36px;
        }
        .date-label {
            font-size: 10px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            margin-bottom: 6px;
        }
        .date-value {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: clamp(1.2rem, 3.5vw, 1.6rem);
            font-weight: 700;
        }

        /* features */
        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            width: 100%;
            margin-bottom: 40px;
        }

        @media (min-width: 480px) {
            .features { grid-template-columns: repeat(4, 1fr); }
        }

        .feature-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 16px;
            padding: 18px 10px;
            text-align: center;
            transition: background 0.2s, transform 0.2s;
            cursor: default;
        }
        .feature-card:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-3px);
        }
        .feature-emoji { font-size: 26px; display: block; margin-bottom: 8px; }
        .feature-text  { font-size: 11.5px; color: rgba(255,255,255,0.7); font-weight: 500; }

        /* divider */
        .divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.13), transparent);
            margin-bottom: 24px;
        }

        /* redes */
        .social-label {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            text-align: center;
            margin-bottom: 16px;
            letter-spacing: 0.05em;
        }
        .socials {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .social-btn {
            width: 46px; height: 46px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.13);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
        }
        .social-btn:hover { background: rgba(255,255,255,0.18); transform: scale(1.1); }
        .social-btn svg { width: 20px; height: 20px; fill: currentColor; }

        /* admin link */
        .admin-link {
            font-size: 11.5px;
            color: rgba(255,255,255,0.28);
            text-decoration: none;
            transition: color 0.2s;
        }
        .admin-link:hover { color: rgba(255,255,255,0.55); }

        /* ocultar blobs/deco en móvil para performance */
        @media (max-width: 480px) {
            .deco { display: none; }
        }
    </style>
</head>
<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <span class="deco deco-1">🍇</span>
    <span class="deco deco-2">🫐</span>
    <span class="deco deco-3">🍓</span>
    <span class="deco deco-4">✨</span>
    <span class="deco deco-5">🥣</span>

    <div class="wrapper">

        <div class="logo-ring">
            <img src="{{ asset('images/logo.png') }}" alt="{{ $storeName }}">
        </div>

        <div class="badge">
            <span class="badge-dot"></span>
            En construcción
        </div>

        <h1 class="headline">{{ $message }}</h1>

        <p class="subtext">
            Estamos preparando una experiencia única con los mejores açaís de la ciudad.
            ¡Muy pronto podrás disfrutarla!
        </p>

        @if($date)
            <div class="date-card">
                <div class="date-label">Lanzamiento estimado</div>
                <div class="date-value">{{ \Carbon\Carbon::parse($date)->format('d \d\e F, Y') }}</div>
            </div>
        @endif

        <div class="features">
            <div class="feature-card">
                <span class="feature-emoji">🛒</span>
                <span class="feature-text">Pedidos Online</span>
            </div>
            <div class="feature-card">
                <span class="feature-emoji">🚚</span>
                <span class="feature-text">Delivery</span>
            </div>
            <div class="feature-card">
                <span class="feature-emoji">💳</span>
                <span class="feature-text">Pagos Fáciles</span>
            </div>
            <div class="feature-card">
                <span class="feature-emoji">⭐</span>
                <span class="feature-text">Promociones</span>
            </div>
        </div>

        @php
            $facebook  = \App\Models\Setting::get('social_facebook');
            $instagram = \App\Models\Setting::get('social_instagram');
            $whatsapp  = \App\Models\Setting::get('social_whatsapp');
        @endphp

        @if($facebook || $instagram || $whatsapp)
            <div class="divider"></div>
            <p class="social-label">Seguinos para enterarte del lanzamiento</p>
            <div class="socials">
                @if($facebook)
                    <a href="{{ $facebook }}" target="_blank" class="social-btn" title="Facebook">
                        <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                @endif
                @if($instagram)
                    <a href="{{ $instagram }}" target="_blank" class="social-btn" title="Instagram">
                        <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                @endif
                @if($whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" class="social-btn" title="WhatsApp">
                        <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                @endif
            </div>
        @endif

        <a href="{{ route('login') }}" class="admin-link">Acceso administrador →</a>

    </div>
</body>
</html>