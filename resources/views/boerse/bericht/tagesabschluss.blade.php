@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200">
    <h1 class="text-3xl font-extrabold text-amber-700">📋 Tagesbericht</h1>
    <p class="text-slate-700">Übersicht aller Vorgänge von heute.</p>
    <div class="mt-2">Börsen-Kasse aktuell: <b>{{ $kassenstand }} Radi</b></div>
</div>

<div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-amber-200">
    <h2 class="text-xl font-bold mb-3">Aktive Betriebe</h2>
    <table class="w-full">
        <thead class="text-left text-slate-600">
            <tr><th>Betrieb</th><th>Wert</th><th>Verkauft</th></tr>
        </thead>
        <tbody>
        @foreach($betriebe as $b)
            <tr class="border-t border-amber-100">
                <td class="py-2 font-bold">{{ $b->name }}</td>
                <td>{{ $b->aktien_kurs }} Radi</td>
                <td>{{ $b->anteileVerkauft() }} / {{ $b->aktien_gesamt }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-amber-200">
    <h2 class="text-xl font-bold mb-3">Transaktionen heute</h2>
    @if($transaktionen->isEmpty())
        <p class="text-slate-500">Keine.</p>
    @else
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500"><tr>
                <th>Zeit</th><th>Typ</th><th>Kind</th><th>Betrieb</th><th>Stück</th><th>Summe</th>
            </tr></thead>
            <tbody>
            @foreach($transaktionen as $t)
                <tr class="border-t border-amber-100">
                    <td class="py-1">{{ $t->created_at?->format('H:i') }}</td>
                    <td class="font-bold">{{ $t->typ }}</td>
                    <td>{{ $t->kind?->name }}</td>
                    <td>{{ $t->betrieb?->name }}</td>
                    <td>{{ $t->stueck }}</td>
                    <td>{{ $t->summe }} Radi</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

