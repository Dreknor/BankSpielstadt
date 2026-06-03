<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>📈 Radi-Börse</title>
    @vite(['resources/css/app.css'])
    <script src="https://kit.fontawesome.com/c8f58e3eb6.js" crossorigin="anonymous" defer></script>
    @stack('css')
</head>
<body class="bg-amber-50 text-slate-800 min-h-screen">
<div class="min-h-screen flex flex-col">

    @if(session('boerse'))
    @php
        $boerseNavBetrieb     = \App\Models\Customer::boerseBetrieb();
        $boerseNavSupport     = \App\Models\Customer::supportBetrieb();
        $boerseNavZeigeHilfe  = $boerseNavSupport && $boerseNavBetrieb;
        $boerseNavHilferuf    = null;
        if ($boerseNavZeigeHilfe) {
            $boerseNavHilferuf = \App\Models\Hilferuf::where('customer_id', $boerseNavBetrieb->id)
                ->whereIn('status', ['offen', 'in_bearbeitung'])->first();
        }
    @endphp
    <nav class="bg-amber-500 text-white shadow-kid">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between flex-wrap gap-3">
            <a href="/boerse" class="flex items-center gap-3 hover:opacity-90">
                <span class="text-3xl">📈</span>
                <span class="text-xl font-extrabold">Radi-Börse</span>
            </a>
            <div class="flex items-center gap-2 flex-wrap text-sm md:text-base">
                <a href="/boerse/handel"    class="px-3 py-2 rounded-xl hover:bg-amber-600 font-semibold"><i class="fa-solid fa-handshake mr-1"></i>Handel</a>
                <a href="/boerse/erfassung" class="px-3 py-2 rounded-xl hover:bg-amber-600 font-semibold">🔭 Erfassung</a>
                <a href="/boerse/kasse"     class="px-3 py-2 rounded-xl hover:bg-amber-600 font-semibold">💰 Kasse</a>
                <a href="/boerse/kurse"     class="px-3 py-2 rounded-xl hover:bg-amber-600 font-semibold">📊 Kurse</a>
                <a href="/boerse/anzeige" target="_blank" class="px-3 py-2 rounded-xl hover:bg-amber-600 font-semibold">🖥️ Anzeige</a>
                <a href="/boerse/hilfe"     class="px-3 py-2 rounded-xl bg-white/20 hover:bg-white/30 font-semibold">❓ Hilfe</a>
                <span class="bg-white/20 rounded-xl px-3 py-2 font-bold">{{ now()->format('H:i') }} Uhr</span>

                {{-- Hilfe-Button --}}
                @if($boerseNavZeigeHilfe)
                    @if($boerseNavHilferuf)
                        <span class="flex items-center gap-2 bg-rose-400 text-white font-extrabold px-4 py-2 rounded-2xl text-sm animate-pulse">
                            🆘 {{ $boerseNavHilferuf->status === 'in_bearbeitung' ? 'Helfer unterwegs!' : 'Warten auf Hilfe…' }}
                        </span>
                    @else
                        <button onclick="document.getElementById('boerse-hilfe-modal').classList.remove('hidden')"
                                class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 active:scale-95 text-white font-extrabold px-4 py-2 rounded-2xl text-sm transition-all shadow">
                            🆘 Hilfe rufen!
                        </button>
                    @endif
                @endif

                <a href="/boerse/logout" class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 font-semibold"><i class="fa-solid fa-right-from-bracket mr-1"></i>Abmelden</a>
            </div>
        </div>
    </nav>

    {{-- Hilfe-Modal --}}
    @if($boerseNavZeigeHilfe && !$boerseNavHilferuf)
    <div id="boerse-hilfe-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 space-y-5">
            <div class="text-center">
                <div class="text-6xl mb-2">🆘</div>
                <h2 class="text-3xl font-extrabold text-slate-800">Hilfe rufen</h2>
                <p class="text-slate-600 mt-1">Ein Helfer kommt dann zu euch!</p>
            </div>
            <form action="{{ route('boerse.hilfe.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-lg font-bold text-slate-700 mb-2">
                            <i class="fa-solid fa-comment mr-1 text-slate-400"></i>
                            Was ist das Problem? <span class="text-slate-400 font-normal text-base">(optional)</span>
                        </label>
                        <textarea name="nachricht" maxlength="500" rows="3"
                                  placeholder="z. B. Frage zum Kurs, technisches Problem…"
                                  class="w-full rounded-2xl border-2 border-slate-300 focus:border-rose-400 focus:outline-none p-3 text-lg resize-none"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 bg-rose-500 hover:bg-rose-600 active:scale-95 text-white font-extrabold text-xl py-4 rounded-2xl transition-all">
                            🆘 Hilfe schicken!
                        </button>
                        <button type="button"
                                onclick="document.getElementById('boerse-hilfe-modal').classList.add('hidden')"
                                class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xl py-4 rounded-2xl transition-all">
                            Abbrechen
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Aufgaben-Status-Banner: 3 Kacheln auf jeder Seite --}}
    @isset($aufgabenStatus)
    <div class="bg-white border-b-2 border-amber-200">
        <div class="max-w-7xl mx-auto px-4 py-3 grid grid-cols-1 md:grid-cols-3 gap-3">
            @php
                $kachelStyles = [
                    'ok'    => 'bg-emerald-100 border-emerald-300 text-emerald-900',
                    'warn'  => 'bg-amber-100 border-amber-400 text-amber-900',
                    'alarm' => 'bg-rose-100 border-rose-400 text-rose-900',
                ];
                $icons = ['ok'=>'✅', 'warn'=>'⚠️', 'alarm'=>'🚨'];
                $kacheln = [
                    'beobachtung'     => ['url' => '/boerse/erfassung', 'icon' => '🔭', 'label' => 'Beobachtung'],
                    'kassenkontrolle' => ['url' => '/boerse/kasse',     'icon' => '💰', 'label' => 'Kassenkontrolle'],
                    'kurstafel'       => ['url' => '/boerse/kurse',     'icon' => '📰', 'label' => 'Kurstafel'],
                ];
            @endphp
            @foreach($kacheln as $key => $info)
                @php
                    $s   = $aufgabenStatus[$key] ?? ['status'=>'ok','minuten'=>0];
                    $cls = $kachelStyles[$s['status']] ?? $kachelStyles['ok'];
                    $ic  = $icons[$s['status']] ?? '✅';
                @endphp
                <a href="{{ $info['url'] }}" class="block rounded-xl border-2 p-3 {{ $cls }} hover:shadow-md transition">
                    <div class="font-bold text-lg">{{ $info['icon'] }} {{ $info['label'] }}</div>
                    <div class="text-sm">
                        {{ $ic }}
                        @if(isset($s['minuten']) && $s['minuten'] < 999)
                            vor {{ $s['minuten'] }} Min
                        @elseif(isset($s['text']))
                            {{ $s['text'] }}
                        @endif
                        @if($s['status'] === 'warn') — bitte bald erledigen!
                        @elseif($s['status'] === 'alarm') — bitte jetzt erledigen!
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endisset
    @endif

    <main class="flex-1 py-6">
        <div class="max-w-7xl mx-auto px-4 space-y-4">

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 border-2 border-rose-200 text-rose-800 p-4">
                    <div class="font-bold mb-1">⚠️ Fehler:</div>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if(session('Meldung'))
                @php
                    $t = session('type');
                    $styles = [
                        'success'=>'bg-emerald-50 border-emerald-200 text-emerald-800',
                        'error'  =>'bg-rose-50 border-rose-200 text-rose-800',
                        'danger' =>'bg-rose-50 border-rose-200 text-rose-800',
                        'warning'=>'bg-amber-50 border-amber-300 text-amber-900',
                    ];
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

