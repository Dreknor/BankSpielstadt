<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Bank') }} – Export</title>
    @vite(['resources/css/app.css'])
</head>
<body class="p-6 bg-slate-50 font-sans">
    @php \Carbon\Carbon::setlocale(config('app.locale')); @endphp
    <table class="min-w-full border-collapse">
        <thead>
            <tr class="bg-slate-200">
                <th colspan="7" class="border border-slate-400 px-3 py-2 text-left text-lg font-extrabold">
                    Übersicht Betriebe
                </th>
            </tr>
            <tr class="bg-slate-100">
                <th class="border border-slate-400 px-3 py-2 text-left">Name</th>
                @for($x = \Carbon\Carbon::today()->startOfWeek(); $x < \Carbon\Carbon::today()->endOfWeek()->subDays(2); $x->addDay())
                    <th class="border border-slate-400 px-3 py-2 text-left">{{ $x->translatedFormat('l') }}</th>
                @endfor
                <th class="border border-slate-400 px-3 py-2 text-left">Gesamt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $buisness)
                <tr>
                    <td class="border border-slate-400 px-3 py-2 font-semibold">{{ $buisness->name }}</td>
                    @for($y = \Carbon\Carbon::today()->startOfWeek(); $y < \Carbon\Carbon::today()->endOfWeek()->subDays(2); $y->addDay())
                        <td class="border border-slate-400 px-3 py-2">{{ $buisness->daily_balance($y) }}</td>
                    @endfor
                    <td class="border border-slate-400 px-3 py-2 font-bold">
                        {{ $buisness->payments()
                            ->whereNot('comment', 'LIKE', 'Kredit')
                            ->whereNot('comment', 'LIKE', 'Startkapital')
                            ->sum('amount') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
