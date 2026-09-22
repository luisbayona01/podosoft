<div class="max-w-3xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-3 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="text-blue-600">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                <line x1="16" x2="16" y1="2" y2="6" />
                <line x1="8" x2="8" y1="2" y2="6" />
                <line x1="3" x2="21" y1="10" y2="10" />
            </svg>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Google Calendar</h1>
                <p class="text-sm text-slate-500">Conecta el calendario de Google de tu clínica</p>
            </div>
        </div>

        @if (session('message'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm border border-emerald-200">
                {{ session('message') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex items-center gap-2 mb-6">
            <span class="font-semibold text-slate-700">Estado:</span>
            @if ($statusColor === 'green')
                <span class="inline-flex items-center gap-1.5 text-emerald-600 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Conectado
                </span>
            @elseif ($statusColor === 'yellow')
                <span class="inline-flex items-center gap-1.5 text-amber-600 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> {{ $statusText }}
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-slate-500 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> No conectado
                </span>
            @endif
        </div>

        @if (!$connection)
            <a href="{{ route('google.calendar.redirect') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21.35 11.1H12v3.9h5.35c-.45 2.35-2.6 3.9-5.35 3.9-3.2 0-5.8-2.6-5.8-5.9s2.6-5.9 5.8-5.9c1.4 0 2.65.5 3.6 1.4l2.75-2.75C16.5 3.7 14.5 2.8 12 2.8 6.9 2.8 2.8 6.9 2.8 12s4.1 9.2 9.2 9.2c5.3 0 8.8-3.7 8.8-9 0-.25-.05-.5-.1-.75z"/>
                </svg>
                Conectar Google Calendar
            </a>
            <p class="mt-3 text-xs text-slate-500">
                Serás redirigido a Google para autorizar el acceso a tu calendario.
            </p>
        @else
            <div class="space-y-3 mb-6">
                <div>
                    <span class="text-sm text-slate-500">Cuenta:</span>
                    <span class="font-medium text-slate-800">{{ $connection->google_email ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-sm text-slate-500">Calendario:</span>
                    <span class="font-medium text-slate-800">{{ $connection->calendar_id ?: 'primary' }}</span>
                </div>
                <div>
                    <span class="text-sm text-slate-500">Conectado desde:</span>
                    <span class="font-medium text-slate-800">{{ $connection->connected_at?->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            @if ($testResult)
                @php [$type, $msg] = explode(':', $testResult, 2); @endphp
                <div class="mb-4 p-3 rounded-lg text-sm border {{ $type === 'ok' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                    {{ $msg }}
                </div>
            @endif

            <div class="flex items-center gap-3">
                <button wire:click="test" wire:loading.attr="disabled"
                    class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-medium hover:bg-slate-200 transition">
                    <span wire:loading.remove wire:target="test">Probar conexión</span>
                    <span wire:loading wire:target="test">Probando...</span>
                </button>

                <form method="POST" action="{{ route('google.calendar.disconnect') }}"
                    onsubmit="return confirm('¿Desconectar Google Calendar? Las citas de PodoSoft no se eliminarán.')">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2.5 bg-red-50 text-red-600 rounded-xl font-medium hover:bg-red-100 transition">
                        Desconectar
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
