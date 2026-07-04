@if($account)
@php
$statusClass = match($account->status) {
    'connected' => 'bg-emerald-500',
    'connecting' => 'bg-amber-500',
    'disconnected' => 'bg-red-500',
    'error' => 'bg-red-500',
    default => 'bg-slate-400'
};
@endphp
<div class="relative" x-data="{ showTooltip: false }">
    <button
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
        class="relative p-1 rounded-full hover:bg-slate-700 transition-colors">
        <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 {{ $statusClass }} rounded-full border-2 border-slate-800"></span>
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
        </svg>
    </button>

    <div x-show="showTooltip"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute left-full ml-2 top-1/2 -translate-y-1/2 z-50 bg-slate-800 text-white text-xs rounded-lg px-3 py-2 shadow-xl whitespace-nowrap"
         style="display: none;">
        <div class="font-semibold mb-1">WhatsApp</div>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full {{ $statusClass }}"></span>
            <span class="capitalize">{{ $account->status }}</span>
        </div>
        @if($account->phone)
        <div class="text-slate-400 mt-1">{{ $account->phone }}</div>
        @endif
        <div class="absolute right-full top-1/2 -translate-y-1/2 border-8 border-transparent border-r-slate-800"></div>
    </div>
</div>
@else
<div class="relative" x-data="{ showTooltip: false }">
    <button
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
        class="relative p-1 rounded-full hover:bg-slate-700 transition-colors">
        <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-slate-400 rounded-full border-2 border-slate-800"></span>
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
        </svg>
    </button>

    <div x-show="showTooltip"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute left-full ml-2 top-1/2 -translate-y-1/2 z-50 bg-slate-800 text-white text-xs rounded-lg px-3 py-2 shadow-xl whitespace-nowrap"
         style="display: none;">
        <div class="font-semibold mb-1">WhatsApp</div>
        <div class="text-slate-400">No configurado</div>
        <a href="{{ route('config.whatsapp') }}" class="text-blue-400 hover:underline mt-1 block">Configurar</a>
        <div class="absolute right-full top-1/2 -translate-y-1/2 border-8 border-transparent border-r-slate-800"></div>
    </div>
</div>
@endif