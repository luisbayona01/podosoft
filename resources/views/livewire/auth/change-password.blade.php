<div class="max-w-md mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-100">
        <h3 class="text-xl font-bold text-slate-900">Cambiar Contraseña</h3>
        <p class="text-sm text-slate-500">Asegúrate de que tu nueva contraseña sea segura y difícil de adivinar.</p>
    </div>
    
    <div class="p-6">
        <form wire:submit.prevent="updatePassword" class="space-y-5">
            <div>
                <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Contraseña Actual</label>
                <input type="password" wire:model="current_password" id="current_password" 
                    class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none @error('current_password') border-red-500 @enderror">
                @error('current_password') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1">Nueva Contraseña</label>
                <input type="password" wire:model="new_password" id="new_password" 
                    class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none @error('new_password') border-red-500 @enderror">
                @error('new_password') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirmar Nueva Contraseña</label>
                <input type="password" wire:model="new_password_confirmation" id="new_password_confirmation" 
                    class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none @error('new_password_confirmation') border-red-500 @enderror">
                @error('new_password_confirmation') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="pt-2">
                <button type="submit" 
                    class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Actualizar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>
