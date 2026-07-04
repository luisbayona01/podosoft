<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Configuración de WhatsApp</h1>
            <p class="text-sm text-slate-500 mt-1">Conecta tu número de WhatsApp para atomitizar el flujo de citas con tis pacientes </p>
        </div>
        @if($account)
        <div class="flex items-center gap-2">
            <span class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium
                {{ $statusColor === 'green' ? 'bg-emerald-100 text-emerald-700' : '' }}
                {{ $statusColor === 'yellow' ? 'bg-amber-100 text-amber-700' : '' }}
                {{ $statusColor === 'red' ? 'bg-red-100 text-red-700' : '' }}
                {{ $statusColor === 'gray' ? 'bg-slate-100 text-slate-700' : '' }}">
                <span class="w-2 h-2 rounded-full
                    {{ $statusColor === 'green' ? 'bg-emerald-500' : '' }}
                    {{ $statusColor === 'yellow' ? 'bg-amber-500' : '' }}
                    {{ $statusColor === 'red' ? 'bg-red-500' : '' }}
                    {{ $statusColor === 'gray' ? 'bg-slate-400' : '' }}"></span>
                {{ $statusText }}
            </span>
        </div>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg flex items-center gap-3 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12" />
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errorMessage)
    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-center gap-3 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <path d="m15 9-6 6M9 9l6 6" />
        </svg>
        {{ $errorMessage }}
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        @if(!$account)
        <div class="p-8 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">WhatsApp no conectado</h3>
            <p class="text-sm text-slate-500 mb-6 max-w-md mx-auto">
                Conecta tu número de WhatsApp para que los pacientes puedan comunicarse contigo a través del agente IA.
            </p>
            <button wire:click="createAndConnect" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-all shadow-sm disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                <span wire:loading.remove wire:target="createAndConnect">Conectar WhatsApp</span>
                <span wire:loading wire:target="createAndConnect">Procesando...</span>
            </button>
        </div>

        @else
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-slate-50 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Estado de Conexión</div>
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full
                            {{ $account->status === 'connected' ? 'bg-emerald-500' : '' }}
                            {{ $account->status === 'connecting' ? 'bg-amber-500 animate-pulse' : '' }}
                            {{ $account->status === 'disconnected' ? 'bg-red-500' : '' }}
                            {{ $account->status === 'error' ? 'bg-red-500' : '' }}
                            {{ $account->status === 'pending' ? 'bg-slate-400' : '' }}">
                        </div>
                        <span class="font-medium text-slate-800 capitalize">{{ $statusText }}</span>
                    </div>
                    @if($account->connected_at)
                    <p class="text-xs text-slate-500 mt-2">
                        Conectado desde: {{ $account->connected_at->format('d/m/Y H:i') }}
                    </p>
                    @endif
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Número</div>
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <span class="font-medium text-slate-800">{{ $statusText ?? 'No conectado' }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">
                        Instancia: {{ $account->instance_name }}
                    </p>
                </div>
            </div>

            @if($account->status === 'connected')
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <span class="text-sm text-emerald-700 font-medium">¡WhatsApp conectado correctamente!</span>
                </div>
                <p class="text-xs text-emerald-600 mt-1">El agente IA está listo para recibir y enviar mensajes.</p>
            </div>
            @endif

            @if($account->status === 'connecting' || $account->status === 'pending')
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    <span class="text-sm text-amber-700 font-medium">Esperando escaneo del código QR</span>
                </div>
                <p class="text-xs text-amber-600 mt-1">Abre WhatsApp en tu teléfono y escanea el código QR.</p>
            </div>
            @endif

            <div class="flex flex-wrap gap-3">
                @if($account->status === 'pending' || $account->status === 'connecting')
                <button wire:click="requestQr" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="5" height="5" x="3" y="3" rx="1" />
                        <rect width="5" height="5" x="16" y="3" rx="1" />
                        <rect width="5" height="5" x="3" y="16" rx="1" />
                    </svg>
                    <span wire:loading.remove wire:target="requestQr">Mostrar QR</span>
                    <span wire:loading wire:target="requestQr">Cargando...</span>
                </button>
                @endif

                @if($account->status === 'connected')
                <button wire:click="refreshConnectionStatus" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                        <path d="M16 16h5v5" />
                    </svg>
                    <span wire:loading.remove wire:target="refreshConnectionStatus">Verificar Conexión</span>
                    <span wire:loading wire:target="refreshConnectionStatus">Verificando...</span>
                </button>

                <button wire:click="reconnect" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                    </svg>
                    <span wire:loading.remove wire:target="reconnect">Reconectar</span>
                    <span wire:loading wire:target="reconnect">Reconectando...</span>
                </button>

                <button wire:click="disconnect" wire:loading.attr="disabled" wire:confirm="¿Desconectar WhatsApp?"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                    Desconectar
                </button>
                @endif

                @if($account->status === 'disconnected' || $account->status === 'error')
                <button wire:click="reconnect" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                    <span wire:loading.remove wire:target="reconnect">Reconectar</span>
                    <span wire:loading wire:target="reconnect">Reconectando...</span>
                </button>
                @endif

                <button wire:click="deleteInstance" wire:loading.attr="disabled" wire:confirm="¿Eliminar esta instancia? Esta acción no se puede deshacer."
                    class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors disabled:opacity-50 ml-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18" />
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                    </svg>
                    Eliminar Instancia
                </button>
            </div>
        </div>
        @endif
    </div>

    @if($showQrModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeQrModal"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Escanea el código QR</h3>
                    <button wire:click="closeQrModal" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                <p class="text-sm text-slate-500 mb-4">
                    Abre WhatsApp en tu teléfono y escanea este código QR para vincular tu número.
                </p>

                <div class="flex justify-center mb-4">
                    @if($qrCodeBase64)
                    <img src="{{ $qrCodeBase64 }}" alt="QR Code" class="w-64 h-64 rounded-xl border border-slate-200">
                    @else
                    <div class="w-64 h-64 flex items-center justify-center bg-slate-100 rounded-xl">
                        <p class="text-slate-500 text-sm">Generando código QR...</p>
                    </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button wire:click="refreshQr" wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-200 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="refreshQr">Actualizar QR</span>
                        <span wire:loading wire:target="refreshQr">Actualizando...</span>
                    </button>
                    <button wire:click="checkConnection" wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="checkConnection">Verificar Conexión</span>
                        <span wire:loading wire:target="checkConnection">Verificando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>