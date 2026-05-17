@extends('betrieb.layouts.app')

@section('content')

{{-- Kassenbestand --}}
<div class="card p-6 text-center mb-6">
    <div class="text-sm text-slate-500 uppercase font-bold mb-1">Aktueller Kassenbestand</div>
    <div class="text-6xl font-extrabold {{ $kassenbestand >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
        {{ $kassenbestand }} <span class="text-3xl">Radi</span>
    </div>
</div>

<div class="card">
    <div class="p-5 border-b-2 border-slate-100 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-extrabold flex items-center gap-2">
            <i class="fa-solid fa-chart-bar text-emerald-600"></i> Abrechnung
        </h2>
        <div class="flex gap-2 items-center flex-wrap">
            <form method="GET" action="{{ route('betrieb.abrechnung') }}" class="flex gap-2 items-center">
                <input type="date" name="datum" value="{{ $datum }}" class="field py-2 w-auto">
                <button type="submit" class="btn btn-ghost py-2">
                    <i class="fa-solid fa-filter"></i> Filtern
                </button>
            </form>
            <a href="{{ route('betrieb.abrechnung.print', ['datum' => $datum]) }}"
               target="_blank" class="btn btn-info py-2">
                <i class="fa-solid fa-print"></i> Drucken / PDF
            </a>
        </div>
    </div>

    {{-- Summen --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-5 border-b-2 border-slate-100">
        <div class="rounded-2xl bg-emerald-50 p-4 text-center">
            <div class="text-sm text-emerald-700 font-semibold uppercase">Einlagen</div>
            <div class="text-2xl font-extrabold text-emerald-700">{{ $summen['einlagen'] }} Radi</div>
        </div>
        <div class="rounded-2xl bg-sky-50 p-4 text-center">
            <div class="text-sm text-sky-700 font-semibold uppercase">Verkäufe (bar)</div>
            <div class="text-2xl font-extrabold text-sky-700">{{ $summen['verkaeufe'] }} Radi</div>
        </div>
        <div class="rounded-2xl bg-rose-50 p-4 text-center">
            <div class="text-sm text-rose-700 font-semibold uppercase">Entnahmen</div>
            <div class="text-2xl font-extrabold text-rose-700">{{ $summen['entnahmen'] }} Radi</div>
        </div>
    </div>

    {{-- Transaktionsliste --}}
    <div class="p-5">
        @if($transaktionen->isEmpty())
            <p class="text-slate-500 text-center py-6">Keine Buchungen für diesen Tag.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="text-sm uppercase text-slate-500 border-b-2 border-slate-200">
                        <tr>
                            <th class="px-3 py-2">Uhrzeit</th>
                            <th class="px-3 py-2">Typ</th>
                            <th class="px-3 py-2">Betrag</th>
                            <th class="px-3 py-2">Kommentar / Positionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transaktionen as $t)
                            @php
                                $color = match($t->type) {
                                    'einlage' => 'text-emerald-700',
                                    'verkauf' => 'text-sky-700',
                                    'entnahme' => 'text-rose-700',
                                    default => ''
                                };
                                $label = match($t->type) {
                                    'einlage' => 'Einlage',
                                    'verkauf' => 'Verkauf',
                                    'entnahme' => 'Entnahme',
                                    default => $t->type
                                };
                            @endphp
                            <tr>
                                <td class="px-3 py-3 text-slate-500 text-sm">{{ $t->created_at->format('H:i') }}</td>
                                <td class="px-3 py-3">
                                    <span class="rounded-xl px-3 py-1 text-sm font-bold {{ $color }} bg-slate-100">{{ $label }}</span>
                                </td>
                                <td class="px-3 py-3 font-extrabold {{ $color }}">{{ $t->amount }} Radi</td>
                                <td class="px-3 py-3 text-slate-600 text-sm">
                                    {{ $t->comment }}
                                    @if($t->positionen->isNotEmpty())
                                        <ul class="mt-1 list-disc list-inside">
                                            @foreach($t->positionen as $pos)
                                                <li>{{ $pos->menge }}× {{ $pos->product->name }} à {{ $pos->einzelpreis }} Radi</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

