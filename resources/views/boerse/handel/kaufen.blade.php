@extends('boerse.layouts.app')
@section('content')
@php
    $gebuehr    = (int) config('bank.aktien.kauf_gebuehr', 1);
    $maxAnteile = (int) config('bank.aktien.max_anteile_je_kind', 10);
@endphp
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-emerald-300">
    <h1 class="text-3xl font-extrabold text-emerald-700">🛒 Anteile kaufen – {{ $customer->name }}</h1>
    <p class="text-slate-700 mt-1">
        Aktueller Wert: <b>{{ $customer->aktien_kurs }} Radi</b> pro Anteil ·
        Frei: <b>{{ $customer->anteileEigen() }}</b> Anteile
        @if($gebuehr > 0)
            · <span class="text-amber-700">Bearbeitungsgebühr der Börse: <b>{{ $gebuehr }} Radi</b> je Kauf</span>
        @endif
    </p>
    <p class="text-slate-500 text-sm mt-1">
        📌 Jedes Kind darf höchstens <b>{{ $maxAnteile }} Anteile</b> von diesem Betrieb besitzen.
    </p>

    <form method="POST" action="/boerse/handel/{{ $customer->id }}/kaufen" class="mt-4 space-y-4">
        @csrf
        @include('boerse.partials.kind_suche', [
            'endpoint'    => '/boerse/handel/suche/kinder',
            'accent'      => 'emerald',
            'idPrefix'    => 'kaufKind',
            'platzhalter' => 'Name des Kindes tippen…',
        ])

        {{-- Aktueller Bestand wird per JS nach der Kindauswahl befüllt --}}
        <div id="bestand-hinweis" class="hidden rounded-xl bg-blue-50 border-2 border-blue-200 px-4 py-2 text-sm text-blue-800">
            Dieser Besitz bereits: <b id="bestand-aktuell">0</b> Anteile ·
            Noch möglich: <b id="bestand-rest">{{ $maxAnteile }}</b> Anteile
        </div>

        <div>
            <label class="block font-semibold mb-1">Wie viele Anteile?</label>
            <input type="number" name="stueck" id="kaufKind-stueck" min="1" max="{{ $maxAnteile }}" required value="{{ old('stueck', 1) }}"
                   class="w-32 text-2xl text-center border-2 border-slate-300 rounded-xl px-3 py-2"
                   oninput="
                        var s = parseInt(this.value)||0;
                        var k = {{ $customer->aktien_kurs }};
                        var g = {{ $gebuehr }};
                        document.getElementById('anteile').innerText = (s*k) + ' Radi';
                        document.getElementById('gebuehr').innerText = g + ' Radi';
                        document.getElementById('preis').innerText = (s*k + g) + ' Radi';
                   ">
        </div>
        <div class="bg-emerald-50 border-2 border-emerald-200 rounded-xl p-4 text-lg space-y-1">
            <div>Anteile: <b id="anteile">{{ $customer->aktien_kurs }} Radi</b></div>
            @if($gebuehr > 0)
                <div>Gebühr der Börse: <b id="gebuehr">{{ $gebuehr }} Radi</b></div>
            @endif
            <div class="border-t pt-1 text-xl">
                💵 Das Kind muss insgesamt <b id="preis">{{ $customer->aktien_kurs + $gebuehr }} Radi</b> bar bezahlen.
            </div>
        </div>
        @if(session('bestaetigung_noetig'))
            <input type="hidden" name="bestaetigt" value="1">
            <div class="bg-amber-100 border-2 border-amber-400 rounded-xl p-3 font-bold text-amber-900">
                ⚠️ Bitte nochmal klicken zum Bestätigen.
            </div>
        @endif
        <div class="flex gap-2">
            <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-xl font-bold px-6 py-3 rounded-xl shadow-kid">
                ✅ Kauf bestätigen
            </button>
            <a href="/boerse/handel" class="bg-slate-200 font-bold px-6 py-3 rounded-xl">Abbrechen</a>
        </div>
    </form>
</div>

<script>
// Bestand des ausgewählten Kindes nachladen und Eingabefeld begrenzen
(function () {
    var maxAnteile = {{ $maxAnteile }};
    var buisnessId = {{ $customer->id }};

    function onKindGewaehlt(customerId) {
        if (!customerId) {
            document.getElementById('bestand-hinweis').classList.add('hidden');
            document.getElementById('kaufKind-stueck').max = maxAnteile;
            return;
        }
        fetch('/boerse/handel/suche/inhaber/' + buisnessId + '?q=')
            .then(r => r.json())
            .then(function (list) {
                var eintrag = list.find(function (k) { return k.id == customerId; });
                var aktuell = eintrag ? eintrag.stueck : 0;
                var rest    = Math.max(0, maxAnteile - aktuell);
                document.getElementById('bestand-aktuell').innerText = aktuell;
                document.getElementById('bestand-rest').innerText    = rest;
                document.getElementById('kaufKind-stueck').max        = rest;
                if (rest < parseInt(document.getElementById('kaufKind-stueck').value || 1)) {
                    document.getElementById('kaufKind-stueck').value = Math.max(1, rest);
                    document.getElementById('kaufKind-stueck').dispatchEvent(new Event('input'));
                }
                document.getElementById('bestand-hinweis').classList.toggle('hidden', aktuell === 0 && rest === maxAnteile);
            })
            .catch(function () {});
    }

    // Auf das versteckte Input-Feld lauschen (von kind_suche partial gesetzt)
    var hiddenInput = document.getElementById('kaufKind-customer_id');
    if (hiddenInput) {
        // Bereits vorbelegt (z.B. nach Fehler mit old())
        if (hiddenInput.value) onKindGewaehlt(hiddenInput.value);

        var observer = new MutationObserver(function () { onKindGewaehlt(hiddenInput.value); });
        observer.observe(hiddenInput, { attributes: true, attributeFilter: ['value'] });
        hiddenInput.addEventListener('change', function () { onKindGewaehlt(this.value); });
    }
})();
</script>
@endsection


