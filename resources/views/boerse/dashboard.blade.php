@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200">
    <h1 class="text-3xl font-extrabold text-amber-700 mb-2">📈 Willkommen an der Radi-Börse!</h1>
    <p class="text-slate-700 text-lg">Wähle aus, was du jetzt tun möchtest:</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <a href="/boerse/handel" class="block bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200 hover:border-amber-500 transition">
        <div class="text-5xl mb-2">🤝</div>
        <div class="text-2xl font-bold">Handel</div>
        <p class="text-slate-600">Anteile kaufen, verkaufen oder Rückkauf erfassen.</p>
    </a>
    <a href="/boerse/erfassung" class="block bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200 hover:border-amber-500 transition">
        <div class="text-5xl mb-2">🔭</div>
        <div class="text-2xl font-bold">Erfassung</div>
        <p class="text-slate-600">Anzahl Mitarbeiter eintragen — jede Stunde!</p>
    </a>
    <a href="/boerse/kasse" class="block bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200 hover:border-amber-500 transition">
        <div class="text-5xl mb-2">💰</div>
        <div class="text-2xl font-bold">Kasse</div>
        <p class="text-slate-600">Bargeldbestand prüfen und bestätigen.</p>
    </a>
    <a href="/boerse/kurse" class="block bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200 hover:border-amber-500 transition">
        <div class="text-5xl mb-2">📊</div>
        <div class="text-2xl font-bold">Kurse</div>
        <p class="text-slate-600">Kurstafel drucken und Kursverlauf ansehen.</p>
    </a>
</div>

<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200">
    <h2 class="text-xl font-bold text-amber-700 mb-3">🏪 Betriebe an der Börse</h2>
    @if($betriebe->isEmpty())
        <p class="text-slate-500">Noch keine Betriebe freigeschaltet. Die Lehrkraft macht das.</p>
    @else
        <table class="w-full">
            <thead class="text-left text-slate-600">
                <tr><th class="py-2">Betrieb</th><th>Aktueller Wert</th><th>Anteile gesamt</th></tr>
            </thead>
            <tbody>
                @foreach($betriebe as $b)
                <tr class="border-t border-amber-100">
                    <td class="py-2 font-bold">{{ $b->name }}</td>
                    <td>{{ $b->aktien_kurs }} Radi</td>
                    <td>{{ $b->aktien_gesamt }} Stück</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

