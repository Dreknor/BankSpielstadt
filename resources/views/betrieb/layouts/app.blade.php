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
    @php
        $navBetrieb       = \App\Models\Customer::find(session('betrieb')->id);
        $navSupportBetrieb = \App\Models\Customer::supportBetrieb();
        $navOffenerHilferuf = null;
        $navZeigeHilfe    = $navSupportBetrieb && $navBetrieb && !$navBetrieb->isSupport();
        if ($navZeigeHilfe) {
            $navOffenerHilferuf = \App\Models\Hilferuf::where('customer_id', $navBetrieb->id)
                ->whereIn('status', ['offen', 'in_bearbeitung'])->first();
        }
    @endphp
    <nav class="bg-emerald-600 text-white shadow-kid">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-store text-2xl"></i>
                <span class="text-xl font-extrabold">{{ session('betrieb')->name }}</span>
            </div>
            <div class="flex items-center gap-4 flex-wrap">
                <span class="bg-white/20 rounded-2xl px-4 py-2 font-extrabold text-lg">
                    <i class="fa-solid fa-cash-register mr-1"></i>
                    Kasse: {{ $navBetrieb?->kassenbestand() ?? 0 }} Radi
                </span>
                <a href="/betrieb/kasse"      class="px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold"><i class="fa-solid fa-cash-register mr-1"></i>Kasse</a>
                <a href="/betrieb/produkte"   class="px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold"><i class="fa-solid fa-box mr-1"></i>Produkte</a>
                <a href="/betrieb/abrechnung" class="px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold"><i class="fa-solid fa-chart-bar mr-1"></i>Abrechnung</a>
                @if($navBetrieb?->isFotostudio())
                <a href="/betrieb/fotos"      class="px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold"><i class="fa-solid fa-camera mr-1"></i>Fotos</a>
                @endif
                @if($navBetrieb?->isSupport())
                <a href="/betrieb/hilfe" class="relative px-3 py-2 rounded-xl hover:bg-emerald-700 font-semibold">
                    <i class="fa-solid fa-bell mr-1"></i>Hilferufe
                    @php $offeneHilferufe = \App\Models\Hilferuf::where('status','offen')->count(); @endphp
                    @if($offeneHilferufe > 0)
                        <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-xs font-extrabold rounded-full w-5 h-5 flex items-center justify-center animate-pulse">{{ $offeneHilferufe }}</span>
                    @endif
                </a>
                @endif

                {{-- Hilfe-Button für normale Betriebe --}}
                @if($navZeigeHilfe)
                    @if($navOffenerHilferuf)
                        {{-- Laufender Hilferuf: Status-Anzeige --}}
                        <span class="flex items-center gap-2 bg-amber-400 text-amber-900 font-extrabold px-4 py-2 rounded-2xl text-sm animate-pulse">
                            🆘 {{ $navOffenerHilferuf->status === 'in_bearbeitung' ? 'Helfer unterwegs!' : 'Warten auf Hilfe…' }}
                        </span>
                    @else
                        {{-- Kein aktiver Hilferuf: Button --}}
                        <button onclick="document.getElementById('hilfe-modal').classList.remove('hidden')"
                                class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 active:scale-95 text-white font-extrabold px-4 py-2 rounded-2xl text-sm transition-all shadow">
                            🆘 Hilfe rufen!
                        </button>
                    @endif
                @endif

                <a href="/betrieb/logout" class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 font-semibold"><i class="fa-solid fa-right-from-bracket mr-1"></i>Abmelden</a>
            </div>
        </div>
    </nav>
    @endif

    {{-- Hilfe-Modal (global, für alle Betrieb-Seiten) --}}
    @if(isset($navZeigeHilfe) && $navZeigeHilfe && !$navOffenerHilferuf)
    <div id="hilfe-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 space-y-5">
            <div class="text-center">
                <div class="text-6xl mb-2">🆘</div>
                <h2 class="text-3xl font-extrabold text-slate-800">Hilfe rufen</h2>
                <p class="text-slate-600 mt-1">Ein Helfer kommt dann zu euch!</p>
            </div>
            <form action="{{ route('betrieb.hilfe.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-lg font-bold text-slate-700 mb-2">
                            <i class="fa-solid fa-comment mr-1 text-slate-400"></i>
                            Was ist das Problem? <span class="text-slate-400 font-normal text-base">(optional)</span>
                        </label>
                        <textarea name="nachricht" maxlength="500" rows="3"
                                  placeholder="z. B. Kasse macht Fehler, Frage zu Produkten…"
                                  class="w-full rounded-2xl border-2 border-slate-300 focus:border-rose-400 focus:outline-none p-3 text-lg resize-none"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 bg-rose-500 hover:bg-rose-600 active:scale-95 text-white font-extrabold text-xl py-4 rounded-2xl transition-all">
                            🆘 Hilfe schicken!
                        </button>
                        <button type="button"
                                onclick="document.getElementById('hilfe-modal').classList.add('hidden')"
                                class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xl py-4 rounded-2xl transition-all">
                            Abbrechen
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
