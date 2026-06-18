<div class="max-w-4xl mx-auto p-4 md:p-8 bg-gray-50/50 min-h-screen">
    <!-- Stepper Moderno -->
    <div class="mb-12">
        <div class="relative flex items-center justify-center">
            <!-- Línea de fondo -->
            <div class="absolute top-5 left-0 w-full h-0.5 bg-gray-200 -z-10"></div>
            <div class="absolute top-5 left-0 h-0.5 bg-blue-600 transition-all duration-500 -z-10" style="width: {{ ($step - 1) * 33.33 }}%"></div>
            
            <!-- Step 1 -->
            <div class="flex flex-col items-center group w-1/3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-500 font-bold border-2 {{ $step >= 1 ? 'bg-blue-600 border-blue-600 text-white scale-110 shadow-lg shadow-blue-200' : 'bg-white border-gray-300 text-gray-400' }}">
                    @if($step > 1) 
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    @else 1 @endif
                </div>
                <span class="mt-2 text-xs md:text-sm font-medium {{ $step >= 1 ? 'text-blue-700' : 'text-gray-500' }}">Clínica</span>
            </div>
            
            <!-- Step 2 -->
            <div class="flex flex-col items-center group w-1/3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-500 font-bold border-2 {{ $step >= 2 ? 'bg-blue-600 border-blue-600 text-white scale-110 shadow-lg shadow-blue-200' : 'bg-white border-gray-300 text-gray-400' }}">
                    @if($step > 2) 
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    @else 2 @endif
                </div>
                <span class="mt-2 text-xs md:text-sm font-medium {{ $step >= 2 ? 'text-blue-700' : 'text-gray-500' }}">Administrador</span>
            </div>
            
            <!-- Step 3 -->
            <div class="flex flex-col items-center group w-1/3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-500 font-bold border-2 {{ $step >= 3 ? 'bg-blue-600 border-blue-600 text-white scale-110 shadow-lg shadow-blue-200' : 'bg-white border-gray-300 text-gray-400' }}">
                    @if($step > 3) 
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    @else 3 @endif
                </div>
                <span class="mt-2 text-xs md:text-sm font-medium {{ $step >= 3 ? 'text-blue-700' : 'text-gray-500' }}">Confirmación</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden transition-all">
        @if($errors->has('registration'))
            <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-red-700">{{ $errors->first('registration') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($step == 1)
            <div class="p-6 md:p-10">
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Configuración de la Clínica</h2>
                    <p class="text-gray-500 mt-2">Establece la identidad legal y de contacto de tu centro médico.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Sección Identificación -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Identificación Legal</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre de la Clínica <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="clinic_name" placeholder="Ej. Clínica Odontológica DentalCare" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all placeholder:text-gray-400">
                                @error('clinic_name') <p class="text-red-500 text-xs mt-1.5 flex items-center"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg> {{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">NIT / RUT <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="clinic_nit" placeholder="Número de identificación tributaria" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all placeholder:text-gray-400">
                                @error('clinic_nit') <p class="text-red-500 text-xs mt-1.5 flex items-center"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg> {{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Sección Contacto -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            </div>
                            <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Ubicación y Contacto</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo Electrónico <span class="text-red-500">*</span></label>
                                <input type="email" wire:model.live.debounce.300ms="clinic_email" placeholder="clinica@email.com" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all placeholder:text-gray-400">
                                @error('clinic_email') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="clinic_phone" placeholder="Ej. 3001234567" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all placeholder:text-gray-400">
                                @error('clinic_phone') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Ciudad <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="clinic_city" placeholder="Ej. Bogotá" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all placeholder:text-gray-400">
                                @error('clinic_city') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Dirección Física <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="clinic_address" placeholder="Calle 123 # 45-67, Barrio Centro" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all placeholder:text-gray-400">
                                @error('clinic_address') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-12 pt-6 border-t border-gray-100">
                    <button wire:click="nextStep" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center">
                        Siguiente Paso
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </button>
                </div>
            </div>
        @elseif($step == 2)
            <div class="p-6 md:p-10">
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Perfil del Administrador</h2>
                    <p class="text-gray-500 mt-2">Configura la cuenta del usuario principal con permisos totales.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Identidad -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Identidad Personal</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="admin_name" placeholder="Ej. Juan" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                                @error('admin_name') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="admin_apellido" placeholder="Ej. Pérez" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                                @error('admin_apellido') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Documento de Identidad <span class="text-red-500">*</span></label>
                                <input type="text" oninput="this.value = this.value.replace(/[^0-9]/g, '')" wire:model.live.debounce.300ms="admin_document" placeholder="Solo números" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                                @error('admin_document') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Profesional y Seguridad -->
                    <div class="space-y-8">
                        <div class="space-y-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                </div>
                                <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Perfil Profesional</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Número de Licencia <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="admin_licencia" placeholder="Registro profesional" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                                    @error('admin_licencia') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Admin <span class="text-red-500">*</span></label>
                                    <input type="email" wire:model.live.debounce.300ms="admin_email" placeholder="admin@email.com" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                                    @error('admin_email') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono <span class="text-red-500">*</span></label>
                                    <input type="text" oninput="this.value = this.value.replace(/[^0-9]/g, '')" wire:model.live.debounce.300ms="admin_phone" placeholder="Ej. 3001234567" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                                    @error('admin_phone') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </div>
                                <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Seguridad de Cuenta</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ show: false }">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input :type="show ? 'text' : 'password'" wire:model.live.debounce.300ms="admin_password" placeholder="••••••••" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                                        <button @click="show = !show" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L5.636 5.636m12.728 12.728L19.364 19.364" /></svg>
                                        </button>
                                    </div>
                                    @error('admin_password') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar <span class="text-red-500">*</span></label>
                                    <input :type="show ? 'text' : 'password'" wire:model.live.debounce.300ms="admin_password_confirmation" placeholder="••••••••" class="block w-full px-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-12 pt-6 border-t border-gray-100">
                    <button wire:click="prevStep" class="px-8 py-3 bg-white text-gray-600 font-bold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all active:scale-95">Anterior</button>
                    <button wire:click="nextStep" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center">
                        Siguiente Paso
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </button>
                </div>
            </div>
        @elseif($step == 3)
            <div class="p-6 md:p-10">
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-red-800">Por favor corrige los siguientes errores:</p>
                            </div>
                        </div>
                        <ul class="text-sm text-red-700 space-y-1 ml-8">
                            @foreach($errors->all() as $error)
                                <li class="flex items-start flex-wrap">
                                    <span class="mr-2">•</span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @php
                            $hasClinicErrors = $errors->has(['clinic_name', 'clinic_nit', 'clinic_email', 'clinic_phone', 'clinic_city', 'clinic_address']);
                            $hasAdminErrors = $errors->has(['admin_name', 'admin_apellido', 'admin_document', 'admin_licencia', 'admin_email', 'admin_phone', 'admin_password']);
                        @endphp
                        <div class="flex gap-3 mt-4 ml-8">
                            @if($hasClinicErrors)
                                <button type="button" wire:click="goToStep(1)" class="px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-200 transition-colors">
                                    Corregir datos de Clínica
                                </button>
                            @endif
                            @if($hasAdminErrors)
                                <button type="button" wire:click="goToStep(2)" class="px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-lg hover:bg-purple-200 transition-colors">
                                    Corregir datos de Administrador
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mb-8 text-center">
                    @php
                        $allClinicFilled = !empty(trim($clinic_name ?? '')) &&
                                           !empty(trim($clinic_nit ?? '')) &&
                                           !empty(trim($clinic_email ?? '')) &&
                                           !empty(trim($clinic_phone ?? '')) &&
                                           !empty(trim($clinic_city ?? '')) &&
                                           !empty(trim($clinic_address ?? ''));
                        $allAdminFilled = !empty(trim($admin_name ?? '')) &&
                                          !empty(trim($admin_apellido ?? '')) &&
                                          !empty(trim($admin_document ?? '')) &&
                                          !empty(trim($admin_licencia ?? '')) &&
                                          !empty(trim($admin_email ?? '')) &&
                                          !empty(trim($admin_phone ?? '')) &&
                                          !empty(trim($admin_password ?? ''));
                    @endphp
                    @if(!$allClinicFilled || !$allAdminFilled)
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
                            <div class="flex items-center justify-center space-x-2 text-amber-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <span class="font-medium">Faltan datos por completar</span>
                            </div>
                            <p class="text-sm text-amber-600 mt-2 text-center">Por favor completa todos los pasos del formulario.</p>
                            <div class="flex justify-center gap-3 mt-4">
                                @if(!$allClinicFilled)
                                    <button type="button" wire:click="goToStep(1)" class="px-4 py-2 bg-blue-100 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-200 transition-colors">
                                        Ir a Datos de Clínica
                                    </button>
                                @endif
                                @if(!$allAdminFilled)
                                    <button type="button" wire:click="goToStep(2)" class="px-4 py-2 bg-purple-100 text-purple-700 text-sm font-medium rounded-lg hover:bg-purple-200 transition-colors">
                                        Ir a Datos del Administrador
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 text-green-600 rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Revisión Final</h2>
                    <p class="text-gray-500 mt-2">Por favor, verifica que todos los datos sean correctos.</p>
                    @endif
                </div>

                @if($allClinicFilled && $allAdminFilled)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Resumen Clínica -->
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 space-y-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            <h3 class="font-bold text-gray-800">Datos de la Clínica</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-y-3 text-sm">
                            <span class="text-gray-500">Nombre</span><span class="font-semibold text-gray-900 text-right">{{ $clinic_name }}</span>
                            <span class="text-gray-500">NIT</span><span class="font-semibold text-gray-900 text-right">{{ $clinic_nit }}</span>
                            <span class="text-gray-500">Email</span><span class="font-semibold text-gray-900 text-right">{{ $clinic_email }}</span>
                            <span class="text-gray-500">Teléfono</span><span class="font-semibold text-gray-900 text-right">{{ $clinic_phone }}</span>
                            <span class="text-gray-500">Ciudad</span><span class="font-semibold text-gray-900 text-right">{{ $clinic_city }}</span>
                            <span class="text-gray-500 col-span-1">Dirección</span><span class="font-semibold text-gray-900 text-right col-span-1">{{ $clinic_address }}</span>
                        </div>
                    </div>

                    <!-- Resumen Admin -->
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 space-y-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <h3 class="font-bold text-gray-800">Datos del Administrador</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-y-3 text-sm">
                            <span class="text-gray-500">Nombre</span><span class="font-semibold text-gray-900 text-right">{{ $admin_name }} {{ $admin_apellido }}</span>
                            <span class="text-gray-500">Documento</span><span class="font-semibold text-gray-900 text-right">{{ $admin_document }}</span>
                            <span class="text-gray-500">Licencia</span><span class="font-semibold text-gray-900 text-right">{{ $admin_licencia }}</span>
                            <span class="text-gray-500">Email</span><span class="font-semibold text-gray-900 text-right">{{ $admin_email }}</span>
                            <span class="text-gray-500">Teléfono</span><span class="font-semibold text-gray-900 text-right">{{ $admin_phone }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 bg-blue-50 p-5 rounded-2xl border border-blue-100 flex items-start space-x-4">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <p class="text-sm text-blue-700 leading-relaxed">
                        Al finalizar el registro, se crearán automáticamente la entidad de la clínica y tu cuenta de administrador profesional. No podrás cambiar el NIT una vez creado el registro.
                    </p>
                </div>

                <div class="flex justify-between mt-12 pt-6 border-t border-gray-100">
                    <button wire:click="prevStep" class="px-8 py-3 bg-white text-gray-600 font-bold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all active:scale-95">Anterior</button>
                    <button wire:click="register" class="px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 shadow-lg shadow-green-200 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center">
                        Finalizar Registro
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </button>
                </div>
                @endif
            </div>
        @endif
    </div>
</div>
