<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Bank') }}</title>

    {{-- Tailwind CSS (kompiliert via Laravel Mix + PostCSS) --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <script src="https://kit.fontawesome.com/c8f58e3eb6.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" defer></script>

    @stack('css')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
<div id="app" class="min-h-screen flex flex-col">

    <nav class="bg-brand-600 text-white shadow-kid">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-2xl font-extrabold tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-piggy-bank"></i>
                {{ config('app.name', 'Bank') }}
            </a>

            <button type="button" id="navToggle" class="md:hidden p-2 rounded-lg hover:bg-brand-700" aria-label="Menü">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <div class="hidden md:flex items-center gap-2">
                @guest
                    @if (Route::has('login'))
                        <a class="px-4 py-2 rounded-xl hover:bg-brand-700" href="{{ route('login') }}">{{ __('Login') }}</a>
                    @endif
                    @if (Route::has('register'))
                        <a class="px-4 py-2 rounded-xl hover:bg-brand-700" href="{{ route('register') }}">{{ __('Register') }}</a>
                    @endif
                @else
                    <div class="relative" id="userDrop">
                        <button type="button" class="px-4 py-2 rounded-xl hover:bg-brand-700 flex items-center gap-2"
                                onclick="document.getElementById('userDropMenu').classList.toggle('hidden')">
                            <i class="fa-solid fa-user-circle"></i>
                            {{ Auth::user()->name }}
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                        <div id="userDropMenu" class="hidden absolute right-0 mt-2 w-56 bg-white text-slate-800 rounded-2xl shadow-kid overflow-hidden z-50">
                            @if(auth()->user()->is_admin)
                                <a class="block px-4 py-3 hover:bg-slate-100" href="{{ url('dashboard') }}"><i class="fa-solid fa-gauge mr-2 text-brand-600"></i>Dashboard</a>
                                <a class="block px-4 py-3 hover:bg-slate-100" href="{{ url('import') }}"><i class="fa-solid fa-file-import mr-2 text-brand-600"></i>Import</a>
                                <a class="block px-4 py-3 hover:bg-slate-100" href="{{ url('remove/key') }}"><i class="fa-solid fa-key mr-2 text-brand-600"></i>Key entfernen</a>
                                <a class="block px-4 py-3 hover:bg-slate-100" href="{{ route('admin.betriebe.pin') }}"><i class="fa-solid fa-store mr-2 text-emerald-600"></i>Betriebs-Kassen</a>
                                @php
                                    try { $boerseAlarm = app(\App\Services\BoerseAufgabenService::class)->alarmCount(); }
                                    catch (\Throwable $e) { $boerseAlarm = 0; }
                                @endphp
                                <a class="block px-4 py-3 hover:bg-slate-100 flex items-center justify-between" href="{{ route('admin.boerse') }}">
                                    <span><i class="fa-solid fa-chart-line mr-2 text-amber-600"></i>Radi-Börse</span>
                                    @if($boerseAlarm > 0)
                                        <span class="bg-rose-500 text-white text-xs font-bold px-2 py-1 rounded-full">🔴 {{ $boerseAlarm }}</span>
                                    @endif
                                </a>
                            @endif
                            @if(auth()->user()->is_manager)
                                <a class="block px-4 py-3 hover:bg-slate-100" href="{{ url('create/customer') }}"><i class="fa-solid fa-user-plus mr-2 text-brand-600"></i>neuer Kunde</a>
                            @endif
                            <a class="block px-4 py-3 hover:bg-rose-50 text-rose-600 font-semibold"
                               href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa-solid fa-right-from-bracket mr-2"></i>{{ __('Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
        <div id="navMenuMobile" class="hidden md:hidden px-4 pb-4 space-y-1">
            @guest
                @if (Route::has('login'))<a class="block px-3 py-2 rounded-lg hover:bg-brand-700" href="{{ route('login') }}">{{ __('Login') }}</a>@endif
                @if (Route::has('register'))<a class="block px-3 py-2 rounded-lg hover:bg-brand-700" href="{{ route('register') }}">{{ __('Register') }}</a>@endif
            @else
                @if(auth()->user()->is_admin)
                    <a class="block px-3 py-2 rounded-lg hover:bg-brand-700" href="{{ url('dashboard') }}">Dashboard</a>
                    <a class="block px-3 py-2 rounded-lg hover:bg-brand-700" href="{{ url('import') }}">Import</a>
                    <a class="block px-3 py-2 rounded-lg hover:bg-brand-700" href="{{ url('remove/key') }}">Key entfernen</a>
                @endif
                @if(auth()->user()->is_manager)
                    <a class="block px-3 py-2 rounded-lg hover:bg-brand-700" href="{{ url('create/customer') }}">neuer Kunde</a>
                @endif
                <a class="block px-3 py-2 rounded-lg hover:bg-brand-700" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form-m').submit();">{{ __('Logout') }}</a>
                <form id="logout-form-m" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            @endguest
        </div>
    </nav>

    <main class="flex-1 py-6">
        <div class="max-w-7xl mx-auto px-4 space-y-4">
            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 border-2 border-rose-200 text-rose-800 p-4">
                    <div class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Da ist etwas schief gelaufen:</div>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if(session('Meldung'))
                @php
                    $t = session('type');
                    $styles = [
                        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                        'error'   => 'bg-rose-50 border-rose-200 text-rose-800',
                        'danger'  => 'bg-rose-50 border-rose-200 text-rose-800',
                        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
                        'info'    => 'bg-sky-50 border-sky-200 text-sky-800',
                    ];
                    $cls = $styles[$t] ?? 'bg-slate-50 border-slate-200 text-slate-800';
                @endphp
                <div class="rounded-2xl border-2 p-4 text-lg font-semibold {{ $cls }}">
                    {{ session('Meldung') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script>
    document.getElementById('navToggle')?.addEventListener('click', () => {
        document.getElementById('navMenuMobile').classList.toggle('hidden');
    });
    document.addEventListener('click', (e) => {
        const d = document.getElementById('userDrop');
        const m = document.getElementById('userDropMenu');
        if (d && m && !d.contains(e.target)) m.classList.add('hidden');
    });
</script>
@stack('js')
</body>
</html>
