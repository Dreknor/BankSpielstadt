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
@endsection

