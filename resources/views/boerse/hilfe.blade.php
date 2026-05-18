@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200">
    <h1 class="text-3xl font-extrabold text-amber-700">📋 Was mache ich heute an der Börse?</h1>
    <p class="text-slate-700 mt-2">Schau dir unten deine Aufgabe an — und los geht's!</p>
    <div class="mt-4 flex gap-2 flex-wrap">
        <a href="/boerse/hilfe/drucken" target="_blank" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl">🖨️ Jobkarten drucken (DIN A5)</a>
        <a href="/boerse" class="bg-slate-200 hover:bg-slate-300 font-bold px-4 py-2 rounded-xl">🔙 Zurück zum Dashboard</a>
    </div>
</div>

@php
    $jobs = [
        ['icon'=>'🤝','titel'=>'Job 1: Der Händler','color'=>'border-emerald-400','aufgaben'=>[
            'Wenn jemand Anteile kaufen will → „Kauf erfassen"',
            'Wenn jemand Anteile verkaufen will → „Verkauf erfassen"',
            'Wenn ein Betrieb Anteile zurückkauft → „Rückkauf erfassen"',
            'Stündlich: Schau, ob die Kasse stimmt',
        ]],
        ['icon'=>'🔭','titel'=>'Job 2: Der Kursbeobachter','color'=>'border-sky-400','aufgaben'=>[
            'Jede Stunde: Geh zu jedem Betrieb, zähle Mitarbeiter',
            'Trag die Zahl ein: „Beobachtung erfassen"',
            'Füge einen kurzen Eindruck ein: „voll", „ruhig", „leer"',
            'Schau die Kursvorschau an — was ändert sich?',
        ]],
        ['icon'=>'💰','titel'=>'Job 3: Der Kassenwart','color'=>'border-amber-400','aufgaben'=>[
            'Jede Stunde: Kassenstand prüfen → „Kassenstand bestätigen"',
            'Wenn Geld eingelegt wird: „Einlage erfassen"',
            'Wenn zu wenig Geld → Lehrkraft informieren!',
            'Am Ende: Kassenabschluss drucken',
        ]],
        ['icon'=>'📰','titel'=>'Job 4: Der Börsenwart','color'=>'border-rose-400','aufgaben'=>[
            'Nach jeder Kursberechnung: Kurstafel drucken + aufhängen',
            'Dann klicken: „Kurstafel ist ausgehängt" ← wichtig!',
            'Fragen von Kindern beantworten: Was kostet ein Anteil?',
            'Am Ende: Tagesbericht drucken',
        ]],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($jobs as $job)
        <div class="bg-white rounded-2xl shadow-kid p-6 border-2 {{ $job['color'] }}">
            <div class="text-4xl mb-1">{{ $job['icon'] }}</div>
            <div class="text-xl font-extrabold mb-3">{{ $job['titel'] }}</div>
            <ul class="space-y-2">
                @foreach($job['aufgaben'] as $a)
                    <li class="flex gap-2">
                        <span class="text-emerald-500 font-bold">✅</span>
                        <span>{{ $a }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>

{{-- ════════════════════════════════ KURSBERECHNUNG ════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-indigo-300 mt-2">
    <h2 class="text-2xl font-extrabold text-indigo-700 mb-1">📈 Wie wird der Kurs berechnet?</h2>
    <p class="text-slate-600 mb-5">
        Jede Stunde berechnet der Computer den neuen Kurs für jeden Betrieb.
        Dabei spielen <strong>zwei Dinge</strong> eine Rolle:
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

        {{-- Faktor 1: Mitarbeiter --}}
        <div class="rounded-2xl border-2 border-sky-300 bg-sky-50 p-5">
            <div class="text-3xl mb-1">🧑‍🤝‍🧑</div>
            <div class="text-lg font-extrabold text-sky-700 mb-2">Faktor 1: Mitarbeiter</div>
            <p class="text-slate-700 text-sm mb-3">
                Der Kursbeobachter zählt die Mitarbeiter im Betrieb.
                Normal sind <strong>{{ $kursInfo['normalAngest'] }} Mitarbeiter</strong>.
            </p>
            <table class="w-full text-sm rounded-xl overflow-hidden">
                <thead class="bg-sky-200 text-sky-900">
                    <tr>
                        <th class="p-2 text-left">Mitarbeiter</th>
                        <th class="p-2 text-center">Kursänderung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-100">
                    @php
                        $n = $kursInfo['normalAngest'];
                        $rows = [
                            [0,       '😴 0'],
                            [$n - 2,  '😕 ' . ($n-2)],
                            [$n - 1,  '🙁 ' . ($n-1)],
                            [$n,      '😐 ' . $n . ' (normal)'],
                            [$n + 1,  '🙂 ' . ($n+1)],
                            [$n + 2,  '😄 ' . ($n+2)],
                            [$n + 3,  '🤩 ' . ($n+3) . ' oder mehr'],
                        ];
                    @endphp
                    @foreach($rows as [$anz, $label])
                        @php
                            $d   = max(-2, min(2, $anz - $n));
                            $cls = $d > 0 ? 'text-emerald-600 font-bold' : ($d < 0 ? 'text-rose-600 font-bold' : 'text-slate-500');
                            $txt = $d > 0 ? '+' . $d . ' Radi' : ($d < 0 ? $d . ' Radi' : '± 0 Radi');
                        @endphp
                        <tr class="bg-white">
                            <td class="p-2">{{ $label }}</td>
                            <td class="p-2 text-center {{ $cls }}">{{ $txt }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Faktor 2: Umsatz --}}
        <div class="rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-5">
            <div class="text-3xl mb-1">🛒</div>
            <div class="text-lg font-extrabold text-emerald-700 mb-2">Faktor 2: Umsatz (Verkäufe)</div>
            <p class="text-slate-700 text-sm mb-3">
                Je mehr ein Betrieb verkauft, desto höher steigt sein Kurs.
                Für je <strong>{{ $kursInfo['teiler'] }} Radi Gewinn</strong> steigt der Kurs um <strong>1 Radi</strong>.
            </p>
            <div class="space-y-2 text-sm">
                @php
                    $t = $kursInfo['teiler'];
                    $beispiele = [
                        [0,        '🤷 0 Radi Gewinn',                0],
                        [$t - 1,   '📦 ' . ($t-1) . ' Radi Gewinn',  0],
                        [$t,       '📦 ' . $t . ' Radi Gewinn',       1],
                        [$t * 2,   '📦 ' . ($t*2) . ' Radi Gewinn',   2],
                        [$t * 3,   '🚀 ' . ($t*3) . ' Radi Gewinn',   3],
                    ];
                @endphp
                @foreach($beispiele as [$umsatz, $label, $plus])
                    <div class="flex items-center justify-between bg-white rounded-xl px-3 py-2 border border-emerald-100">
                        <span>{{ $label }}</span>
                        <span class="{{ $plus > 0 ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">
                            {{ $plus > 0 ? '+' . $plus . ' Radi' : '± 0 Radi' }}
                        </span>
                    </div>
                @endforeach
                <p class="text-xs text-slate-500 mt-1">
                    Entnahmen werden vom Umsatz abgezogen.
                </p>
            </div>
        </div>
    </div>

    {{-- Bremse --}}
    <div class="rounded-2xl border-2 border-amber-300 bg-amber-50 p-5 mb-4">
        <div class="text-3xl mb-1">🛡️</div>
        <div class="text-lg font-extrabold text-amber-700 mb-2">Die Bremse — der Kurs kann nicht zu schnell springen!</div>
        <p class="text-slate-700 text-sm mb-3">
            Egal wie viele Mitarbeiter da sind oder wie viel verkauft wurde —
            der Kurs darf pro Stunde nur um <strong>höchstens {{ $kursInfo['maxSprungPct'] }}%</strong>
            des aktuellen Kurses steigen oder fallen (mindestens aber 1 Radi).
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
            @foreach([5, 10, 20] as $beispielKurs)
                @php $max = max(1, (int) floor($beispielKurs * $kursInfo['maxSprungPct'] / 100)); @endphp
                <div class="bg-white rounded-xl border border-amber-200 p-3 text-center">
                    <div class="font-extrabold text-xl text-amber-700">{{ $beispielKurs }} Radi</div>
                    <div class="text-slate-500 text-xs">aktueller Kurs</div>
                    <div class="mt-2 font-bold text-slate-700">max. ±{{ $max }} Radi/Stunde</div>
                    <div class="text-xs text-slate-500">({{ $kursInfo['maxSprungPct'] }}% von {{ $beispielKurs }})</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Minimum --}}
    <div class="rounded-2xl border-2 border-rose-200 bg-rose-50 p-4 mb-5">
        <div class="flex items-center gap-3">
            <span class="text-3xl">📉</span>
            <div>
                <div class="font-extrabold text-rose-700">Der Kurs fällt nie unter {{ $kursInfo['minKurs'] }} Radi</div>
                <div class="text-sm text-slate-600">
                    Auch wenn ein Betrieb sehr schlecht läuft — sein Anteil ist mindestens immer
                    <strong>{{ $kursInfo['minKurs'] }} Radi</strong> wert.
                </div>
            </div>
        </div>
    </div>

    {{-- Rechenweg --}}
    <div class="rounded-2xl border-2 border-indigo-200 bg-indigo-50 p-5">
        <div class="text-lg font-extrabold text-indigo-700 mb-3">🔢 Rechenweg — Schritt für Schritt</div>
        <ol class="space-y-3 text-sm">
            <li class="flex gap-3">
                <span class="bg-indigo-200 text-indigo-900 font-bold rounded-full w-7 h-7 flex items-center justify-center flex-shrink-0">1</span>
                <span><strong>Mitarbeiter zählen:</strong>
                    Abweichung von {{ $kursInfo['normalAngest'] }} ergibt −2 bis +2 Radi.</span>
            </li>
            <li class="flex gap-3">
                <span class="bg-indigo-200 text-indigo-900 font-bold rounded-full w-7 h-7 flex items-center justify-center flex-shrink-0">2</span>
                <span><strong>Umsatz berechnen:</strong>
                    (Verkäufe − Entnahmen) ÷ {{ $kursInfo['teiler'] }} = Radi Kursänderung (abgerundet).</span>
            </li>
            <li class="flex gap-3">
                <span class="bg-indigo-200 text-indigo-900 font-bold rounded-full w-7 h-7 flex items-center justify-center flex-shrink-0">3</span>
                <span><strong>Zusammenrechnen:</strong>
                    Alter Kurs + Mitarbeiter-Änderung + Umsatz-Änderung = vorläufiger neuer Kurs.</span>
            </li>
            <li class="flex gap-3">
                <span class="bg-indigo-200 text-indigo-900 font-bold rounded-full w-7 h-7 flex items-center justify-center flex-shrink-0">4</span>
                <span><strong>Bremse anwenden:</strong>
                    Maximale Änderung = {{ $kursInfo['maxSprungPct'] }}% des alten Kurses (min. 1 Radi).
                    Zu große Änderungen werden begrenzt.</span>
            </li>
            <li class="flex gap-3">
                <span class="bg-indigo-200 text-indigo-900 font-bold rounded-full w-7 h-7 flex items-center justify-center flex-shrink-0">5</span>
                <span><strong>Minimum prüfen:</strong>
                    Fällt der Kurs unter {{ $kursInfo['minKurs'] }} Radi → wird er auf {{ $kursInfo['minKurs'] }} Radi gesetzt.</span>
            </li>
        </ol>

        {{-- Live-Beispiel --}}
        <div class="mt-4 bg-white rounded-xl border-2 border-indigo-200 p-4">
            <div class="font-bold text-indigo-700 mb-2">💡 Beispiel:</div>
            @php
                $bspKurs    = 10;
                $bspAngest  = $kursInfo['normalAngest'] + 2;
                $bspUmsatz  = $kursInfo['teiler'] * 2;
                $bspAngestD = max(-2, min(2, $bspAngest - $kursInfo['normalAngest']));
                $bspUmsatzD = (int) floor($bspUmsatz / $kursInfo['teiler']);
                $bspRoh     = $bspKurs + $bspAngestD + $bspUmsatzD;
                $bspMax     = max(1, (int) floor($bspKurs * $kursInfo['maxSprungPct'] / 100));
                $bspNeu     = max($kursInfo['minKurs'], max($bspKurs - $bspMax, min($bspKurs + $bspMax, $bspRoh)));
            @endphp
            <div class="text-sm space-y-1 text-slate-700">
                <p>Aktueller Kurs: <strong>{{ $bspKurs }} Radi</strong>
                   &nbsp;·&nbsp; Mitarbeiter: <strong>{{ $bspAngest }}</strong>
                   &nbsp;·&nbsp; Umsatz: <strong>{{ $bspUmsatz }} Radi</strong></p>
                <p>→ Mitarbeiter-Änderung:
                   <strong class="text-emerald-600">+{{ $bspAngestD }} Radi</strong>
                   ({{ $bspAngest }} − {{ $kursInfo['normalAngest'] }} = +{{ $bspAngestD }})</p>
                <p>→ Umsatz-Änderung:
                   <strong class="text-emerald-600">+{{ $bspUmsatzD }} Radi</strong>
                   ({{ $bspUmsatz }} ÷ {{ $kursInfo['teiler'] }} = {{ $bspUmsatzD }})</p>
                <p>→ Vorläufig: {{ $bspKurs }} + {{ $bspAngestD }} + {{ $bspUmsatzD }} =
                   <strong>{{ $bspRoh }} Radi</strong></p>
                <p>→ Bremse greift: max ±{{ $bspMax }} Radi → höchstens {{ $bspKurs + $bspMax }} Radi</p>
                <p class="text-base font-extrabold text-indigo-700 mt-2">
                    ✅ Neuer Kurs: {{ $bspNeu }} Radi
                    {{ $bspNeu > $bspKurs ? '📈' : ($bspNeu < $bspKurs ? '📉' : '➡️') }}
                    ({{ $bspNeu > $bspKurs ? '+' : '' }}{{ $bspNeu - $bspKurs }} Radi)
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

