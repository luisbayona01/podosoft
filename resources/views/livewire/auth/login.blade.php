<div class="flex min-h-screen">
    <!-- Sección Izquierda: Formulario -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 bg-white">
        <div class="w-full max-w-md">
            <!-- Logo/Icono Superior -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-blue-600 text-white mb-6 shadow-xl shadow-blue-200 transform transition-transform hover:scale-105 duration-300">
                    <!-- Icono de Pie/Podología (SVG) -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 12c0-2.5 2-4 5-4s5 1.5 5 4" />
                        <path d="M12 8v8" />
                        <path d="M10 16h4" />
                        <path d="M12 16c-2 0-3 1-3 3s1 3 3 3 3-1 3-3-1-3-3-3Z" />
                        <path d="M12 16v-2" />
                    </svg>
                </div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Bienvenido</h2>
                <p class="text-slate-500 mt-3 text-lg">Acceda a su cuenta de PodoSoft</p>
            </div>

            <!-- Formulario de Login -->
            <form wire:submit.prevent="authenticate" class="space-y-6">
                <div>
                    <label for="email-address" class="block text-sm font-semibold text-slate-700 mb-2">Correo Electrónico</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <input id="email-address" type="email" wire:model="email" required 
                                class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all bg-slate-50/50 focus:bg-white" 
                                placeholder="correo@ejemplo.com">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-semibold text-slate-700">Contraseña</label>
                        <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="relative group" wire:ignore>
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input id="password" name="password" type="password" wire:model="password" 
                                class="block w-full pl-10 pr-10 py-3 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all bg-slate-50/50 focus:bg-white" 
                                placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors">
                            <svg id="eye-icon-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eye-icon-hide" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" type="checkbox" wire:model="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-600"> Recordarme </label>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition-all transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                    <span>Ingresar al Sistema</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>

            <!-- Footer -->
            <div class="text-center mt-12">
                <p class="text-slate-400 text-sm mb-2">
                    &copy; {{ date('Y') }} <span class="font-semibold text-slate-600">PodoSoft</span>. Todos los derechos reservados.
                </p>
                <p class="text-slate-400 text-xs">
                    Powered by <a href="{{ config('company.provider_url') }}" class="text-blue-600 hover:underline font-medium">{{ config('company.provider_name') }}</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Sección Derecha: Imagen y Branding -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
        <img src="/images/podosoft.png" 
             alt="Podología Profesional" 
             class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 hover:scale-110">
        
        <!-- Overlay Gradiente y Contenido -->
        <div class="absolute inset-0 bg-gradient-to-tr from-blue-900/90 via-blue-900/40 to-transparent flex flex-col justify-end p-16 text-white">
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-xs font-medium mb-6 animate-bounce">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    Gestión Clínica Inteligente
                </div>
                <h1 class="text-5xl font-black mb-6 leading-tight tracking-tight">
                    Cuidado experto <br>
                    <span class="text-blue-300">para cada paso.</span>
                </h1>
                <p class="text-xl text-blue-100 max-w-lg leading-relaxed mb-8 font-light">
                    Optimiza tu práctica podológica con la plataforma más completa en gestión de pacientes, citas e historias clínicas.
                </p>
                
                <!-- Mini Card de Feature -->
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 p-6 rounded-2xl max-w-sm shadow-2xl">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-blue-500/30 text-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-white">Seguridad Médica</p>
                            <p class="text-sm text-blue-200">Tus datos y los de tus pacientes encriptados bajo estándares internacionales.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const iconShow = document.getElementById('eye-icon-show');
    const iconHide = document.getElementById('eye-icon-hide');
    if (input.type === 'password') {
        input.type = 'text';
        iconShow.style.display = 'none';
        iconHide.style.display = 'inline';
    } else {
        input.type = 'password';
        iconShow.style.display = 'inline';
        iconHide.style.display = 'none';
    }
}
</script>
