<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Contactos de WhatsApp</h1>
            <p class="text-slate-500 text-sm">Consulta los contactos de tu cuenta de WhatsApp y descárgalos en CSV.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="fetchContacts(false)" wire:loading.attr="disabled" wire:target="fetchContacts" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-indigo-700 transition-all active:scale-95 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"></path></svg>
                Obtener contactos
            </button>
            @if($hasFetched)
                <button wire:click="refreshContacts" wire:loading.attr="disabled" wire:target="refreshContacts" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all active:scale-95 gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M20 8A8 8 0 006 8M4 16a8 8 0 0014 0"></path></svg>
                    Actualizar contactos
                </button>
            @endif
        </div>
    </div>

    @if($errorMessage)
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-medium">{{ $errorMessage }}</span>
        </div>
    @endif

    @if($successMessage && !$errorMessage)
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-medium">{{ $successMessage }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl flex items-center gap-3 shadow-sm">
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">WhatsApp conectado</h3>
        </div>

        @if(!empty($account))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Instancia</p>
                    <p class="text-sm font-semibold text-slate-900 mt-1">{{ $account['instance_name'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Teléfono</p>
                    <p class="text-sm font-semibold text-slate-900 mt-1">{{ $account['phone'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</p>
                    <p class="mt-1">
                        @php
                            $status = $account['status'] ?? 'unknown';
                            $statusLabel = match($status) {
                                'connected' => 'Conectado',
                                'connecting' => 'Conectando',
                                'disconnected' => 'Desconectado',
                                'error' => 'Error',
                                default => ucfirst($status),
                            };
                            $statusClasses = match($status) {
                                'connected' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'connecting' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'disconnected', 'error' => 'bg-red-100 text-red-700 border-red-200',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClasses }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'connected' ? 'bg-emerald-600' : ($status === 'connecting' ? 'bg-amber-500 animate-pulse' : 'bg-red-500') }} mr-1.5"></span>
                            {{ $statusLabel }}
                        </span>
                    </p>
                </div>
                <div class="md:col-span-3">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Servidor</p>
                    <p class="text-xs font-mono text-slate-600 mt-1 break-all">{{ $account['server_url'] ?? '—' }}</p>
                </div>
            </div>
        @else
            <p class="text-sm text-slate-500 pt-4">No hay cuenta de WhatsApp configurada. Configúrala desde Configuración → WhatsApp.</p>
        @endif
    </div>

    @if($hasFetched)
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            @php
                $summaryCards = [
                    ['label' => 'Total encontrados', 'value' => $summary['found'], 'color' => 'text-slate-600', 'bg' => 'bg-slate-50'],
                    ['label' => 'Contactos', 'value' => $summary['personal'], 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                    ['label' => 'Grupos', 'value' => $summary['groups'], 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50'],
                    ['label' => 'Inválidos', 'value' => $summary['invalid'] + ($summary['others'] ?? 0), 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                    ['label' => 'Duplicados', 'value' => $duplicatesFound, 'color' => 'text-red-600', 'bg' => 'bg-red-50'],
                ];
            @endphp
            @foreach($summaryCards as $card)
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold {{ $card['color'] }}">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>

        @if($hasMore)
            <div wire:poll.2s="loadNextPage" class="p-4 bg-indigo-50 border-l-4 border-indigo-500 text-indigo-700 rounded-xl flex items-center gap-3 shadow-sm">
                <svg class="animate-spin w-5 h-5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="text-sm font-medium">
                    Cargando contactos de WhatsApp... <strong>{{ number_format($summary['found']) }}</strong> obtenidos hasta ahora (página {{ $pagesProcessed }}).
                </span>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center gap-2 w-full md:w-96">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, push name o teléfono..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-semibold text-slate-400 uppercase">Filtro:</span>
                    <div class="flex p-1 bg-slate-100 rounded-xl">
                        <button wire:click="$set('filter', 'all')" class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all {{ $filter === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Todos</button>
                        <button wire:click="$set('filter', 'contactos')" class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all {{ $filter === 'contactos' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Contactos</button>
                        <button wire:click="$set('filter', 'grupos')" class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all {{ $filter === 'grupos' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Grupos</button>
                        <button wire:click="$set('filter', 'invalidos')" class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all {{ $filter === 'invalidos' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Inválidos</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Push Name</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Teléfono</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Remote JID</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($visibleContacts as $index => $contact)
                            @php
                                $globalIndex = (($page - 1) * $perPage) + $index + 1;
                                $class = $contact['class'] ?? 'invalid';
                                $classLabel = match($class) {
                                    'personal' => 'Contacto',
                                    'group' => 'Grupo',
                                    'broadcast' => 'Broadcast',
                                    'newsletter' => 'Newsletter',
                                    'device' => 'Dispositivo',
                                    default => 'Inválido',
                                };
                                $classBadge = match($class) {
                                    'personal' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'group' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                    default => 'bg-amber-100 text-amber-700 border-amber-200',
                                };
                                $classDot = match($class) {
                                    'personal' => 'bg-emerald-600',
                                    'group' => 'bg-indigo-600',
                                    default => 'bg-amber-500',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-3 text-sm text-slate-400 font-semibold">{{ $globalIndex }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-slate-900">{{ $contact['name'] ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-700">{{ $contact['push_name'] ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm font-mono text-slate-700">{{ $contact['phone'] ?? '—' }}</td>
                                <td class="px-6 py-3 text-xs font-mono text-slate-500 max-w-[220px] truncate" title="{{ $contact['remote_jid'] ?? '' }}">{{ $contact['remote_jid'] ?? '—' }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600">{{ $contact['type'] ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $classBadge }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $classDot }} mr-1.5"></span>
                                        {{ $classLabel }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <p class="text-slate-900 font-semibold">Sin resultados</p>
                                        <p class="text-slate-500 text-sm">Ajusta el filtro o la búsqueda para ver contactos.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($totalFiltered > 0)
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 flex items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">
                        Mostrando <strong>{{ (($page - 1) * $perPage) + 1 }}</strong>–<strong>{{ min($page * $perPage, $totalFiltered) }}</strong> de <strong>{{ number_format($totalFiltered) }}</strong> contactos
                    </p>
                    <div class="flex items-center gap-2">
                        <button wire:click="goToPage({{ $page - 1 }})" @disabled($page <= 1) class="px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">Anterior</button>
                        <span class="text-xs font-semibold text-slate-600">Página {{ $page }} / {{ $maxPage }}</span>
                        <button wire:click="goToPage({{ $page + 1 }})" @disabled($page >= $maxPage) class="px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">Siguiente</button>
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Descargar contactos</h3>
                <p class="text-xs text-slate-500">Genera un CSV con la información actual de tus contactos.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="downloadCsv" wire:loading.attr="disabled" wire:target="downloadCsv" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all active:scale-95 gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Descargar CSV
                </button>
                <button wire:click="downloadPatientsCsv" wire:loading.attr="disabled" wire:target="downloadPatientsCsv" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all active:scale-95 gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Descargar CSV para pacientes
                </button>
            </div>
        </div>
    @endif

    <div wire:loading wire:target="fetchContacts,refreshContacts" class="fixed inset-0 bg-slate-900/40 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 shadow-xl space-y-3 flex flex-col items-center">
            <svg class="animate-spin w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <p class="text-sm font-bold text-slate-700">Obteniendo contactos...</p>
            @if($pagesProcessed > 0)
                <p class="text-xs text-slate-500">Página {{ $pagesProcessed }} procesada</p>
            @endif
        </div>
    </div>
</div>