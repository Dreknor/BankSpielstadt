<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $lieferdienst->name }} – Bestellen</title>
    @vite(['resources/css/app.css'])
    <script src="https://kit.fontawesome.com/c8f58e3eb6.js" crossorigin="anonymous" defer></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">

{{-- Navbar --}}
<nav class="bg-emerald-600 text-white shadow-md">
    <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
        <i class="fa-solid fa-motorcycle text-3xl"></i>
        <div>
            <div class="text-2xl font-extrabold">{{ $lieferdienst->name }}</div>
            <div class="text-emerald-200 text-sm">Lieferdienst · Bestell-Seite</div>
        </div>
    </div>
</nav>

<main class="max-w-3xl mx-auto px-4 py-8 space-y-6">

    {{-- Meldungen --}}
    @if(session('Meldung'))
        @php
            $t = session('type', 'success');
            $cls = ['success'=>'bg-emerald-50 border-emerald-300 text-emerald-800','error'=>'bg-rose-50 border-rose-300 text-rose-800'][$t] ?? 'bg-slate-50 border-slate-300';
        @endphp
        <div class="rounded-2xl border-2 p-5 text-lg font-semibold {{ $cls }}">
            {{ session('Meldung') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-800 p-5">
            <div class="font-bold mb-2"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Bitte diese Fehler beheben:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if($angebote->isEmpty())
        <div class="card p-10 text-center text-slate-400 rounded-3xl bg-white shadow">
            <i class="fa-solid fa-box-open text-5xl mb-3"></i>
            <p class="text-xl font-semibold">Noch keine Produkte verfügbar.</p>
            <p class="mt-2 text-sm">Schau später nochmal rein!</p>
        </div>
    @else

    <form method="POST" action="{{ route('lieferdienst.store', $lieferdienst) }}" id="bestellForm">
        @csrf

        {{-- Persönliche Angaben --}}
        <div class="bg-white rounded-3xl shadow p-6 space-y-4 mb-6">
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-user text-emerald-600"></i> Wer bist du?
            </h2>

            <div>
                <label class="block text-lg font-bold mb-2">Dein Name <span class="text-rose-500">*</span></label>
                <input type="text" name="besteller_name" value="{{ old('besteller_name') }}"
                       placeholder="z. B. Max Mustermann"
                       class="w-full rounded-2xl border-2 border-slate-300 focus:border-emerald-400 focus:outline-none p-4 text-lg"
                       required minlength="2" maxlength="100">
            </div>

            <div>
                <label class="block text-lg font-bold mb-2">Wo soll geliefert werden? <span class="text-rose-500">*</span></label>
                <input type="text" name="lieferort" value="{{ old('lieferort') }}"
                       placeholder="z. B. Stand 12, Marktplatz"
                       class="w-full rounded-2xl border-2 border-slate-300 focus:border-emerald-400 focus:outline-none p-4 text-lg"
                       required minlength="2" maxlength="200">
            </div>
        </div>

        {{-- Produkte nach Betrieb gruppiert --}}
        <div class="space-y-5">
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-shopping-cart text-emerald-600"></i> Was möchtest du bestellen?
            </h2>

            @foreach($angebote as $shopName => $items)
            <div class="bg-white rounded-3xl shadow p-5">
                <h3 class="text-lg font-extrabold text-slate-600 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-store text-slate-400"></i> {{ $shopName }}
                </h3>
                <div class="space-y-3">
                    @foreach($items as $angebot)
                    @php $produkt = $angebot->product; @endphp
                    <div class="flex items-center justify-between gap-3 rounded-2xl border-2 border-slate-100 p-3 hover:border-emerald-200 transition">
                        <div class="flex-1">
                            <div class="font-bold text-base">{{ $produkt->name }}</div>
                            <div class="text-emerald-600 font-extrabold">{{ $produkt->price }} Radi</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    onclick="mengeAendern({{ $produkt->id }}, -1)"
                                    class="w-10 h-10 rounded-full bg-rose-100 hover:bg-rose-200 text-rose-600 font-extrabold text-xl flex items-center justify-center transition active:scale-90">
                                −
                            </button>
                            <span id="menge-{{ $produkt->id }}"
                                  data-price="{{ $produkt->price }}"
                                  data-name="{{ addslashes($produkt->name) }}"
                                  class="w-10 text-center font-extrabold text-xl">
                                0
                            </span>
                            <button type="button"
                                    onclick="mengeAendern({{ $produkt->id }}, 1)"
                                    class="w-10 h-10 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-600 font-extrabold text-xl flex items-center justify-center transition active:scale-90">
                                +
                            </button>
                            <input type="hidden"
                                   name="positionen[{{ $produkt->id }}][product_id]"
                                   value="{{ $produkt->id }}">
                            <input type="hidden"
                                   id="input-{{ $produkt->id }}"
                                   name="positionen[{{ $produkt->id }}][menge]"
                                   value="0">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- Zusammenfassung & Abschicken --}}
        <div class="bg-white rounded-3xl shadow p-6 mt-6 space-y-4" id="zusammenfassung">
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-receipt text-emerald-600"></i> Deine Bestellung
            </h2>

            <div id="summaryLeer" class="text-slate-400 text-center py-4">
                <i class="fa-solid fa-shopping-cart text-3xl mb-2"></i>
                <p>Noch nichts ausgewählt – füge oben Produkte hinzu!</p>
            </div>

            <div id="summaryInhalt" class="hidden space-y-3">
                <ul id="summaryList" class="divide-y divide-slate-100"></ul>
                @if($lieferdienst->lieferkosten)
                <div class="flex justify-between text-base text-slate-600 pt-2">
                    <span>Lieferkosten</span>
                    <span class="font-bold">{{ $lieferdienst->lieferkosten }} Radi</span>
                </div>
                @endif
                <div class="flex justify-between text-2xl font-extrabold border-t-2 pt-3">
                    <span>Gesamt:</span>
                    <span id="gesamtSumme" class="text-emerald-700">0 Radi</span>
                </div>

                <button type="submit" id="bestellBtn"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-2xl py-5 rounded-2xl transition-all shadow-lg">
                    <i class="fa-solid fa-motorcycle mr-2"></i> Jetzt bestellen!
                </button>
            </div>
        </div>
    </form>
    @endif
</main>

<footer class="text-center text-slate-400 text-sm py-6 mt-8">
    {{ $lieferdienst->name }} · Lieferdienst der Kinderspielstadt
</footer>

<script>
const mengen = {};
const lieferkosten = {{ $lieferdienst->lieferkosten ?? 0 }};

function mengeAendern(id, delta) {
    mengen[id] = Math.max(0, (mengen[id] || 0) + delta);
    document.getElementById('menge-' + id).textContent = mengen[id];
    document.getElementById('input-' + id).value = mengen[id];
    aktualisiereZusammenfassung();
}

function aktualisiereZusammenfassung() {
    const list = document.getElementById('summaryList');
    const leer = document.getElementById('summaryLeer');
    const inhalt = document.getElementById('summaryInhalt');
    const gesamtEl = document.getElementById('gesamtSumme');

    list.innerHTML = '';
    let gesamt = 0;
    let irgendwas = false;

    // Alle Produktkarten durchgehen
    document.querySelectorAll('[id^="menge-"]').forEach(el => {
        const id = el.id.replace('menge-', '');
        const menge = mengen[id] || 0;
        if (menge > 0) {
            irgendwas = true;
            const preis = parseInt(el.dataset.price);
            const name  = el.dataset.name;
            const sub   = preis * menge;
            gesamt += sub;
            list.innerHTML += `<li class="flex justify-between py-1 text-base">
                <span>${menge}× ${name}</span>
                <span class="font-bold">${sub} Radi</span>
            </li>`;
        }
    });

    gesamt += lieferkosten;

    if (irgendwas) {
        leer.classList.add('hidden');
        inhalt.classList.remove('hidden');
        gesamtEl.textContent = gesamt + ' Radi';
    } else {
        leer.classList.remove('hidden');
        inhalt.classList.add('hidden');
    }
}
</script>
</body>
</html>



