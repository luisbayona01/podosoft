<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PodoSoft | Software para Clínicas de Podología</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}">
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <link rel="stylesheet" href="{{ asset('build/assets/app-B9F4AZFd.css') }}">
    <script src="{{ asset('build/assets/app-UyRVujZY.js') }}" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20" /><path d="m17 5-5-3-5 3" /><rect width="20" height="14" x="2" y="5" rx="2" /></svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-slate-900">PodoSoft</span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#caracteristicas" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Características</a>
                    <a href="#planes" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Planes</a>
                    <a href="#contacto" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Contacto</a>
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">Iniciar Sesión</a>
                </div>
                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button class="p-2 text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-100 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-100 rounded-full blur-3xl opacity-50"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-semibold mb-6 animate-fade-in">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                SaaS especializado para Podólogos
            </div>
            <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-tight tracking-tight mb-6">
                Software para <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500">Clínicas de Podología</span>
            </h1>
            <p class="text-lg lg:text-xl text-slate-600 max-w-3xl mx-auto mb-10 leading-relaxed">
                Administra pacientes, historias clínicas, citas y automatiza WhatsApp con IA. La herramienta definitiva para profesionalizar tu práctica podológica.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="https://wa.me/{{ env('APP_WHATSAPP_NUMBER', '573219376071') }}?text=Hola!%20Me%20gustaría%20solicitar%20una%20demo%20de%20PodoSoft" 
                   class="w-full sm:w-auto px-8 py-4 text-lg font-bold text-white bg-indigo-600 rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200 flex items-center justify-center gap-2 transform hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.//9.6 8.38 8.38 0 0 1-7.78-8.38V4l-6 6 6 6v-8.62z"/></svg>
                    Solicitar Demo
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 text-lg font-semibold text-slate-700 bg-white border border-slate-200 rounded-2xl hover:bg-slate-50 transition-all">
                    Iniciar Sesión
                </a>
            </div>
            
            <!-- Preview Image -->
            <div class="mt-16 relative max-w-5xl mx-auto">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-3xl blur opacity-20"></div>
                <div class="relative bg-white border border-slate-200 rounded-3xl shadow-2xl overflow-hidden">
                    <img src="/images/podosoft.png" alt="PodoSoft Dashboard Preview" class="w-full h-auto">
                </div>
            </div>
        </div>
    </section>

    <!-- Características -->
    <section id="caracteristicas" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-indigo-600 font-bold uppercase tracking-widest text-sm mb-3">Funcionalidades</h2>
                <p class="text-4xl font-black text-slate-900 tracking-tight">Todo lo que tu clínica necesita</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:border-indigo-200 transition-all group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="//12" cy="7" r="4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Gestión de Pacientes</h3>
                    <p class="text-slate-600 leading-relaxed">Expedientes digitales completos, datos de contacto y seguimiento detallado de cada paciente.</p>
                </div>
                <!-- Feature 2 -->
                <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:border-indigo-200 transition-all group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Agenda Médica</h3>
                    <p class="text-slate-600 leading-relaxed">Calendario inteligente para organizar citas, evitar solapamientos y optimizar tu tiempo.</p>
                </div>
                <!-- Feature 3 -->
                <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:border-indigo-200 transition-all group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Historias Clínicas</h3>
                    <p class="text-slate-600 leading-relaxed">Registros médicos detallados con mapas podológicos y evolución del tratamiento.</p>
                </div>
                <!-- Feature 4 -->
                <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:border-indigo-200 transition-all group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.//9.6 8.38 8.38 0 0 1-7.78-8.38V4l-6 6 6 6v-8.62z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">WhatsApp con IA</h3>
                    <p class="text-slate-600 leading-relaxed">Automatiza recordatorios de citas y respuestas frecuentes mediante inteligencia artificial.</p>
                </div>
                <!-- Feature 5 -->
                <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:border-indigo-200 transition-all group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Multi-sede</h3>
                    <p class="text-slate-600 leading-relaxed">Gestiona múltiples consultorios desde una sola cuenta con control de accesos.</p>
                </div>
                <!-- Feature 6 -->
                <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:border-indigo-200 transition-all group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Reportes</h3>
                    <p class="text-slate-600 leading-relaxed">Analiza la rentabilidad de tu clínica con reportes financieros y de productividad.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes -->
    <section id="planes" class="py-24 bg-slate-50" x-data="{ annual: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-indigo-600 font-bold uppercase tracking-widest text-sm mb-3">Planes</h2>
                <p class="text-4xl font-black text-slate-900 tracking-tight mb-8">Escale su clínica con PodoSoft</p>
                
                <!-- Toggle Mensual/Anual -->
                <div class="flex items-center justify-center gap-4">
                    <span :class="!annual ? 'text-slate-900 font-bold' : 'text-slate-500'" class="text-sm transition-colors">Mensual</span>
                    <button @click="annual = !annual" class="relative w-14 h-7 bg-slate-200 rounded-full transition-colors focus:outline-none" :class="annual ? 'bg-indigo-600' : 'bg-slate-200'">
                        <div class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full transition-transform duration-200 shadow-sm" :class="annual ? 'translate-x-7' : 'translate-x-0'"></div>
                    </button>
                    <span :class="annual ? 'text-slate-900 font-bold' : 'text-slate-500'" class="text-sm transition-colors">Anual <span class="ml-1 px-2 py-0.5 bg-green-100 text-green-600 text-xs rounded-full font-bold">-20%</span></span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                <!-- Básico -->
                <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex flex-col transition-all hover:shadow-lg hover:-translate-y-1 order-2 md:order-1">
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Básico</h3>
                    <p class="text-slate-500 text-sm mb-6">Para podólogos independientes que inician</p>
                    
                    <div class="mb-8">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-slate-900" x-text="annual ? '$63.200' : '$79.000'"></span>
                            <span class="text-slate-400 text-lg" x-text="annual ? '/mes' : '/mes'"></span>
                        </div>
                        <template x-if="annual">
                            <p class="text-xs text-slate-400 line-through mt-1">$79.000 /mes</p>
                        </template>
                    </div>

                    <ul class="space-y-4 mb-10 flex-grow">
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Hasta 100 pacientes activos
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Gestión de pacientes e historial básico
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Agenda médica con vista semanal
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Historias clínicas estándar
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Registro de cobros y métodos de pago
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Recordatorios manuales por WhatsApp
                        </li>
                    </ul>
                    <a href="https://wa.me/{{ env('APP_WHATSAPP_NUMBER', '573219376071') }}?text=Hola!%20Me%20interesa%20el%20plan%20Basico" 
                       class="w-full py-3 text-center font-semibold text-indigo-600 border border-indigo-600 rounded-xl hover:bg-indigo-50 transition-all">Empezar gratis 14 días</a>
                </div>

                <!-- Profesional -->
                <div class="bg-white p-8 rounded-3xl border-2 border-indigo-600 shadow-xl relative flex flex-col transition-all hover:shadow-2xl hover:-translate-y-2 transform scale-105 z-10 order-1 md:order-2 ring-4 ring-indigo-50">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full uppercase tracking-wider shadow-md">Más popular</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Profesional</h3>
                    <p class="text-slate-500 text-sm mb-6">Para consultorios con flujo medio de pacientes</p>
                    
                    <div class="mb-8">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-slate-900" x-text="annual ? '$111.200' : '$139.000'"></span>
                            <span class="text-slate-400 text-lg" x-text="annual ? '/mes' : '/mes'"></span>
                        </div>
                        <template x-if="annual">
                            <p class="text-xs text-slate-400 line-through mt-1">$139.000 /mes</p>
                        </template>
                    </div>

                    <ul class="space-y-4 mb-10 flex-grow">
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-indigo-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Todo lo del plan Básico
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-indigo-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Pacientes ilimitados
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-indigo-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Mapa gráfico interactivo del pie (SVG)
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-indigo-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Recordatorios AUTOMÁTICOS por WhatsApp
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-indigo-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Agente IA que agenda citas 24/7 por WhatsApp
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-indigo-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Sincronización con Google Calendar
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-indigo-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Inventario de insumos con alertas de stock
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-indigo-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Reportes financieros mensuales
                        </li>
                    </ul>
                    <a href="https://wa.me/{{ env('APP_WHATSAPP_NUMBER', '573219376071') }}?text=Hola!%20Me%20interesa%20el%20plan%20Profesional" 
                       class="w-full py-3 text-center font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">Empezar gratis 14 días</a>
                </div>

                <!-- Empresarial -->
                <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex flex-col transition-all hover:shadow-lg hover:-translate-y-1 order-3">
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Empresarial</h3>
                    <p class="text-slate-500 text-sm mb-6">Para redes de clínicas y centros médicos</p>
                    
                    <div class="mb-8">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-slate-900" x-text="annual ? '$183.200' : '$229.000'"></span>
                            <span class="text-slate-400 text-lg" x-text="annual ? '/mes' : '/mes'"></span>
                        </div>
                        <template x-if="annual">
                            <p class="text-xs text-slate-400 line-through mt-1">$229.000 /mes</p>
                        </template>
                    </div>

                    <ul class="space-y-4 mb-10 flex-grow">
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Todo lo del plan Profesional
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Hasta 5 sedes en una cuenta
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Múltiples podólogos por sede
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Panel administrativo multi-sede
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Facturación electrónica DIAN integrada
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Soporte prioritario 24/7 por WhatsApp
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-600">
                            <svg class="text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Onboarding personalizado
                        </li>
                    </ul>
                    <a href="https://wa.me/{{ env('APP_WHATSAPP_NUMBER', '573219376071') }}?text=Hola!%20Me%20interesa%20el%20plan%20Empresarial" 
                       class="w-full py-3 text-center font-semibold text-indigo-600 border border-indigo-600 rounded-xl hover:bg-indigo-50 transition-all">Contactar ventas</a>
                </div>
            </div>

            <div class="text-center mt-8">
                <p class="text-slate-400 text-xs font-medium">Precios en COP · IVA no incluido</p>
            </div>

            <div class="mt-12 flex flex-wrap justify-center gap-x-8 gap-y-4 text-slate-500 text-sm font-medium">
                <span class="flex items-center gap-2">
                    <svg class="text-green-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Sin tarjeta de crédito para el trial
                </span>
                <span class="flex items-center gap-2">
                    <svg class="text-green-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Cancela cuando quieras
                </span>
                <span class="flex items-center gap-2">
                    <svg class="text-green-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Soporte en español
                </span>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section id="contacto" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-indigo-600 rounded-3xl p-8 lg:p-16 text-white overflow-hidden relative">
                <div class="absolute top-0 right-0 w-1/3 h-full bg-white/10 skew-x-12 translate-x-20"></div>
                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-4xl font-black mb-6">¿Listo para digitalizar tu clínica?</h2>
                        <p class="text-indigo-100 text-lg mb-8 leading-relaxed">
                            Déjanos tus datos y un especialista en gestión podológica se pondrá en contacto contigo para mostrarte cómo PodoSoft puede ayudarte.
                        </p>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2h3a2 2 0 0 1-2.18 2z"/></svg>
                                </div>
                                <span class="font-medium">Atención personalizada vía WhatsApp</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                </div>
                                <span class="font-medium">Soporte técnico especializado</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-2xl text-slate-900">
                        <form class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
                                <input type="text" placeholder="Tu nombre" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Clínica</label>
                                <input type="text" placeholder="Nombre de tu consultorio" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono</label>
                                <input type="tel" placeholder="Ej: +57 300..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Correo</label>
                                <input type="email" placeholder="correo@clinica.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            </div>
                            <button type="button" 
                                    onclick="window.location.href='https://wa.me/{{ env('APP_WHATSAPP_NUMBER', '573219376071') }}?text=Hola!%20Deseo%20más%20información%20sobre%20PodoSoft'"
                                    class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                                Enviar Solicitud
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-2 text-white">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20" /><path d="m17 5-5-3-5 3" /><rect width="20" height="14" x="2" y="5" rx="2" /></svg>
                    </div>
                    <span class="text-lg font-bold">PodoSoft</span>
                </div>
                <div class="flex gap-6 text-sm">
                    <a href="#" class="hover:text-white transition-colors">Términos de Servicio</a>
                    <a href="#" class="hover:text-white transition-colors">Privacidad</a>
                </div>
                <div class="text-sm">
                    &copy; {{ date('Y') }} <span class="text-slate-400">PodoSoft</span>. Desarrollado por <a href="{{ config('company.provider_url') }}" class="text-white font-semibold hover:text-indigo-400 transition-colors">{{ config('company.provider_name') }}</a>. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
