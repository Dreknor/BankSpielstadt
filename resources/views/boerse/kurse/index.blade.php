@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200">
    <h1 class="text-3xl font-extrabold text-amber-700">📊 Kurse aller Betriebe</h1>
    <div class="mt-3 flex gap-2 flex-wrap">
        <a href="/boerse/anzeige" target="_blank" class="bg-slate-700 hover:bg-slate-600 text-white font-bold px-4 py-2 rounded-xl">🖥️ Monitor-Anzeige öffnen</a>
        <a href="/boerse/bericht/kurstafel" target="_blank" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl">🖨️ Kurstafel drucken</a>
        <form method="POST" action="/boerse/bericht/kurstafel/bestaetigen" class="inline">
            @csrf
            <button class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-4 py-2 rounded-xl">✅ Kurstafel ist ausgehängt</button>
        </form>
        <a href="/boerse/bericht/tagesabschluss" class="bg-slate-200 hover:bg-slate-300 font-bold px-4 py-2 rounded-xl">📋 Tagesbericht</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-amber-200">
    @if($betriebe->isEmpty())
        <p class="text-slate-500">Noch keine Betriebe an der Börse.</p>
    @else
        <table class="w-full">
            <thead class="text-left text-slate-600">
                <tr><th class="py-2">Betrieb</th><th>Wert</th><th>Anteile verkauft</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($betriebe as $b)
                    <tr class="border-t border-amber-100">
                        <td class="py-2 font-bold">{{ $b->name }}</td>
                        <td class="text-xl font-bold text-amber-600">{{ $b->aktien_kurs }} Radi</td>
                        <td>{{ $b->anteileVerkauft() }} / {{ $b->aktien_gesamt }}</td>
                        <td><a href="/boerse/kurse/{{ $b->id }}" class="text-amber-700 underline">Verlauf →</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

