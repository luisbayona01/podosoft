<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('patients.index') }}" class="inline-flex items-center justify-center w-10 h-10 text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all shadow-sm" title="Volver a pacientes">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Importar pacientes</h1>
                <p class="text-slate-500 text-sm">Importa masivamente tus pacientes desde un archivo CSV.</p>
            </div>
        </div>
        <button wire:click="downloadTemplate" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all active:scale-95 gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Descargar plantilla CSV
        </button>
    </div>

    @if($uploadError)
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-medium">{{ $uploadError }}</span>
        </div>
    @endif

    @if($processError)
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-medium">{{ $processError }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl flex items-center gap-3 shadow-sm">
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex p-1 bg-slate-100 rounded-2xl w-fit">
        @php
            $steps = [
                'upload' => 'Paso 1 — Seleccionar archivo',
                'preview' => 'Paso 2 — Vista previa',
                'result' => 'Resultado',
            ];
        @endphp
        @foreach($steps as $key => $label)
            <div class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl transition-all {{ $step === $key ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500' }}">
                <span class="w-5 h-5 inline-flex items-center justify-center rounded-full text-xs {{ $step === $key ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500' }}">{{ array_search($key, array_keys($steps)) + 1 }}</span>
                {{ $label }}
            </div>
        @endforeach
    </div>

    @if($step === 'upload')
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <label class="block">
                <div class="flex flex-col items-center justify-center gap-4 border-2 border-dashed border-slate-300 rounded-2xl p-10 hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-bold text-slate-700">Selecciona tu archivo CSV</p>
                        <p class="text-xs text-slate-500 mt-1">Extensiones permitidas: .csv / .txt — Máximo {{ number_format($maxFileSizeKb) }} KB</p>
                    </div>
                    <input type="file" wire:model="file" accept=".csv,.txt,text/csv,text/plain" class="sr-only">
                    <span class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Buscar archivo
                    </span>
                </div>
            </label>
            @error('file') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror

            @if($fileName)
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $fileName }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($fileSize / 1024, 1) }} KB</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="startOver" class="px-3 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">Quitar</button>
                        <button wire:click="parseFile" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all active:scale-95">
                            Continuar
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </button>
                    </div>
                </div>
            @endif

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Formato esperado</p>
                <p class="text-xs text-slate-600 font-mono">nombre,apellido,telefono,tipo_documento,documento,email,fecha_nacimiento,sexo,direccion</p>
                <p class="text-xs text-slate-500">Solo <strong>nombre</strong>, <strong>apellido</strong> y <strong>teléfono</strong> son obligatorios. El resto puede ir vacío.</p>
            </div>
        </div>
    @endif

    @if($step === 'preview')
        <div class="space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $summaryCards = [
                        ['label' => 'Encontrados', 'value' => $counts['found'], 'color' => 'text-slate-600', 'bg' => 'bg-slate-100'],
                        ['label' => 'Válidos', 'value' => $counts['valid'], 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                        ['label' => 'Con errores', 'value' => $counts['errors'], 'color' => 'text-red-600', 'bg' => 'bg-red-50'],
                        ['label' => 'Duplicados', 'value' => $counts['duplicates'], 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                    ];
                @endphp
                @foreach($summaryCards as $card)
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800">Vista previa</h3>
                        <p class="text-xs text-slate-500">Primeras {{ count($previewRows) }} filas de {{ $fileRows }} registros</p>
                    </div>
                    <button wire:click.prevent="startOver" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Cambiar archivo
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200">
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Apellido</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Teléfono</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Tipo doc.</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Documento</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Nacimiento</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Sexo</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Dirección</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($previewRows as $row)
                                @php
                                    $data = $row['data'];
                                    $status = $row['status'];
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3 text-sm text-slate-400 font-semibold">{{ $row['rowNumber'] }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $data['nombre'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $data['apellido'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $data['telefono'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $data['tipo_documento'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $data['documento'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $data['email'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $data['fecha_nacimiento'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $data['sexo'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500 max-w-[180px] truncate">{{ $data['direccion'] ?? '' }}</td>
                                    <td class="px-4 py-3">
                                        @if($status === 'valid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mr-1.5"></span>
                                                Válido
                                            </span>
                                        @elseif($status === 'error')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200" title="{{ implode(' | ', $row['errors']) }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1.5"></span>
                                                Error
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200" title="{{ $row['message'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                                Duplicado
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-8 text-center text-sm text-slate-500">No se encontraron registros en el archivo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($counts['errors'] > 0 || $counts['duplicates'] > 0)
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-2">
                    <p class="text-sm font-bold text-amber-800">Solo se importarán los {{ $counts['valid'] }} registros válidos.</p>
                    <p class="text-xs text-amber-700">
                        {{ $counts['errors'] }} con errores y {{ $counts['duplicates'] }} duplicados serán omitidos y podrás descargarlos después de la importación.
                    </p>
                </div>
            @endif

            <div class="flex justify-end gap-3">
                <button wire:click="startOver" class="px-6 py-3 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all active:scale-95">Cancelar</button>
                <button wire:click="confirmImport" wire:loading.attr="disabled" wire:target="confirmImport" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-md hover:bg-indigo-700 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Confirmar importación
                </button>
            </div>

            <div wire:loading wire:target="confirmImport" class="fixed inset-0 bg-slate-900/40 z-50 flex items-center justify-center">
                <div class="bg-white rounded-2xl p-6 shadow-xl space-y-3 flex flex-col items-center">
                    <svg class="animate-spin w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <p class="text-sm font-bold text-slate-700">Importando pacientes...</p>
                </div>
            </div>
        </div>
    @endif

    @if($step === 'result')
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">Importación completada</h3>
                        <p class="text-sm text-slate-500">Resumen de la importación de pacientes</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    @php
                        $resultCards = [
                            ['label' => 'Encontrados', 'value' => $summary['found'] ?? 0, 'color' => 'text-slate-600', 'bg' => 'bg-slate-100'],
                            ['label' => 'Importados', 'value' => $summary['imported'] ?? 0, 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                            ['label' => 'Omitidos', 'value' => $summary['omitted'] ?? 0, 'color' => 'text-red-600', 'bg' => 'bg-red-50'],
                            ['label' => 'Con errores', 'value' => $summary['errors_rows'] ?? 0, 'color' => 'text-red-600', 'bg' => 'bg-red-50'],
                            ['label' => 'Duplicados', 'value' => $summary['duplicates'] ?? 0, 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                        ];
                    @endphp
                    @foreach($resultCards as $card)
                        <div class="p-4 {{ $card['bg'] }} rounded-xl border border-slate-200/60">
                            <p class="text-xs font-medium text-slate-500 mb-1">{{ $card['label'] }}</p>
                            <p class="text-2xl font-extrabold {{ $card['color'] }}">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                @if(!empty($summary['failed']))
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl max-h-48 overflow-y-auto space-y-1">
                        @foreach($summary['failed'] as $failed)
                            <p class="text-xs text-red-700 font-mono">Fila {{ $failed['rowNumber'] }}: {{ $failed['message'] }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-wrap justify-end gap-3 mt-6">
                    <button wire:click="startOver" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all active:scale-95 gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Nueva importación
                    </button>
                    @if(!empty($summary['failed']))
                        <a href="{{ route('patients.import') }}" wire:click.prevent="downloadErrors" class="inline-flex items-center px-5 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all active:scale-95 gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Descargar errores
                        </a>
                    @endif
                    <a href="{{ route('patients.index') }}" class="inline-flex items-center px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all active:scale-95 gap-2 shadow-md">
                        Ver pacientes
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>