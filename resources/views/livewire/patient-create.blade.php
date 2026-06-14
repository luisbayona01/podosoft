<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Registrar Paciente</h1>
            <p class="text-slate-500 text-sm">Completa la información básica para crear el expediente del paciente.</p>
        </div>
        <a href="{{ route('patients.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all active:scale-95 gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Cancelar
        </a>
    </div>

    @if(session()->has('error'))
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Section: Identification -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Identificación Personal</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Tipo de Documento <span class="text-red-500">*</span></label>
                    <select wire:model="tipo_documento" class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                        <option value="CC">Cédula de Ciudadanía</option>
                        <option value="CE">Cédula de Extranjería</option>
                        <option value="Passport">Pasaporte</option>
                    </select>
                    @error('tipo_documento') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Número de Documento <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="documento" placeholder="Ej: 10203040" class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    @error('documento') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Nombres <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nombre" placeholder="Nombre(s)" class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    @error('nombre') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Apellidos <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="apellido" placeholder="Apellido(s)" class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    @error('apellido') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Sexo <span class="text-red-500">*</span></label>
                    <select wire:model="sexo" class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                        <option value="">Seleccione...</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                    @error('sexo') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Section: Demographics -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Información Demográfica</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha de Nacimiento <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fecha_nacimiento" class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    @error('fecha_nacimiento') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Dirección</label>
                    <input type="text" wire:model="direccion" placeholder="Dirección de residencia" class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    @error('direccion') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Section: Contact -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Información de Contacto</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Teléfono <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="telefono" placeholder="Ej: +57 300..." class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    @error('telefono') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Correo Electrónico</label>
                    <input type="email" wire:model="email" placeholder="correo@ejemplo.com" class="w-full px-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    @error('email') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Legal Consent -->
        <div class="p-6 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-start gap-4">
            <div class="flex items-center h-5">
                <input type="checkbox" wire:model="consentimiento" class="w-5 h-5 rounded text-indigo-600 focus:ring-indigo-500 border-indigo-300">
            </div>
            <div class="space-y-1">
                <label class="text-sm font-bold text-indigo-900">Consentimiento Informado <span class="text-red-500">*</span></label>
                <p class="text-xs text-indigo-700 leading-relaxed">
                    Acepto el consentimiento informado para el tratamiento de datos personales y el inicio del tratamiento podológico según la normativa vigente de protección de datos y salud.
                </p>
            </div>
        </div>
        @error('consentimiento') <span class="block text-red-500 text-xs font-medium px-2">{{ $message }}</span> @enderror

        <!-- Form Actions -->
        <div class="flex justify-end gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-md hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Registrar Paciente
            </button>
        </div>
    </form>
</div>
