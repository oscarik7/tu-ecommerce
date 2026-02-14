<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-500 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 space-y-6">

        <div class="text-center">
            <div class="text-5xl mb-3">🔐</div>
            <h2 class="text-2xl font-extrabold text-gray-900">Nueva contraseña</h2>
            <p class="mt-2 text-sm text-gray-500">Elegí una contraseña nueva para tu cuenta.</p>
        </div>

        <form wire:submit.prevent="resetPassword" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                <input wire:model="email" type="email" autocomplete="email" required
                    placeholder="tu@correo.com"
                    class="w-full px-4 py-3 border-2 rounded-xl text-sm focus:outline-none transition-all
                        @error('email') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nueva contraseña</label>
                <input wire:model="password" type="password" autocomplete="new-password" required
                    placeholder="Mínimo 6 caracteres"
                    class="w-full px-4 py-3 border-2 rounded-xl text-sm focus:outline-none transition-all
                        @error('password') border-red-400 bg-red-50 @else border-gray-200 focus:border-purple-500 @enderror">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar nueva contraseña</label>
                <input wire:model="password_confirmation" type="password" autocomplete="new-password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:outline-none transition-all">
            </div>

            <button type="submit"
                wire:loading.attr="disabled"
                class="w-full py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold text-sm transition-all disabled:opacity-60">
                <span wire:loading.remove>Guardar nueva contraseña</span>
                <span wire:loading>Guardando...</span>
            </button>

        </form>

    </div>
</div>