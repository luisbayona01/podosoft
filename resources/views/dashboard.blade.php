@extends('layouts.app')

@section('content')
    @php $title = 'Panel de Control'; @endphp

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="//" cy="7" r="4"/></svg>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Pacientes Totales</p>
                <p class="text-2xl font-bold text-slate-900">1,284</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Citas Hoy</p>
                <p class="text-2xl font-bold text-slate-900">12</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Ingresos Mensuales</p>
                <p class="text-2xl font-bold text-slate-900">$4,250.00</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Tasa de Retorno</p>
                <p class="text-2xl font-bold text-slate-900">84%</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Appointments Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Próximas Citas</h3>
                <a href="#" class="text-sm text-blue-600 font-medium hover:underline">Ver todas</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-3">Paciente</th>
                            <th class="px-6 py-3">Servicio</th>
                            <th class="px-6 py-3">Fecha/Hora</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">Carlos Mendoza</td>
                            <td class="px-6 py-4 text-slate-600">Quiropodia</td>
                            <td class="px-6 py-4 text-slate-600">Hoy, 14:30</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-medium">Confirmada</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-blue-600 hover:text-blue-800 font-medium">Detalles</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">Ana García</td>
                            <td class="px-6 py-4 text-slate-600">Estudio Biomecánico</td>
                            <td class="px-6 py-4 text-slate-600">Hoy, 16:00</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">Pendiente</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-blue-600 hover:text-blue-800 font-medium">Detalles</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions/Summary -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="font-bold text-slate-800 mb-4">Acciones Rápidas</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="#" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-all border border-transparent hover:border-blue-200 group">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm group-hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </div>
                        <span class="text-sm font-medium">Nueva Cita</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-all border border-transparent hover:border-blue-200 group">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm group-hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <span class="text-sm font-medium">Nuevo Paciente</span>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl shadow-lg text-white">
                <h3 class="font-bold mb-2">Soporte Premium</h3>
                <p class="text-sm text-blue-100 mb-4">¿Tienes dudas sobre la configuración de tu consultorio?</p>
                <button class="w-full py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg text-sm font-semibold transition-colors">
                    Contactar Soporte
                </button>
            </div>
        </div>
    </div>
@endsection
