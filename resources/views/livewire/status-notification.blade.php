<div>
    @if($show)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full"
        >
            <div class="bg-white rounded-2xl shadow-2xl border-2 {{ $shopStatus['is_open'] ? 'border-green-400' : 'border-red-400' }} overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            @if($shopStatus['is_open'])
                                <div class="relative">
                                    <span class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-75"></span>
                                    <div class="relative bg-green-500 rounded-full p-3">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            @else
                                <div class="bg-red-500 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">
                                {{ $shopStatus['label'] }} 🍇
                            </h3>
                            <p class="text-sm text-gray-600 mb-2">
                                {{ $shopStatus['hours'] }}
                            </p>
                            @if($shopStatus['is_open'])
                                <a href="https://wa.me/595986150627?text=Hola!%20Quiero%20hacer%20un%20pedido%20de%20Taskinho%20Açaí"
                                   target="_blank"
                                   class="inline-flex items-center gap-2 text-sm font-semibold text-green-600 hover:text-green-700">
                                    <span>¡Haz tu pedido ahora!</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @else
                                <p class="text-sm font-semibold text-red-600">
                                    Puedes hacer pedidos anticipados por WhatsApp 📱
                                </p>
                            @endif
                        </div>
                        <button
                            @click="show = false"
                            class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
