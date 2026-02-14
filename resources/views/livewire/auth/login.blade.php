<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-500 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 space-y-6">

        <div class="text-center">
            <h2 class="text-4xl font-extrabold text-gray-900">🍇 Taskinho</h2>
            <p class="mt-2 text-sm text-gray-500">Ingresá a tu cuenta</p>
        </div>

        @if(session('status'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit.prevent="login" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                <input wire:model="email" type="email" autocomplete="email" required
                    placeholder="tu@correo.com"
                    class="w-full px-4 py-3 border-2 rounded-xl text-sm focus:outline-none transition-all
                        @error('email') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña</label>
                <input wire:model="password" type="password" autocomplete="current-password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 border-2 rounded-xl text-sm focus:outline-none transition-all
                        @error('password') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input wire:model="remember" type="checkbox"
                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    Recordarme
                </label>
                <a href="{{ route('password.request') }}"
                    class="text-sm font-medium text-purple-600 hover:text-purple-700">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit"
                wire:loading.attr="disabled"
                class="w-full py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold text-sm transition-all disabled:opacity-60">
                <span wire:loading.remove>Iniciar Sesión</span>
                <span wire:loading>Ingresando...</span>
            </button>

            <p class="text-center text-sm text-gray-500">
                ¿No tenés cuenta?
                <a href="{{ route('register') }}" class="font-bold text-purple-600 hover:text-purple-700">
                    Registrate aquí
                </a>
            </p>

        </form>
    </div>
</div>