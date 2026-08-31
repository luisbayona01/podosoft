<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'PodoSoft') }} - {{ $title ?? 'Dashboard' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}">
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <link rel="stylesheet" href="{{ asset('build/assets/app-B9F4AZFd.css') }}">
    <script src="{{ asset('build/assets/app-UyRVujZY.js') }}" defer></script>
</head>

<body class="bg-slate-50 font-sans antialiased text-slate-900"
    x-data="{ sidebarOpen: false, aboutOpen: false, sidebarCollapsed: false }">
    <div class="flex h-screen overflow-hidden">


        <!-- MOBILE OVERLAY -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden" x-cloak>
        </div>

        <!-- SIDEBAR -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" :class="sidebarCollapsed ? 'w-20' : 'w-72'"
            class="fixed inset-y-0 left-0 z-50 bg-slate-900 text-slate-300 transition-all duration-300 ease-in-out lg:relative lg:translate-x-0 flex flex-col shadow-2xl lg:shadow-none">

            <!-- Logo Area -->
            <div class="p-4 flex items-center bg-slate-950 border-b border-slate-800/50 overflow-hidden">
                <div class="flex items-center gap-3 transition-all duration-300"
                    :class="sidebarCollapsed ? 'mx-auto' : 'ml-0'">
                    <div
                        class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/20 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20" />
                            <path d="m17 5-5-3-5 3" />
                            <rect width="20" height="14" x="2" y="5" rx="2" />
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" x-transition:enter="transition opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="text-2xl font-extrabold text-white tracking-tight whitespace-nowrap">PodoSoft</span>
                </div>
                <button @click="sidebarCollapsed = !sidebarCollapsed" x-show="!sidebarOpen"
                    class="hidden lg:flex items-center justify-center p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all duration-200 absolute right-4"
                    title="Contraer/Expandir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        :class="sidebarCollapsed ? 'rotate-180' : ''" class="transition-transform duration-300">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto">
                <div>
                    <div x-show="!sidebarCollapsed"
                        class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 px-3">Principal</div>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="11" rx="1" />
                                <rect width="7" height="9" x="3" y="15" rx="1" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Dashboard</span>
                        </a>
                    </div>
                </div>

                <div>
                    <div x-show="!sidebarCollapsed"
                        class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 px-3">Clínica</div>
                    <div class="space-y-1">
                        <a href="{{ route('patients.index') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('patients.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('patients.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Pacientes</span>
                        </a>
                        <a href="{{ route('patients.import') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('patients.import') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('patients.import') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Importar pacientes</span>
                        </a>
                        <a href="{{ route('appointments.index') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('appointments.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('appointments.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <rect width="18" height="18" x="3" y="3" rx="2" />
                                <path d="M3 9h18" />
                                <path d="M9 21V9" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Agenda</span>
                        </a>
                        <a href="{{ route('profesionales.index') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('profesionales.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('profesionales.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Profesionales</span>
                        </a>
                        <a href="{{ route('clinical-history.index') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('clinical-history.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('clinical-history.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Historias Clínicas</span>
                        </a>
                        <a href="{{ route('diagnostico-plantillas') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('diagnostico-plantillas') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('diagnostico-plantillas') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Plantillas de Diagnóstico</span>
                        </a>
                    </div>
                </div>

                <div>
                    <div x-show="!sidebarCollapsed"
                        class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3 px-3">Administración
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('insumos.index') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('insumos.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('insumos.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                <path d="M3 6h18" />
                                <path d="M16 6v14" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Inventario</span>
                        </a>
                        <a href="{{ route('financials.report') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('financials.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('financials.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <line x1="12" x2="12" y1="2" y2="22" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Facturación</span>
                        </a>
                        <a href="{{ route('servicios.index') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('servicios.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('servicios.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <path d="M12 2v20" />
                                <path d="m17 5-5-3-5 3" />
                                <rect width="20" height="14" x="2" y="5" rx="2" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Servicios</span>
                        </a>
                        @php
$whatsAppStatus = \App\Models\TenantWhatsAppAccount::where('tenant_id', auth()->user()->tenant_id ?? 1)->first();
$whatsAppStatusClass = match($whatsAppStatus?->status) {
    'connected' => 'bg-emerald-500',
    'connecting' => 'bg-amber-500 animate-pulse',
    'disconnected' => 'bg-red-500',
    'error' => 'bg-red-500',
    default => 'bg-slate-400'
};
@endphp
                        <a href="{{ route('config.whatsapp') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('config.whatsapp') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="{{ request()->routeIs('config.whatsapp') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 {{ $whatsAppStatusClass }} rounded-full border-2 {{ request()->routeIs('config.whatsapp') ? 'border-blue-600' : 'border-slate-800' }}"></span>
                            </div>
                            <span x-show="!sidebarCollapsed" class="font-medium">WhatsApp</span>
                        </a>
                        <a href="{{ route('whatsapp.contacts') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('whatsapp.contacts') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('whatsapp.contacts') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Contactos</span>
                        </a>
                        <a href="{{ route('config.bot-blocked-contacts') }}"
                            :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                            class="flex items-center py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('config.bot-blocked-contacts') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="{{ request()->routeIs('config.bot-blocked-contacts') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m4.9 4.9 14.2 14.2" />
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="font-medium">Números bloqueados</span>
                        </a>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-800/60">
                    <button @click="aboutOpen = true"
                        :class="sidebarCollapsed ? 'justify-center gap-0 px-0' : 'gap-3 px-3'"
                        class="w-full flex items-center py-2.5 rounded-xl transition-all duration-200 text-slate-400 hover:bg-slate-800 hover:text-white group">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="text-slate-500 group-hover:text-white">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4" />
                            <path d="M12 8h.01" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="font-medium">Acerca de</span>
                    </button>
                </div>
            </nav>


            <!-- Bottom User Profile -->
            <div class="p-4 border-t border-slate-800/60 bg-slate-950/50">
                <div class="flex items-center gap-3 px-3 py-3 rounded-2xl bg-slate-900 border border-slate-800 shadow-inner"
                    :class="sidebarCollapsed ? 'justify-center px-0' : ''">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white font-bold shadow-lg shrink-0">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div x-show="!sidebarCollapsed" class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
                        <p class="text-[11px] text-slate-500 truncate font-medium uppercase tracking-wider">
                            Administrador</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- HEADER -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-8 z-40">
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 rounded-md text-slate-600 hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" x2="20" y1="12" y2="12" />
                            <line x1="4" x2="20" y1="6" y2="6" />
                            <line x1="4" x2="20" y1="18" y2="18" />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-slate-800">{{ $title ?? 'Dashboard' }}</h2>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <button class="p-2 text-slate-400 hover:text-blue-600 relative transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                        </svg>
                        <span
                            class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-3 p-1 pr-3 rounded-full hover:bg-slate-100 transition-all duration-200 group">
                            <div
                                class="h-9 w-9 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm group-hover:shadow-indigo-200 transition-all">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span
                                class="hidden md:block text-sm font-medium text-slate-700 group-hover:text-indigo-600 transition-colors">
                                {{ Auth::user()->name }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="text-slate-400 group-hover:text-indigo-500 transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50"
                            x-cloak>

                            <div class="px-4 py-2 border-b border-slate-100 mb-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Cuenta</p>
                            </div>

                            <a href="{{ route('dashboard') }}"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                Mi Perfil
                            </a>

                            <a href="{{ route('profile.password') }}"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                Cambiar Contraseña
                            </a>

                            <div class="border-t border-slate-100 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                            <polyline points="16 17 21 12 16 7" />
                                            <line x1="21" x2="21" y1="12" y2="12" />
                                        </svg>
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-8 bg-slate-50 flex flex-col">
                <div class="flex-1">
                    {{ $slot }}
                    @if(session('message'))
                        <div
                            class="mb-6 p-4 bg-indigo-50 border-l-4 border-indigo-500 text-indigo-700 rounded-r-lg flex items-center gap-3 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span class="text-sm font-medium">{{ session('message') }}</span>
                        </div>
                    @endif
                    @if(session('success'))
                        <div
                            class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg flex items-center gap-3 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div
                            class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-center gap-3 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Internal Footer -->
                <footer class="py-6 mt-auto border-t border-slate-200 text-center">
                    <p class="text-xs text-slate-400">
                        PodoSoft &copy; {{ date('Y') }} | Desarrollado por <a
                            href="{{ config('company.provider_url') }}" target="_blank"
                            class="text-slate-500 hover:text-indigo-600 font-medium transition-colors">{{ config('company.provider_name') }}</a>
                    </p>
                </footer>
            </main>
        </div>
    </div>

    <!-- About Modal -->


    <!-- About Modal -->

    <div x-show="aboutOpen" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="aboutOpen = false">
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="relative z-10 inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 py-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 2v20" />
                                <path d="m17 5-5-3-5 3" />
                                <rect width="20" height="14" x="2" y="5" rx="2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900">Acerca de PodoSoft</h3>
                    </div>
                    <div class="space-y-4 text-slate-600 leading-relaxed">
                        <p>
                            PodoSoft es una solución SaaS de vanguardia diseñada específicamente para la gestión de
                            consultorios de podología.
                        </p>
                        <p>
                            Esta tecnología ha sido desarrollada y es mantenida por <a
                                href="{{ config('company.provider_url') }}" target="_blank"
                                class="text-indigo-600 font-semibold hover:underline">{{ config('company.provider_name') }}</a>,
                            empresa especializada en desarrollo de software, automatización de procesos, inteligencia
                            artificial y soluciones SaaS.
                        </p>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-sm font-medium text-slate-900 mb-2">Contacto de Soporte:</p>
                            <a href="mailto:{{ config('company.support_email') }}"
                                class="text-sm text-indigo-600 hover:underline">{{ config('company.support_email') }}</a>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 flex justify-end">
                    <button @click="aboutOpen = false"
                        class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-100 transition-all">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>