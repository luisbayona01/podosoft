<div class="space-y-6">
    <!-- Incomplete data banner -->
    @if($patient->isInformationIncomplete())
        <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-800 rounded-xl flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="text-sm font-bold">Información incompleta - actualizar datos del paciente</p>
                    <p class="text-xs text-amber-700">Faltan: {{ implode(', ', $patient->missingImportantFields()) }}</p>
                </div>
            </div>
            <a href="{{ route('patients.edit', $patient->id) }}" class="inline-flex items-center shrink-0 px-3 py-2 text-xs font-bold text-amber-900 bg-white border border-amber-300 rounded-lg hover:bg-amber-100 transition-all gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Actualizar datos
            </a>
        </div>
    @endif

    <!-- Profile Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl font-bold shadow-inner">
                    {{ substr($patient->nombre, 0, 1) }}{{ substr($patient->apellido, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-3xl font-extrabold text-slate-900">{{ $patient->nombre }} {{ $patient->apellido }}</h1>
                        @if($patient->hasRiskAntecedents())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1.5"></span>
                                RIESGO MÉDICO
                            </span>
                        @endif
                    </div>
                    <p class="text-slate-500 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                        {{ $patient->tipo_documento }}: {{ $patient->documento }}
                    </p>
                </div>
            </div>
                <div class="flex gap-3 w-full md:w-auto">
                    <a href="{{ route('patients.index') }}" class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all active:scale-95 gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Volver
                    </a>
                    <a href="{{ route('patients.edit', $patient->id) }}" class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all active:scale-95 gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Editar Ficha
                    </a>
                </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex p-1 bg-slate-100 rounded-2xl w-fit">
        @php
            $tabs = [
                'general' => ['label' => 'Datos Generales', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                'citas' => ['label' => 'Citas', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                'historia' => ['label' => 'Historia Clínica', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                'facturacion' => ['label' => 'Facturación', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
            <button wire:click="setTab('{{ $key }}')" 
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-xl transition-all {{ $activeTab === $key ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"></path></svg>
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    <!-- Tab Content -->
    <div class="animate-in fade-in slide-in-from-bottom-2 duration-300">
        @if($activeTab === 'general')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Personal Info -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <h3 class="font-bold text-slate-800">Información Personal</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Documento</label>
                            <p class="text-slate-900 font-semibold">{{ $patient->tipo_documento }}: {{ $patient->documento }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Teléfono</label>
                            <p class="text-slate-900 font-semibold">{{ $patient->telefono }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Correo Electrónico</label>
                            <p class="text-slate-900 font-semibold">{{ $patient->email ?? 'No registrado' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Demographics -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <h3 class="font-bold text-slate-800">Datos Demográficos</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Fecha de Nacimiento</label>
                            <p class="text-slate-900 font-semibold">{{ $patient->fecha_nacimiento?->format('d/m/Y') ?? 'No registrada' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sexo</label>
                            <p class="text-slate-900 font-semibold">{{ $patient->sexo }}</p>
                        </div>
                    </div>
                </div>

                <!-- Address & Legal -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <h3 class="font-bold text-slate-800">Ubicación y Legal</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Dirección</label>
                            <p class="text-slate-900 font-semibold">{{ $patient->direccion ?? 'No registrada' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Consentimiento</label>
                            <div class="mt-1">
                                @if($patient->consentimiento)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                        ✓ Aceptado el {{ $patient->fecha_consentimiento?->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        ⚠ Pendiente
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'citas')
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <livewire:patient-appointments :patientId="$patient->id" />
            </div>
        @elseif($activeTab === 'historia')
            <div class="space-y-8">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <h3 class="text-lg font-bold text-slate-800">Antecedentes Médicos</h3>
                    </div>
                    <livewire:patient-antecedents :patientId="$patient->id" />
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 00// 10.172V5L8 4z"></path></svg>
                        <h3 class="text-lg font-bold text-slate-800">Historia Clínica Podológica</h3>
                    </div>
                    <livewire:clinical-history-index :patientId="$patient->id" />
                </div>
            </div>
        @elseif($activeTab === 'facturacion')
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <livewire:patient-billing :patientId="$patient->id" />
            </div>
        @endif
    </div>
</div>
