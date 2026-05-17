@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-4">
    <h2 class="text-2xl font-extrabold">📋 Tagesbericht Börse</h2>

    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-xl border-2 p-4 bg-slate-50">
            <div class="text-sm text-slate-500">Börsen-Kasse aktuell</div>
            <div class="text-3xl font-extrabold">{{ $kassenstand }} Radi</div>
        </div>
        <div class="rounded-xl border-2 p-4 bg-slate-50">
            <div class="text-sm text-slate-500">Aktive Betriebe</div>
            <div class="text-3xl font-extrabold">{{ $betriebe->count() }}</div>
        </div>
        <div class="rounded-xl border-2 p-4 bg-amber-50">
            <div class="text-sm text-amber-700">Gebühren-Einnahmen heute</div>
            <div class="text-3xl font-extrabold text-amber-700">{{ $gebuehrSumme }} Radi</div>
        </div>
    </div>

    <h3 class="text-lg font-bold mt-4">Transaktionen heute</h3>
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500"><tr>
            <th>Zeit</th><th>Typ</th><th>Kind</th><th>Betrieb</th><th>Stück</th><th>Kurs</th><th>Summe</th>
        </tr></thead>
        <tbody>
        @forelse($transaktionen as $t)
            <tr class="border-t border-slate-100">
                <td>{{ $t->created_at?->format('H:i') }}</td>
                <td class="font-bold">{{ $t->typ }}</td>
                <td>{{ $t->kind?->name }}</td>
                <td>{{ $t->betrieb?->name }}</td>
                <td>{{ $t->stueck }}</td>
                <td>{{ $t->kurs }}</td>
                <td>{{ $t->summe }} Radi</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-slate-500 py-2">Heute keine Transaktionen.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h3 class="text-lg font-bold mt-4">Kassenbewegungen heute</h3>
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500"><tr>
            <th>Zeit</th><th>Typ</th><th>Betrag</th><th>Notiz</th>
        </tr></thead>
        <tbody>
        @forelse($kassenbewegungen as $k)
            @php $minus = in_array($k->typ, ['verkauf_auszahlung','rueckkauf_auszahlung','entnahme','abschluss_auszahlung']); @endphp
            <tr class="border-t border-slate-100">
                <td>{{ $k->created_at?->format('H:i') }}</td>
                <td>{{ $k->typ }}</td>
                <td class="font-bold {{ $minus ? 'text-rose-600' : 'text-emerald-600' }}">{{ $minus ? '-' : '+' }}{{ $k->betrag }}</td>
                <td>{{ $k->notiz }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-slate-500 py-2">Heute keine Bewegungen.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div><a href="/admin/boerse" class="btn">🔙 Zurück</a></div>
</div>
@endsection


