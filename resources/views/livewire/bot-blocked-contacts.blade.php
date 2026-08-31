<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Números bloqueados del bot</h1>
        <p class="text-sm text-slate-500 mt-1">Los mensajes de estos números serán ignorados por el agente de IA y no recibirán respuesta.</p>
        <p class="text-xs text-slate-400 mt-1">Los mensajes provenientes de grupos de WhatsApp siempre son ignorados automáticamente.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg shadow-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Agregar número</h2>
        <form wire:submit.prevent="add" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model="phone" placeholder="Ej: 573001234567 (con código de país)"
                    class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
                @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex-1">
                <input type="text" wire:model="reason" placeholder="Motivo (opcional)"
                    class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
                @error('reason') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                    Agregar
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Motivo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contacts as $contact)
                <tr>
                    <td class="px-6 py-4 font-mono text-sm text-slate-800">+{{ $contact->phone }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $contact->reason ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $contact->active ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $contact->active ? 'Bloqueado' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button wire:click="toggle({{ $contact->id }})"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg
                                {{ $contact->active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                            {{ $contact->active ? 'Desactivar' : 'Activar' }}
                        </button>
                        <button wire:click="delete({{ $contact->id }})"
                            wire:confirm="¿Eliminar este número de la lista?"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">
                            Eliminar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">
                        No hay números bloqueados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $contacts->links() }}
        </div>
    </div>
</div>
