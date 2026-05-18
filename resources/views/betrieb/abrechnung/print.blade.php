<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Abrechnung {{ $betrieb->name }} – {{ \Carbon\Carbon::parse($datum)->format('d.m.Y') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 13px; }
        }
        body { font-family: 'Nunito', system-ui, sans-serif; background: white; color: #1e293b; padding: 2rem; }
    </style>
</head>
<body>
    <div class="no-print mb-4 flex gap-3">
        <button onclick="window.print()" class="btn btn-success">
            <i class="fa-solid fa-print"></i> Jetzt drucken
        </button>
        <a href="{{ route('betrieb.abrechnung', ['datum' => $datum]) }}" class="btn btn-ghost">
            ← Zurück
        </a>
    </div>

    <div class="border-b-2 border-slate-300 pb-4 mb-6">
        <h1 class="text-3xl font-extrabold">{{ $betrieb->name }}</h1>
        <div class="text-xl text-slate-600 mt-1">
            Abrechnung für: {{ \Carbon\Carbon::parse($datum)->translatedFormat('l, d. F Y') }}
        </div>
        <div class="text-sm text-slate-400 mt-1">Ausgedruckt: {{ now()->format('d.m.Y H:i') }}</div>
    </div>

    {{-- Kassenbestand --}}
    <div class="mb-6 rounded-2xl border-2 border-slate-300 p-5 text-center">
        <div class="text-sm uppercase font-bold text-slate-500 mb-1">Aktueller Kassenbestand (gesamt)</div>
        <div class="text-4xl font-extrabold {{ $kassenbestand >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
            {{ $kassenbestand }} Radi
        </div>
    </div>

    {{-- Summen --}}
    <table class="min-w-full border-collapse mb-6">
        <thead>
            <tr class="bg-slate-100">
                <th class="border border-slate-300 px-4 py-2 text-left">Einlagen</th>
                <th class="border border-slate-300 px-4 py-2 text-left">Verkäufe</th>
                <th class="border border-slate-300 px-4 py-2 text-left">Entnahmen</th>
                <th class="border border-slate-300 px-4 py-2 text-left font-extrabold">Saldo (Tag)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border border-slate-300 px-4 py-2 font-bold text-emerald-700">{{ $summen['einlagen'] }} Radi</td>
                <td class="border border-slate-300 px-4 py-2 font-bold text-sky-700">{{ $summen['verkaeufe'] }} Radi</td>
                <td class="border border-slate-300 px-4 py-2 font-bold text-rose-700">{{ $summen['entnahmen'] }} Radi</td>
                <td class="border border-slate-300 px-4 py-2 font-extrabold">
                    {{ $summen['einlagen'] + $summen['verkaeufe'] - $summen['entnahmen'] }} Radi
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Einzelbuchungen --}}
    <h2 class="text-xl font-extrabold mb-3">Einzelbuchungen</h2>
    @if($transaktionen->isEmpty())
        <p class="text-slate-500">Keine Buchungen für diesen Tag.</p>
    @else
        <table class="min-w-full border-collapse">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-slate-300 px-3 py-2 text-left">Uhrzeit</th>
                    <th class="border border-slate-300 px-3 py-2 text-left">Typ</th>
                    <th class="border border-slate-300 px-3 py-2 text-left">Betrag</th>
                    <th class="border border-slate-300 px-3 py-2 text-left">Kommentar / Positionen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaktionen as $t)
                    <tr>
                        <td class="border border-slate-300 px-3 py-2 text-sm">{{ $t->created_at->format('H:i') }}</td>
                        <td class="border border-slate-300 px-3 py-2 font-semibold">
                            {{ match($t->type) { 'einlage' => 'Einlage', 'verkauf' => 'Verkauf', 'entnahme' => 'Entnahme', default => $t->type } }}
                        </td>
                        <td class="border border-slate-300 px-3 py-2 font-bold">{{ $t->amount }} Radi</td>
                        <td class="border border-slate-300 px-3 py-2 text-sm">
                            {{ $t->comment }}
                            @if($t->positionen->isNotEmpty())
                                <ul class="list-disc list-inside mt-1">
                                    @foreach($t->positionen as $pos)
                                        <li>{{ $pos->menge }}× {{ $pos->product->name ?? '(gelöschtes Produkt)' }} à {{ $pos->einzelpreis }} Radi</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="mt-8 text-xs text-slate-400 border-t-2 border-slate-200 pt-4">
        Kinderspielstadt-Bank · Abrechnung automatisch erstellt
    </div>
</body>
</html>

