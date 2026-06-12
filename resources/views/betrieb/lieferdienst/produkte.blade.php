@extends('betrieb.layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Kopfzeile --}}
    <div class="card p-5 flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-2xl font-extrabold flex items-center gap-2">
            <i class="fa-solid fa-motorcycle text-emerald-600"></i>
            Lieferprodukte verwalten
        </h1>
        <div class="flex gap-3">
            <a href="{{ route('betrieb.lieferbestellungen') }}" class="btn btn-primary">
                <i class="fa-solid fa-list-check mr-1"></i> Bestellungen anzeigen
            </a>
        </div>
    </div>

    {{-- Lieferkosten --}}
    <div class="card p-5">
        <h2 class="text-xl font-extrabold mb-3 flex items-center gap-2 text-slate-700">
            <i class="fa-solid fa-coins text-amber-500"></i> Lieferkosten
        </h2>
        <form method="POST" action="{{ route('betrieb.lieferkosten.store') }}" class="flex items-end gap-3 flex-wrap">
            @csrf
            <div>
                <label class="label text-sm">Lieferkosten pro Bestellung (Radi)</label>
                <input type="number" name="lieferkosten" min="0" max="9999"
                       value="{{ $betrieb->lieferkosten ?? 0 }}"
                       class="field w-32" required>
            </div>
            <button type="submit" class="btn btn-success">Speichern</button>
        </form>
        <p class="text-xs text-slate-500 mt-2">0 Radi = kostenlose Lieferung</p>
    </div>

    {{-- Produkte auswählen --}}
    <form method="POST" action="{{ route('betrieb.lieferprodukte.store') }}">
        @csrf
        <div class="card p-5">
            <h2 class="text-xl font-extrabold mb-4 flex items-center gap-2 text-slate-700">
                <i class="fa-solid fa-boxes-stacked text-emerald-600"></i>
                Welche Produkte liefert ihr?
            </h2>
            <p class="text-slate-500 mb-5">Wähle die Produkte aus, die ihr von anderen Betrieben liefern könnt. Diese erscheinen dann auf eurer öffentlichen Bestell-Seite.</p>

            @if($alleBetriebe->isEmpty())
                <div class="text-center text-slate-400 py-8">
                    <i class="fa-solid fa-box-open text-4xl mb-2"></i>
                    <p>Keine anderen Betriebe mit aktiven Produkten gefunden.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($alleBetriebe as $shop)
                    <div class="border-2 border-slate-200 rounded-2xl p-4">
                        <div class="font-extrabold text-lg mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-store text-slate-500"></i>
                            {{ $shop->name }}
                            <button type="button"
                                    onclick="alleAuswaehlen({{ $shop->id }})"
                                    class="ml-auto text-sm font-semibold text-emerald-600 hover:underline">
                                Alle auswählen
                            </button>
                            <button type="button"
                                    onclick="alleAbwaehlen({{ $shop->id }})"
                                    class="text-sm font-semibold text-rose-500 hover:underline">
                                Alle abwählen
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" id="betrieb-{{ $shop->id }}">
                            @foreach($shop->products as $produkt)
                            <label class="cursor-pointer flex items-start gap-2 rounded-2xl border-2 p-3
                                          {{ in_array($produkt->id, $ausgewaehlt) ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-emerald-300' }}
                                          transition product-card" data-betrieb="{{ $shop->id }}">
                                <input type="checkbox" name="produkte[]" value="{{ $produkt->id }}"
                                       {{ in_array($produkt->id, $ausgewaehlt) ? 'checked' : '' }}
                                       class="mt-1 accent-emerald-600 product-checkbox"
                                       onchange="karteAktualisieren(this)">
                                <span>
                                    <span class="font-bold block">{{ $produkt->name }}</span>
                                    <span class="text-emerald-700 font-extrabold">{{ $produkt->price }} Radi</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-success w-full text-xl py-4 mt-6">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Auswahl speichern
                </button>
            @endif
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
function karteAktualisieren(checkbox) {
    const label = checkbox.closest('label');
    if (checkbox.checked) {
        label.classList.add('border-emerald-400', 'bg-emerald-50');
        label.classList.remove('border-slate-200');
    } else {
        label.classList.remove('border-emerald-400', 'bg-emerald-50');
        label.classList.add('border-slate-200');
    }
}

function alleAuswaehlen(betriebId) {
    document.querySelectorAll(`#betrieb-${betriebId} .product-checkbox`).forEach(cb => {
        cb.checked = true;
        karteAktualisieren(cb);
    });
}

function alleAbwaehlen(betriebId) {
    document.querySelectorAll(`#betrieb-${betriebId} .product-checkbox`).forEach(cb => {
        cb.checked = false;
        karteAktualisieren(cb);
    });
}
</script>
@endpush

