<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ session('betrieb') ? session('betrieb')->name . ' – Kasse' : 'Betriebs-Kasse' }}</title>
    @vite(['resources/css/app.css'])
    <script src="https://kit.fontawesome.com/c8f58e3eb6.js" crossorigin="anonymous" defer></script>
    @stack('css')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
<div class="min-h-screen flex flex-col">

    @if(session('betrieb'))
    <nav class="bg-emerald-600 text-white shadow-kid">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-store text-2xl"></i>
                <span class="text-xl font-extrabold">{{ session('betrieb')->name }}</span>
            </div>
            <div class="flex items-center gap-4 flex-wrap">
                <span class="bg-white/20 rounded-2xl px-4 py-2 font-extrabold text-lg">
                    <i class="fa-solid fa-cash-register mr-1"></i>
                    Kasse: {{ session('betrieb') ? \App\Models\Customer::find(session('betrieb')->id)?->kassenbestand() : 0 }} Radi
                </span>
                <a href="/betrieb/kasse"      class="px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold"><i class="fa-solid fa-cash-register mr-1"></i>Kasse</a>
                <a href="/betrieb/produkte"   class="px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold"><i class="fa-solid fa-box mr-1"></i>Produkte</a>
                <a href="/betrieb/abrechnung" class="px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold"><i class="fa-solid fa-chart-bar mr-1"></i>Abrechnung</a>
                @if(session('betrieb') && \App\Models\Customer::find(session('betrieb')->id)?->isFotostudio())
                <a href="/betrieb/fotos"      class="px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold"><i class="fa-solid fa-camera mr-1"></i>Fotos</a>
                @endif
                <a href="/betrieb/logout"     class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 font-semibold"><i class="fa-solid fa-right-from-bracket mr-1"></i>Abmelden</a>
            </div>
        </div>
    </nav>
    @endif

    <main class="flex-1 py-6">
        <div class="max-w-7xl mx-auto px-4 space-y-4">

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 border-2 border-rose-200 text-rose-800 p-4">
                    <div class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Fehler:</div>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if(session('Meldung'))
                @php
                    $t = session('type');
                    $styles = ['success'=>'bg-emerald-50 border-emerald-200 text-emerald-800','error'=>'bg-rose-50 border-rose-200 text-rose-800','danger'=>'bg-rose-50 border-rose-200 text-rose-800','warning'=>'bg-amber-50 border-amber-200 text-amber-800'];
                    $cls = $styles[$t] ?? 'bg-slate-50 border-slate-200 text-slate-800';
                @endphp
                <div class="rounded-2xl border-2 p-4 text-lg font-semibold {{ $cls }}">{{ session('Meldung') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
@stack('js')
</body>
</html>

