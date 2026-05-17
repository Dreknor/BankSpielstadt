@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200">
    <h1 class="text-3xl font-extrabold text-amber-700">📈 Kursverlauf – {{ $customer->name }}</h1>
    <div class="text-3xl font-bold mt-2">Aktueller Wert: <span class="text-amber-600">{{ $customer->aktien_kurs }} Radi</span></div>
</div>

<div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-amber-200">
    <h2 class="text-xl font-bold mb-3">Letzte Änderungen</h2>
    @if($kurse->isEmpty())
        <p class="text-slate-500">Noch keine Kursdaten.</p>
    @else
        <div class="flex flex-wrap gap-2 items-end mb-4">
            @foreach($kurse as $k)
                @php
                    $bg = $k->kurs > $k->vorher ? 'bg-emerald-200' : ($k->kurs < $k->vorher ? 'bg-rose-200' : 'bg-slate-200');
                @endphp
                <div class="text-center">
                    <div class="{{ $bg }} rounded-lg px-3 py-2 font-bold text-lg">{{ $k->kurs }}</div>
                    <div class="text-xs text-slate-500 mt-1">{{ $k->created_at?->format('H:i') }}</div>
                </div>
            @endforeach
        </div>
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500"><tr>
                <th>Zeit</th><th>Vorher</th><th>Neu</th><th>Δ</th><th>Grund</th>
            </tr></thead>
            <tbody>
                @foreach($kurse->reverse() as $k)
                    @php $delta = $k->kurs - $k->vorher; @endphp
                    <tr class="border-t border-amber-100">
                        <td class="py-1">{{ $k->created_at?->format('d.m. H:i') }}</td>
                        <td>{{ $k->vorher }}</td>
                        <td class="font-bold">{{ $k->kurs }}</td>
                        <td class="font-bold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-rose-600' : 'text-slate-500') }}">
                            {{ $delta > 0 ? '+' : '' }}{{ $delta }}
                        </td>
                        <td class="text-slate-600">{{ $k->grund }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <div class="mt-4"><a href="/boerse/kurse" class="bg-slate-200 font-bold px-4 py-2 rounded-xl">🔙 Zurück</a></div>
</div>
@endsection

