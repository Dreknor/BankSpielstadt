<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Kurstafel – Radi-Börse</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 12mm; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="bg-white text-slate-800">
<div class="max-w-[210mm] mx-auto p-6">
    <div class="no-print mb-4 flex gap-2">
        <button onclick="window.print()" class="bg-amber-500 text-white font-bold px-4 py-2 rounded-xl">🖨️ Drucken</button>
        <a href="/boerse/kurse" class="bg-slate-200 font-bold px-4 py-2 rounded-xl">🔙 Zurück</a>
        <span class="text-sm text-slate-500 self-center">Mit dem Aufruf wurde „Kurstafel ist ausgehängt" automatisch bestätigt ✅</span>
    </div>

    <div class="text-center mb-6">
        <div class="text-7xl">📈</div>
        <h1 class="text-5xl font-extrabold text-amber-700">Radi-Börse</h1>
        <div class="text-2xl mt-2">Kurstafel — {{ now()->format('d.m.Y H:i') }} Uhr</div>
    </div>

    <table class="w-full text-3xl">
        <thead>
            <tr class="border-b-4 border-amber-500 text-left">
                <th class="py-3">Betrieb</th>
                <th class="py-3 text-right">Wert je Anteil</th>
                <th class="py-3 text-right">Verfügbar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($betriebe as $b)
                <tr class="border-b-2 border-amber-100">
                    <td class="py-4 font-extrabold">{{ $b->name }}</td>
                    <td class="py-4 text-right font-extrabold text-amber-600">{{ $b->aktien_kurs }} Radi</td>
                    <td class="py-4 text-right">{{ $b->anteileEigen() }} / {{ $b->aktien_gesamt }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-8 text-center text-slate-500 text-sm">
        Die nächste Aktualisierung erfolgt automatisch zur vollen Stunde.
    </div>
</div>
</body>
</html>

