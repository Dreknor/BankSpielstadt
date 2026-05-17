@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-sky-300">
    <h1 class="text-3xl font-extrabold text-sky-700">💵 Anteile verkaufen – {{ $customer->name }}</h1>
    <p class="text-slate-700 mt-1">Aktueller Wert: <b>{{ $customer->aktien_kurs }} Radi</b> pro Anteil ·
       Börsen-Kasse: <b>{{ $kassenstand }} Radi</b></p>

    <form method="POST" action="/boerse/handel/{{ $customer->id }}/verkaufen" class="mt-4 space-y-4">
        @csrf
        @include('boerse.partials.kind_suche', [
            'endpoint'    => '/boerse/handel/suche/inhaber/' . $customer->id,
            'accent'      => 'sky',
            'idPrefix'    => 'verkKind',
            'zeigeStueck' => true,
            'maxAttr'     => true,
            'platzhalter' => 'Anteilsinhaber suchen (Name)…',
        ])
        <div>
            <label class="block font-semibold mb-1">Wie viele Anteile?</label>
            <input type="number" name="stueck" id="verkKind-stueck" min="1" required value="{{ old('stueck', 1) }}"
                   class="w-32 text-2xl text-center border-2 border-slate-300 rounded-xl px-3 py-2"
                   oninput="document.getElementById('aus').innerText = ((parseInt(this.value)||0) * {{ $customer->aktien_kurs }}) + ' Radi'">
        </div>
        <div class="bg-sky-50 border-2 border-sky-200 rounded-xl p-4 text-lg">
            💵 Das Kind bekommt <b id="aus">{{ $customer->aktien_kurs }} Radi</b> bar ausgezahlt.
        </div>
        <div class="flex gap-2">
            <button class="bg-sky-500 hover:bg-sky-600 text-white text-xl font-bold px-6 py-3 rounded-xl shadow-kid">
                ✅ Verkauf bestätigen
            </button>
            <a href="/boerse/handel" class="bg-slate-200 font-bold px-6 py-3 rounded-xl">Abbrechen</a>
        </div>
    </form>
</div>
@endsection

