@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-6 print:shadow-none">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-2xl font-extrabold">🔍 Arbitrage-Auswertung Börse</h2>
        <div class="flex gap-2">
            <a href="javascript:window.print()" class="btn btn-sm print:hidden">🖨 Drucken</a>
            <a href="/admin/boerse" class="btn print:hidden">🔙 Zurück</a>
        </div>
    </div>

    @php
        $verdaechtig = $paare->where('verdaechtig', true);
        $schnell     = $paare->where('schnellverkauf', true)->where('verdaechtig', false);
        $schnellMin  = config('bank.aktien.arbitrage_schnellverkauf_min', 30);
        $minGewinn   = config('bank.aktien.arbitrage_min_gewinn', 1);
    @endphp

    <div class="text-sm text-slate-500">
        Schwellen: Schnellverkauf &lt; {{ $schnellMin }} Min · Verdächtig wenn Δ Kurs ≥ {{ $minGewinn }} Radi/Anteil
    </div>

    {{-- Kennzahlen --}}
    <div class="grid grid-cols-3 gap-4 text-sm">
        <div class="rounded-xl border-2 p-4 bg-slate-50">
            <div class="text-slate-500">Paare gesamt</div>
            <div class="text-3xl font-extrabold">{{ $paare->count() }}</div>
        </div>
        <div class="rounded-xl border-2 p-4 bg-orange-50">
            <div class="text-orange-700">Nur Schnellverkäufe</div>
            <div class="text-3xl font-extrabold text-orange-700">{{ $schnell->count() }}</div>
        </div>
        <div class="rounded-xl border-2 p-4 bg-rose-50">
            <div class="text-rose-700">Verdächtig (Kursgewinn)</div>
            <div class="text-3xl font-extrabold text-rose-700">{{ $verdaechtig->count() }}</div>
        </div>
    </div>

    {{-- Summierung je Kind --}}
    @if($summierung->isNotEmpty())
    <div>
        <h3 class="font-bold text-lg mb-2">⚠️ Potenzielle Arbitrage-Gewinne je Kind</h3>
        <table class="w-full text-sm border border-rose-200 rounded-lg overflow-hidden">
            <thead class="bg-rose-50 text-rose-800">
                <tr>
                    <th class="px-4 py-2 text-left">Kind</th>
                    <th class="px-4 py-2 text-center">Auffällige Paare</th>
                    <th class="px-4 py-2 text-right">Summe Kursgewinne</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summierung as $s)
                <tr class="border-t border-rose-100">
                    <td class="px-4 py-2 font-semibold">{{ $s['kind'] }}</td>
                    <td class="px-4 py-2 text-center">{{ $s['anzahl'] }}</td>
                    <td class="px-4 py-2 text-right font-bold text-rose-700">+{{ $s['gewinn_gesamt'] }} Radi</td>
                </tr>
                @endforeach
                <tr class="border-t-2 border-rose-300 bg-rose-100 font-bold">
                    <td class="px-4 py-2">Gesamt</td>
                    <td class="px-4 py-2 text-center">{{ $summierung->sum('anzahl') }}</td>
                    <td class="px-4 py-2 text-right text-rose-800">+{{ $summierung->sum('gewinn_gesamt') }} Radi</td>
                </tr>
            </tbody>
        </table>
    </div>
    @else
        <div class="rounded-xl border-2 border-emerald-200 bg-emerald-50 p-4 text-emerald-800 font-semibold">
            ✅ Keine verdächtigen Kursgewinne gefunden.
        </div>
    @endif

    {{-- Alle Paare --}}
    <div>
        <h3 class="font-bold text-lg mb-2">Alle Kauf-Verkauf-Paare (nach Gewinn sortiert)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2">Kind</th>
                        <th class="px-3 py-2">Betrieb</th>
                        <th class="px-3 py-2 text-center">Stück</th>
                        <th class="px-3 py-2 text-right">Kauf-Kurs</th>
                        <th class="px-3 py-2 text-right">Vk-Kurs</th>
                        <th class="px-3 py-2 text-right">Δ Kurs</th>
                        <th class="px-3 py-2 text-right">Gewinn</th>
                        <th class="px-3 py-2 text-center">Haltezeit</th>
                        <th class="px-3 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paare->sortByDesc('gewinn_gesamt') as $p)
                        @php
                            $rowClass = '';
                            if ($p['verdaechtig'])       $rowClass = 'bg-rose-50';
                            elseif ($p['schnellverkauf']) $rowClass = 'bg-orange-50';
                        @endphp
                        <tr class="border-t border-slate-100 {{ $rowClass }}">
                            <td class="px-3 py-2 font-semibold">{{ $p['kind'] }}</td>
                            <td class="px-3 py-2">{{ $p['betrieb'] }}</td>
                            <td class="px-3 py-2 text-center">{{ $p['stueck'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $p['kauf_kurs'] }} Radi</td>
                            <td class="px-3 py-2 text-right">{{ $p['verkauf_kurs'] }} Radi</td>
                            <td class="px-3 py-2 text-right font-bold
                                {{ $p['kurs_delta'] > 0 ? 'text-rose-600' : ($p['kurs_delta'] < 0 ? 'text-emerald-600' : 'text-slate-400') }}">
                                {{ $p['kurs_delta'] >= 0 ? '+' : '' }}{{ $p['kurs_delta'] }}
                            </td>
                            <td class="px-3 py-2 text-right font-bold {{ $p['gewinn_gesamt'] > 0 ? 'text-rose-600' : '' }}">
                                {{ $p['gewinn_gesamt'] >= 0 ? '+' : '' }}{{ $p['gewinn_gesamt'] }} Radi
                            </td>
                            <td class="px-3 py-2 text-center">{{ $p['haltedauer_min'] }} Min</td>
                            <td class="px-3 py-2 text-center">
                                @if($p['verdaechtig'])
                                    <span class="inline-block bg-rose-100 text-rose-800 text-xs font-bold px-2 py-0.5 rounded-full">🚨 Verdächtig</span>
                                @elseif($p['schnellverkauf'])
                                    <span class="inline-block bg-orange-100 text-orange-800 text-xs font-bold px-2 py-0.5 rounded-full">⚡ Schnell</span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-slate-500 py-4 text-center">Keine Kauf-Verkauf-Paare vorhanden.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
