@extends('boerse.layouts.app')
@section('content')

{{-- Konfigurationswerte für JS --}}
<script>
    const BOERSE_CONFIG = {
        normalAngest:  {{ config('bank.aktien.angestellte_normal', 4) }},
        maxSprungPct:  {{ config('bank.aktien.max_sprung_prozent', 15) }},
        minKurs:       {{ config('bank.aktien.min_kurs', 1) }},
    };
</script>

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
            </div>

            <form method="POST" action="/boerse/erfassung/{{ $b->id }}" class="mt-3 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-semibold">Angestellte:</label>
                    <input type="number" name="angestellte" min="0" max="50" required
                           id="angestellte-{{ $b->id }}"
                           oninput="aktualisiereVorschau({{ $b->id }}, {{ $b->aktien_kurs ?? $b->aktien_startkurs ?? 10 }})"
                           class="w-24 text-2xl text-center border-2 border-slate-300 rounded-xl px-2 py-1">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold">Eindruck (kurz):</label>
                    <input type="text" name="notiz" maxlength="100" placeholder="z. B. voll, ruhig, leer"
                           class="w-full border-2 border-slate-300 rounded-xl px-3 py-1">
                </div>
                <button class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-5 py-2 rounded-xl">✅ Speichern</button>
            </form>

            {{-- Inline-Vorschau (erscheint nach Eingabe) --}}
            <div id="vorschau-{{ $b->id }}"
                 class="hidden mt-3 grid grid-cols-3 gap-3 rounded-2xl border-2 border-sky-200 bg-sky-50 p-4">
                <div class="text-center">
                    <div class="text-xs text-slate-500 font-semibold">Aktueller Kurs</div>
                    <div class="text-3xl font-extrabold text-slate-700">{{ $b->aktien_kurs ?? $b->aktien_startkurs ?? 10 }} Radi</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-slate-500 font-semibold">Einfluss</div>
                    <div id="vorschau-delta-{{ $b->id }}" class="text-3xl font-extrabold">—</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-slate-500 font-semibold">🔮 Neuer Kurs</div>
                    <div id="vorschau-kurs-{{ $b->id }}" class="text-3xl font-extrabold">—</div>
                    <div id="vorschau-text-{{ $b->id }}" class="text-sm mt-1"></div>
                </div>
            </div>
        </div>
    @endforeach
@endif

<script>
function aktualisiereVorschau(id, alterKurs) {
    const input = document.getElementById('angestellte-' + id);
    const panel = document.getElementById('vorschau-' + id);

    if (!input || input.value === '') {
        panel.classList.add('hidden');
        return;
    }

    const angestellte = parseInt(input.value);
    if (isNaN(angestellte)) { panel.classList.add('hidden'); return; }

    const { normalAngest, maxSprungPct, minKurs } = BOERSE_CONFIG;

    // Gleiche Formel wie im Controller
    const delta     = Math.max(-2, Math.min(2, angestellte - normalAngest));
    const maxSprung = Math.max(1, Math.floor(alterKurs * maxSprungPct / 100));
    const rohKurs   = alterKurs + delta;
    const neuerKurs = Math.max(minKurs, Math.max(alterKurs - maxSprung, Math.min(alterKurs + maxSprung, rohKurs)));

    // Anzeige Einfluss
    const deltaEl = document.getElementById('vorschau-delta-' + id);
    deltaEl.textContent = (delta >= 0 ? '+' : '') + delta;
    deltaEl.className   = 'text-3xl font-extrabold ' + (delta > 0 ? 'text-emerald-600' : delta < 0 ? 'text-rose-600' : 'text-slate-500');

    // Anzeige neuer Kurs
    const kursEl = document.getElementById('vorschau-kurs-' + id);
    kursEl.textContent = neuerKurs + ' Radi';
    kursEl.className   = 'text-3xl font-extrabold ' + (neuerKurs > alterKurs ? 'text-emerald-600' : neuerKurs < alterKurs ? 'text-rose-600' : 'text-slate-500');

    // Trendtext
    const textEl = document.getElementById('vorschau-text-' + id);
    if (neuerKurs > alterKurs)      textEl.textContent = '📈 würde steigen';
    else if (neuerKurs < alterKurs) textEl.textContent = '📉 würde fallen';
    else                            textEl.textContent = '➡️ unverändert';

    panel.classList.remove('hidden');
}
</script>
@endsection

