@extends('betrieb.layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Linke Spalte: Produktgitter --}}
    <div class="card p-5">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-box text-emerald-600"></i> Produkte
        </h2>
        @if($produkte->isEmpty())
            <div class="text-slate-500 text-center py-8">
                <i class="fa-solid fa-box-open text-4xl mb-2"></i>
                <p>Noch keine Produkte angelegt.</p>
                <a href="/betrieb/produkte/erstellen" class="btn btn-success mt-4">Produkt anlegen</a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="produktGitter">
                @foreach($produkte as $produkt)
                    <button type="button"
                            class="rounded-2xl bg-emerald-50 hover:bg-emerald-100 border-2 border-emerald-200 p-4 text-center transition active:scale-95 font-bold"
                            onclick="addToCart({{ $produkt->id }}, '{{ addslashes($produkt->name) }}', {{ $produkt->price }})">
                        <div class="text-lg">{{ $produkt->name }}</div>
                        <div class="text-emerald-700 font-extrabold text-xl">{{ $produkt->price }} Radi</div>
                        <div class="mt-2 text-2xl">➕</div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Rechte Spalte: Warenkorb --}}
    <div class="card p-5 flex flex-col">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-cart-shopping text-emerald-600"></i> Warenkorb
        </h2>

        <div id="cartLeer" class="text-slate-400 text-center py-8 flex-1 flex flex-col items-center justify-center">
            <i class="fa-solid fa-cart-shopping text-4xl mb-2"></i>
            <p>Noch nichts im Warenkorb</p>
        </div>

        <div id="cartInhalt" class="hidden flex-1 flex flex-col">
            <ul id="cartList" class="divide-y divide-slate-200 flex-1 overflow-y-auto max-h-80"></ul>

            <div class="border-t-2 border-slate-200 mt-3 pt-3">
                <div class="flex justify-between text-2xl font-extrabold">
                    <span>Gesamt:</span>
                    <span id="cartTotal">0 Radi</span>
                </div>
            </div>

            {{-- Verkauf abschließen --}}
            <form id="verkaufForm" method="POST" action="{{ route('betrieb.kasse.verkauf') }}" class="mt-4 space-y-3">
                @csrf
                <div id="cartInputs"></div>
                <button type="submit" class="btn btn-success w-full text-xl py-4">
                    <i class="fa-solid fa-money-bill-wave"></i> Bezahlt – Kasse buchen
                </button>
                <button type="button" onclick="clearCart()" class="btn btn-ghost w-full">
                    <i class="fa-solid fa-trash"></i> Warenkorb leeren
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Einlage / Entnahme --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
    <div class="card p-5">
        <h3 class="text-xl font-extrabold mb-3 flex items-center gap-2 text-emerald-700">
            <i class="fa-solid fa-arrow-down-to-bracket"></i> Bareinlage
        </h3>
        <form method="POST" action="{{ route('betrieb.kasse.einlage') }}" class="space-y-3">
            @csrf
            <div>
                <label class="label text-sm">Betrag (Radi)</label>
                <input type="number" name="amount" min="1" class="field" required>
            </div>
            <div>
                <label class="label text-sm">Kommentar (optional)</label>
                <input type="text" name="comment" class="field" placeholder="z.B. Tagesstartkapital">
            </div>
            <button type="submit" class="btn btn-success w-full">Einlage buchen</button>
        </form>
    </div>

    <div class="card p-5">
        <h3 class="text-xl font-extrabold mb-3 flex items-center gap-2 text-rose-700">
            <i class="fa-solid fa-arrow-up-from-bracket"></i> Geldentnahme
        </h3>
        <form method="POST" action="{{ route('betrieb.kasse.entnahme') }}" class="space-y-3">
            @csrf
            <div>
                <label class="label text-sm">Betrag (Radi)</label>
                <input type="number" name="amount" min="1" max="{{ $kassenbestand }}" class="field" required>
            </div>
            <div>
                <label class="label text-sm">Kommentar <span class="text-rose-500">*</span></label>
                <input type="text" name="comment" class="field" placeholder="z.B. Abgabe an Bank" required>
            </div>
            <button type="submit" class="btn btn-danger w-full">Entnahme buchen</button>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    // Gespeicherten Warenkorb aus der Datenbank laden
    let cart = @json($gespeicherterWarenkorb ?: new stdClass());
    // JSON-Objekt → cart-Format sicherstellen
    if (Array.isArray(cart)) cart = {};

    const WARENKORB_URL = '{{ route('betrieb.kasse.warenkorb.save') }}';
    const CSRF_TOKEN    = '{{ csrf_token() }}';

    // Debounce: speichert frühestens 400 ms nach der letzten Änderung
    let saveTimer = null;
    function scheduleSync() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(syncWarenkorb, 400);
    }

    function syncWarenkorb() {
        fetch(WARENKORB_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ inhalt: cart }),
        }).catch(() => { /* Verbindungsfehler still ignorieren */ });
    }

    function addToCart(id, name, price) {
        if (cart[id]) {
            cart[id].menge++;
        } else {
            cart[id] = { id, name, price, menge: 1 };
        }
        renderCart();
        scheduleSync();
    }

    function removeFromCart(id) {
        if (cart[id]) {
            cart[id].menge--;
            if (cart[id].menge <= 0) delete cart[id];
        }
        renderCart();
        scheduleSync();
    }

    function clearCart() {
        cart = {};
        renderCart();
        scheduleSync();
    }

    function renderCart() {
        const items = Object.values(cart);
        const list = document.getElementById('cartList');
        const inputs = document.getElementById('cartInputs');
        const total = document.getElementById('cartTotal');
        const leer = document.getElementById('cartLeer');
        const inhalt = document.getElementById('cartInhalt');

        list.innerHTML = '';
        inputs.innerHTML = '';
        let sum = 0;

        items.forEach((item, idx) => {
            const sub = item.price * item.menge;
            sum += sub;
            list.innerHTML += `
                <li class="py-2 flex justify-between items-center gap-2">
                    <span class="font-semibold flex-1">${item.name}</span>
                    <span class="text-slate-500">× ${item.menge}</span>
                    <span class="font-extrabold w-20 text-right">${sub} Radi</span>
                    <button type="button" onclick="removeFromCart(${item.id})"
                        class="text-rose-500 hover:text-rose-700 px-2 py-1 rounded-lg hover:bg-rose-50">
                        <i class="fa-solid fa-minus"></i>
                    </button>
                </li>`;
            inputs.innerHTML += `
                <input type="hidden" name="produkte[${idx}][id]" value="${item.id}">
                <input type="hidden" name="produkte[${idx}][menge]" value="${item.menge}">`;
        });

        total.textContent = sum + ' Radi';

        if (items.length === 0) {
            leer.classList.remove('hidden');
            inhalt.classList.add('hidden');
        } else {
            leer.classList.add('hidden');
            inhalt.classList.remove('hidden');
        }
    }

    // Direkt beim Laden rendern (gespeicherter Warenkorb)
    renderCart();
</script>
@endpush


