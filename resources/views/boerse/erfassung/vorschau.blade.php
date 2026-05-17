@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-sky-300">
    <h1 class="text-3xl font-extrabold text-sky-700">🔮 Kursvorschau – {{ $customer->name }}</h1>
    <p class="text-slate-700 mt-1">Das passiert ungefähr bei der nächsten Kursberechnung:</p>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-50 border-2 border-slate-200 rounded-2xl p-4 text-center">
            <div class="text-sm text-slate-500">Aktueller Wert</div>
            <div class="text-4xl font-extrabold">{{ $alterKurs }} Radi</div>
        </div>
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-4 text-center">
            <div class="text-sm text-slate-500">Angestellte zuletzt</div>
            <div class="text-4xl font-extrabold">
                {{ $letzteBeob !== null ? $letzteBeob : '—' }}
            </div>
            <div class="text-sm text-slate-600">
                Bonus: {{ $angestelltenDelta >= 0 ? '+' : '' }}{{ $angestelltenDelta }}
            </div>
        </div>
        <div class="bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-4 text-center">
            <div class="text-sm text-slate-500">Geschätzter neuer Wert</div>
            <div class="text-4xl font-extrabold text-emerald-700">{{ $vorschauKurs }} Radi</div>
            <div class="text-sm">
                @if($vorschauKurs > $alterKurs) 🙂 würde steigen
                @elseif($vorschauKurs < $alterKurs) 😕 würde fallen
                @else 😐 unverändert
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 text-sm text-slate-500">
        Hinweis: Die echte Berechnung berücksichtigt auch den Umsatz des Betriebs der letzten Stunde.
    </div>
    <div class="mt-4">
        <a href="/boerse/erfassung" class="bg-slate-200 font-bold px-4 py-2 rounded-xl">🔙 Zurück</a>
    </div>
</div>
@endsection

