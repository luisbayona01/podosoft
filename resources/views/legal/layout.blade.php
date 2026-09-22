<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | PodoSoft</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>body { font-family: 'Instrument Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

    <header class="bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20" /><path d="m17 5-5-3-5 3" /><rect width="20" height="14" x="2" y="5" rx="2" /></svg>
                </div>
                <span class="text-lg font-bold tracking-tight text-slate-900">PodoSoft</span>
            </a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('legal.privacy') }}" class="text-slate-600 hover:text-indigo-600 transition">Privacidad</a>
                <a href="{{ route('legal.terms') }}" class="text-slate-600 hover:text-indigo-600 transition">Términos</a>
            </nav>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-10">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-10">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">@yield('heading')</h1>
            <p class="text-sm text-slate-500 mb-8">Última actualización: {{ now()->format('d/m/Y') }}</p>

            <div class="prose-legal space-y-6 text-slate-700 leading-relaxed text-[15px]">
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white mt-10">
        <div class="max-w-4xl mx-auto px-6 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
            <span>© {{ date('Y') }} PodoSoft. Todos los derechos reservados.</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('legal.privacy') }}" class="hover:text-indigo-600 transition">Política de Privacidad</a>
                <a href="{{ route('legal.terms') }}" class="hover:text-indigo-600 transition">Condiciones del Servicio</a>
            </div>
        </div>
    </footer>

</body>
</html>
