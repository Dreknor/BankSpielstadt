@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-violet-300">
    <h1 class="text-3xl font-extrabold text-violet-700">🔄 Rückkauf durch Betrieb – {{ $customer->name }}</h1>
    <p class="text-slate-700 mt-1">
        Aktueller Wert: <b>{{ $customer->aktien_kurs }} Radi</b> pro Anteil.<br>
        Der Betrieb bringt das Bargeld mit — du gibst es direkt dem Kind weiter.
    </p>

    <form method="POST" action="/boerse/handel/{{ $customer->id }}/rueckkauf" class="mt-4 space-y-4">
        @csrf
        @include('boerse.partials.kind_suche', [
            'endpoint'    => '/boerse/handel/suche/inhaber/' . $customer->id,
            'accent'      => 'violet',
            'idPrefix'    => 'rueckKind',
            'zeigeStueck' => true,
            'maxAttr'     => true,
            'platzhalter' => 'Anteilsinhaber suchen (Name)…',
        ])
        <div>
            <label class="block font-semibold mb-1">Wie viele Anteile?</label>
            <input type="number" name="stueck" id="rueckKind-stueck" min="1" required value="{{ old('stueck', 1) }}"
                   class="w-32 text-2xl text-center border-2 border-slate-300 rounded-xl px-3 py-2"
                   oninput="document.getElementById('rb').innerText = ((parseInt(this.value)||0) * {{ $customer->aktien_kurs }}) + ' Radi'">
        </div>
        <div class="bg-violet-50 border-2 border-violet-200 rounded-xl p-4 text-lg">
            💵 Der Betrieb gibt <b id="rb">{{ $customer->aktien_kurs }} Radi</b> bar.
            Du reichst das Geld direkt an das Kind weiter.
        </div>
        <button class="bg-violet-500 hover:bg-violet-600 text-white text-xl font-bold px-6 py-3 rounded-xl shadow-kid">
            ✅ Rückkauf bestätigen
        </button>
    </form>
</div>
@endsection

