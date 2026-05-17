@extends('layouts.app')

@section('content')

<div class="mb-4 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-2xl font-extrabold flex items-center gap-2">
        <i class="fa-solid fa-cash-register text-brand-600"></i>
        Kasse: {{ $betrieb->name }}
    </h2>
    <div class="flex gap-2 flex-wrap">
        <form method="GET" class="flex gap-2 items-center">
            <input type="date" name="datum" value="{{ $datum }}" class="field py-2 w-auto">
            <button type="submit" class="btn btn-ghost py-2"><i class="fa-solid fa-filter"></i> Filtern</button>
        </form>
        <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost py-2">
            ← Zurück
        </a>
    </div>
</div>

<div class="card p-5 text-center mb-6">
    <div class="text-sm uppercase text-slate-500 font-bold mb-1">Kassenbestand gesamt</div>
    <div class="text-5xl font-extrabold {{ $kassenbestand >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
        {{ $kassenbestand }} Radi
    </div>
</div>

<div class="card">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-5 border-b-2 border-slate-100">
        <div class="rounded-2xl bg-emerald-50 p-4 text-center">
            <div class="text-sm text-emerald-700 font-semibold uppercase">Einlagen</div>
            <div class="text-2xl font-extrabold text-emerald-700">{{ $summen['einlagen'] }} Radi</div>
        </div>
        <div class="rounded-2xl bg-sky-50 p-4 text-center">
            <div class="text-sm text-sky-700 font-semibold uppercase">Verkäufe</div>
            <div class="text-2xl font-extrabold text-sky-700">{{ $summen['verkaeufe'] }} Radi</div>
        </div>
        <div class="rounded-2xl bg-rose-50 p-4 text-center">
            <div class="text-sm text-rose-700 font-semibold uppercase">Entnahmen</div>
            <div class="text-2xl font-extrabold text-rose-700">{{ $summen['entnahmen'] }} Radi</div>
        </div>
    </div>

    <div class="p-5">
        @if($transaktionen->isEmpty())
            <p class="text-slate-500 text-center py-6">Keine Buchungen für diesen Tag.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="text-sm uppercase text-slate-500 border-b-2 border-slate-200">
                        <tr>
                            <th class="px-3 py-2">Datum/Zeit</th>
                            <th class="px-3 py-2">Typ</th>
                            <th class="px-3 py-2">Betrag</th>
                            <th class="px-3 py-2">Kommentar / Positionen</th>
                            <th class="px-3 py-2"></th>
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
                            @endphp
                            <tr>
                                <td class="px-3 py-3 text-sm text-slate-500">{{ $t->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-3 py-3 font-bold {{ $color }}">
                                    {{ match($t->type) { 'einlage' => 'Einlage', 'verkauf' => 'Verkauf', 'entnahme' => 'Entnahme', default => $t->type } }}
                                </td>
                                <td class="px-3 py-3 font-extrabold {{ $color }}">{{ $t->amount }} Radi</td>
                                <td class="px-3 py-3 text-sm text-slate-600">
                                    {{ $t->comment }}
                                    @if($t->positionen->isNotEmpty())
                                        <ul class="list-disc list-inside mt-1">
                                            @foreach($t->positionen as $pos)
                                                <li>{{ $pos->menge }}× {{ $pos->product->name }} à {{ $pos->einzelpreis }} Radi</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <form method="POST" action="{{ route('admin.betriebe.transaktion.delete', $t) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger text-sm py-2 px-3"
                                            onclick="return confirm('Buchung wirklich löschen?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
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

