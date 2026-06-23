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
                   class="w-32 text-2xl text-center border-2 border-slate-300 rounded-xl px-3 py-2">
        </div>
        <div class="bg-sky-50 border-2 border-sky-200 rounded-xl p-4 text-lg">
            💵 Das Kind bekommt <b id="aus">–</b> bar ausgezahlt.
            <div id="kurs-hinweis" class="hidden mt-1 text-sm text-amber-700 font-semibold"></div>
        </div>
        <div class="flex gap-2">
            <button class="bg-sky-500 hover:bg-sky-600 text-white text-xl font-bold px-6 py-3 rounded-xl shadow-kid">
                ✅ Verkauf bestätigen
            </button>
            <a href="/boerse/handel" class="bg-slate-200 font-bold px-6 py-3 rounded-xl">Abbrechen</a>
        </div>
    </form>
</div>

@push('js')
<script>
(function () {
    const kursDefault = {{ $customer->aktien_kurs }};

    // Aktuell gewähltes Kind (null = kein Kind gewählt)
    let normalKurs   = null;
    let billigStueck = 0;
    let billigKurs   = 0;

    const stueckIn  = document.getElementById('verkKind-stueck');
    const ausEl     = document.getElementById('aus');
    const hinweisEl = document.getElementById('kurs-hinweis');

    function updateAuszahlung() {
        if (normalKurs === null) {
            ausEl.innerText = '–';
            hinweisEl.classList.add('hidden');
            hinweisEl.innerHTML = '';
            return;
        }
        const stueck = parseInt(stueckIn?.value || 0);
        if (stueck <= 0) {
            ausEl.innerText = '0 Radi';
            hinweisEl.classList.add('hidden');
            return;
        }

        // FIFO: zuerst billig eingekaufte Anteile, dann normale
        const billigVerkauft = Math.min(stueck, billigStueck);
        const normalVerkauft = stueck - billigVerkauft;
        const summe = (billigVerkauft * billigKurs) + (normalVerkauft * normalKurs);

        ausEl.innerText = summe + ' Radi';

        if (billigVerkauft > 0) {
            hinweisEl.classList.remove('hidden');
            if (normalVerkauft > 0) {
                hinweisEl.innerHTML =
                    '⚠️ Gemischter Verkauf: '
                    + billigVerkauft + '× <b>' + billigKurs + ' Radi</b> (unter Mindestpreis eingekauft) '
                    + '+ ' + normalVerkauft + '× <b>' + normalKurs + ' Radi</b>';
            } else {
                hinweisEl.innerHTML =
                    '⚠️ Auszahlung zum Einkaufspreis (<b>' + billigKurs + ' Radi/Anteil</b>), '
                    + 'da unter dem Mindestpreis eingekauft.';
            }
        } else {
            hinweisEl.classList.add('hidden');
            hinweisEl.innerHTML = '';
        }
    }

    document.getElementById('verkKind-id').addEventListener('kindGewaehlt', function (e) {
        normalKurs   = e.detail.verkauf_kurs   ?? kursDefault;
        billigStueck = e.detail.billig_stueck  ?? 0;
        billigKurs   = e.detail.billig_kurs    ?? 0;
        updateAuszahlung();
    });

    document.getElementById('verkKind-id').addEventListener('kindGeleert', function () {
        normalKurs   = null;
        billigStueck = 0;
        billigKurs   = 0;
        updateAuszahlung();
    });

    stueckIn?.addEventListener('input', updateAuszahlung);
})();
</script>
@endpush
@endsection

