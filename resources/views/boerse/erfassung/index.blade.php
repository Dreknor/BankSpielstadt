@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-sky-300">
    <h1 class="text-3xl font-extrabold text-sky-700">🔭 Erfassung der Angestelltenzahl</h1>
    <p class="text-slate-700 mt-1">
        Geh zu jedem Betrieb, zähle die arbeitenden Kinder und trag die Zahl unten ein.
        Das beeinflusst den Kurs!
    </p>
</div>

@if($betriebe->isEmpty())
    <div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200 text-slate-500">
        Noch keine Betriebe an der Börse.
    </div>
@else
    @foreach($betriebe as $b)
        @php $letzte = $b->letzteBeobachtung(); @endphp
        <div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-amber-200">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="text-xl font-extrabold">{{ $b->name }}</div>
                    <div class="text-sm text-slate-600">
                        Aktueller Wert: <b>{{ $b->aktien_kurs }} Radi</b> ·
                        Letzte Beobachtung:
                        @if($letzte !== null)
                            <b>{{ $letzte }}</b> Angestellte
                        @else
                            <span class="text-rose-600">noch keine!</span>
                        @endif
                    </div>
                </div>
                <a href="/boerse/erfassung/vorschau/{{ $b->id }}" class="bg-slate-200 hover:bg-slate-300 font-bold px-3 py-2 rounded-xl">🔮 Vorschau</a>
            </div>

            <form method="POST" action="/boerse/erfassung/{{ $b->id }}" class="mt-3 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-semibold">Angestellte:</label>
                    <input type="number" name="angestellte" min="0" max="50" required
                           class="w-24 text-2xl text-center border-2 border-slate-300 rounded-xl px-2 py-1">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold">Eindruck (kurz):</label>
                    <input type="text" name="notiz" maxlength="100" placeholder="z. B. voll, ruhig, leer"
                           class="w-full border-2 border-slate-300 rounded-xl px-3 py-1">
                </div>
                <button class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-5 py-2 rounded-xl">✅ Speichern</button>
            </form>
        </div>
    @endforeach
@endif
@endsection

