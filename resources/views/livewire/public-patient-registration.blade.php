<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <div class="container mx-auto px-4 py-8 max-w-lg">
        @if($tenantConfig)
        <div class="text-center mb-8">
            @if(!empty($tenantConfig['logo']))
            <img src="{{ Storage::url($tenantConfig['logo']) }}" alt="{{ $tenantConfig['nombre'] }}" class="h-16 mx-auto mb-4 object-contain">
            @endif
            <h1 class="text-2xl font-bold text-slate-800">{{ $tenantConfig['nombre'] ?? 'Clínica' }}</h1>
            <p class="text-slate-500 text-sm mt-1">Registro de Paciente</p>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">Paso {{ $step['current'] }} de {{ $step['total'] }}</span>
                    <span class="text-sm text-slate-400">{{ round(($step['current'] / $step['total']) * 100) }}%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ ($step['current'] / $step['total']) * 100 }}%"></div>
                </div>
            </div>

            @if($errorMessage)
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-red-700 text-sm">{{ $errorMessage }}</p>
            </div>
            @endif

            @if($successMessage)
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-green-700 text-sm">{{ $successMessage }}</p>
                <p class="text-green-600 text-xs mt-2">Recibirás un mensaje de WhatsApp para agendar tu cita.</p>
            </div>
            @else
            <form wire:submit={{ $step['current'] === $step['total'] ? 'submit' : 'nextStep' }}>
                @csrf

                @if($step['current'] === 1)
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Documento de Identidad</h2>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Documento</label>
                        <select wire:model="tipo_documento" class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Seleccione...</option>
                            @foreach($tiposDocumento as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('tipo_documento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Número de Documento</label>
                        <input type="text" wire:model="documento" class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="123456789">
                        @error('documento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                @elseif($step['current'] === 2)
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Datos Personales</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                            <input type="text" wire:model="nombre" class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Juan">
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Apellido</label>
                            <input type="text" wire:model="apellido" class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Pérez">
                            @error('apellido') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp</label>
                        <input type="text" wire:model="telefono" class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="3001234567">
                        @error('telefono') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Correo (opcional)</label>
                        <input type="email" wire:model="email" class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="juan@email.com">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Nacimiento (opcional)</label>
                        <input type="date" wire:model="fecha_nacimiento" class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('fecha_nacimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                @elseif($step['current'] === 3)
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Confirmación</h2>

                    <div class="bg-slate-50 rounded-xl p-4 text-sm">
                        <p class="text-slate-600 mb-2"><strong>Documento:</strong> {{ $tipo_documento }} {{ $documento }}</p>
                        <p class="text-slate-600 mb-2"><strong>Nombre:</strong> {{ $nombre }} {{ $apellido }}</p>
                        <p class="text-slate-600 mb-2"><strong>WhatsApp:</strong> {{ $telefono }}</p>
                        @if($email)
                        <p class="text-slate-600 mb-2"><strong>Correo:</strong> {{ $email }}</p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="consentimiento" class="mt-1 rounded border-slate-400 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-600">
                                Acepto el tratamiento de mis datos personales según la política de privacidad. 
                                <a href="#" class="text-indigo-600 hover:underline">Ver política</a>
                            </span>
                        </label>
                        @error('consentimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled" class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors disabled:opacity-50">
                        <span wire:loading.remove>Completar Registro</span>
                        <span wire:loading>Procesando...</span>
                    </button>
                </div>
                @endif

                <div class="flex gap-3 mt-6">
                    @if($step['current'] > 1)
                    <button type="button" wire:click="previousStep" class="flex-1 py-3 bg-slate-100 text-slate-700 font-medium rounded-xl hover:bg-slate-200 transition-colors">
                        Atrás
                    </button>
                    @endif

                    @if($step['current'] < $step['total'])
                    <button type="submit" class="flex-1 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                        Continuar
                    </button>
                    @endif
                </div>
            </form>
            @endif
        </div>

        <p class="text-center text-slate-400 text-xs mt-6">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Inicia sesión</a>
        </p>
    </div>
</div>