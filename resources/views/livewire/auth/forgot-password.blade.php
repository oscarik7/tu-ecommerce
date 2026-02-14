<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-500 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 space-y-6">

        <div class="text-center">
            <div class="text-5xl mb-3">🔑</div>
            <h2 class="text-2xl font-extrabold text-gray-900">Recuperar contraseña</h2>
            <p class="mt-2 text-sm text-gray-500">
                Ingresá tu correo y te enviamos un link para resetear tu contraseña.
            </p>
        </div>

        @if($status)
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-4 rounded-xl text-sm text-center">
                <span class="text-xl">✅</span>
                <p class="mt-1 font-medium">{{ $status }}</p>
            </div>
        @else
            <form wire:submit.prevent="sendResetLink" class="space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                    <input wire:model="email" type="email" autocomplete="email" required
                        placeholder="tu@correo.com"
                        class="w-full px-4 py-3 border-2 rounded-xl text-sm focus:outline-none transition-all
                            @error('email') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold text-sm transition-all disabled:opacity-60">
                    <span wire:loading.remove>Enviar link de recuperación</span>
                    <span wire:loading>Enviando...</span>
                </button>

            </form>
        @endif

        <p class="text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" class="font-bold text-purple-600 hover:text-purple-700">
                ← Volver al login
            </a>
        </p>

    </div>
</div>