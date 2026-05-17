<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Jobkarten Radi-Börse</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 8mm; }
        @media print { .no-print { display: none !important; } }
        .karte { page-break-inside: avoid; }
    </style>
</head>
<body class="bg-white text-slate-800">
<div class="max-w-[210mm] mx-auto p-4">
    <div class="no-print mb-4 flex gap-2">
        <button onclick="window.print()" class="bg-amber-500 text-white font-bold px-4 py-2 rounded-xl">🖨️ Drucken</button>
        <a href="/boerse/hilfe" class="bg-slate-200 font-bold px-4 py-2 rounded-xl">🔙 Zurück</a>
    </div>

    @php
        $jobs = [
            ['icon'=>'🤝','titel'=>'Der Händler','farbe'=>'border-emerald-500','aufgaben'=>[
                'Kind möchte Anteile kaufen → „Kauf erfassen"',
                'Kind möchte verkaufen → „Verkauf erfassen"',
                'Betrieb kauft zurück → „Rückkauf erfassen"',
                'Stündlich Kasse abgleichen',
            ]],
            ['icon'=>'🔭','titel'=>'Der Kursbeobachter','farbe'=>'border-sky-500','aufgaben'=>[
                'JEDE STUNDE: Geh zu jedem Betrieb',
                'Zähle, wie viele Kinder dort arbeiten',
                'Trag die Zahl ein („Erfassung")',
                'Schreibe einen Eindruck dazu',
            ]],
            ['icon'=>'💰','titel'=>'Der Kassenwart','farbe'=>'border-amber-500','aufgaben'=>[
                'Stündlich „Kassenstand bestätigen" klicken',
                'Geldeinlage / -entnahme buchen',
                'Bei wenig Geld: Lehrkraft holen!',
                'Tagesende: Kassenabschluss',
            ]],
            ['icon'=>'📰','titel'=>'Der Börsenwart','farbe'=>'border-rose-500','aufgaben'=>[
                'Nach jeder Kursberechnung Tafel drucken',
                'Aushängen + „Kurstafel ist ausgehängt"',
                'Fragen der Kinder beantworten',
                'Tagesende: Tagesbericht ausgeben',
            ]],
        ];
    @endphp

    <div class="grid grid-cols-2 gap-3">
        @foreach($jobs as $job)
            <div class="karte border-4 {{ $job['farbe'] }} rounded-2xl p-4 h-[135mm]">
                <div class="text-6xl text-center">{{ $job['icon'] }}</div>
                <div class="text-3xl font-extrabold text-center mt-2">{{ $job['titel'] }}</div>
                <ol class="mt-4 space-y-2 text-lg">
                    @foreach($job['aufgaben'] as $i => $a)
                        <li><span class="font-bold">{{ $i+1 }}.</span> {{ $a }}</li>
                    @endforeach
                </ol>
                <div class="mt-6 pt-4 border-t border-dashed text-base">
                    <span class="font-bold">Name: </span>
                    <span class="inline-block border-b border-slate-500 w-48">&nbsp;</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>

