{{--
    Wiederverwendbares Kind-Suchfeld mit Live-Vorschlägen.
    Parameter (per `@include('boerse.partials.kind_suche', [...])`):
      $endpoint  – URL die JSON [{id,name,(stueck)}] zurückgibt
      $accent    – Tailwind-Farbname (z. B. 'emerald', 'sky', 'violet')
      $idName    – Field-Name des hidden inputs (Default: 'customer_id')
      $idPrefix  – HTML-IDs Präfix (damit mehrere Felder unique sind)
      $zeigeStueck – bool: Bestand-Anzeige in der Liste (Verkauf/Rückkauf)
      $stueckJs  – JS-Funktion oder leer: optionale Callback bei Auswahl, bekommt das Item
      $maxAttr   – falls true: setzt das max-Attribut eines Stück-Inputs anhand des Bestands

    Erwartet im selben Form:
      <input type="number" name="stueck" id="{$idPrefix}-stueck" ...>
--}}
@php
    $endpoint     = $endpoint     ?? '#';
    $accent       = $accent       ?? 'amber';
    $idName       = $idName       ?? 'customer_id';
    $idPrefix     = $idPrefix     ?? 'kindSuche';
    $zeigeStueck  = $zeigeStueck  ?? false;
    $maxAttr      = $maxAttr      ?? false;
    $platzhalter  = $platzhalter  ?? 'Namen tippen…';
@endphp
<div class="relative" data-kindsuche="{{ $idPrefix }}">
    <label class="block font-semibold mb-1">Welches Kind?</label>
    <input type="text"
           id="{{ $idPrefix }}-input"
           autocomplete="off"
           placeholder="{{ $platzhalter }}"
           class="w-full text-lg border-2 border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:border-{{ $accent }}-500">
    <input type="hidden" name="{{ $idName }}" id="{{ $idPrefix }}-id" value="{{ old($idName) }}">

    {{-- Vorschlagsliste --}}
    <ul id="{{ $idPrefix }}-list"
        class="hidden absolute z-30 mt-1 w-full max-h-72 overflow-y-auto bg-white border-2 border-{{ $accent }}-300 rounded-xl shadow-lg"></ul>

    {{-- Ausgewähltes Kind --}}
    <div id="{{ $idPrefix }}-chip" class="hidden mt-2 inline-flex items-center gap-2 bg-{{ $accent }}-100 text-{{ $accent }}-900 rounded-xl px-3 py-2 font-semibold border-2 border-{{ $accent }}-300">
        <span id="{{ $idPrefix }}-chip-text"></span>
        <button type="button" id="{{ $idPrefix }}-clear"
                class="text-{{ $accent }}-700 hover:text-{{ $accent }}-900 font-bold text-xl leading-none">×</button>
    </div>
</div>

@push('js')
<script>
(function() {
    const prefix      = @json($idPrefix);
    const endpoint    = @json($endpoint);
    const zeigeStueck = @json($zeigeStueck);
    const maxAttr     = @json($maxAttr);

    const input  = document.getElementById(prefix + '-input');
    const hidden = document.getElementById(prefix + '-id');
    const list   = document.getElementById(prefix + '-list');
    const chip   = document.getElementById(prefix + '-chip');
    const chipTx = document.getElementById(prefix + '-chip-text');
    const clrBtn = document.getElementById(prefix + '-clear');
    const stueckIn = document.getElementById(prefix + '-stueck'); // optional

    let timer = null;
    let aktuelleItems = [];

    function setSelected(item) {
        hidden.value = item.id;
        chipTx.innerText = '✅ ' + item.name + (zeigeStueck && item.stueck != null ? ' (' + item.stueck + ' Anteile)' : '');
        chip.classList.remove('hidden');
        input.value = '';
        input.classList.add('hidden');
        list.classList.add('hidden');
        if (maxAttr && stueckIn && item.stueck != null) {
            stueckIn.max = item.stueck;
            if (parseInt(stueckIn.value || '0') > item.stueck) {
                stueckIn.value = item.stueck;
                stueckIn.dispatchEvent(new Event('input'));
            }
        }
    }

    function clearSelected() {
        hidden.value = '';
        chip.classList.add('hidden');
        input.classList.remove('hidden');
        input.value = '';
        input.focus();
        if (maxAttr && stueckIn) stueckIn.removeAttribute('max');
    }

    function renderList(items) {
        aktuelleItems = items;
        if (items.length === 0) {
            list.innerHTML = '<li class="px-3 py-2 text-slate-500">Keine Treffer.</li>';
            list.classList.remove('hidden');
            return;
        }
        list.innerHTML = items.map((it, i) =>
            '<li data-idx="'+i+'" class="kind-treffer cursor-pointer px-3 py-2 hover:bg-slate-100 border-b last:border-b-0">'
            + '<b>' + escapeHtml(it.name) + '</b>'
            + (zeigeStueck && it.stueck != null ? ' <span class="text-sm text-slate-500">— ' + it.stueck + ' Anteile</span>' : '')
            + '</li>'
        ).join('');
        list.classList.remove('hidden');
        list.querySelectorAll('.kind-treffer').forEach(li => {
            li.addEventListener('click', () => setSelected(aktuelleItems[li.dataset.idx]));
        });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function suchen() {
        const q = input.value.trim();
        fetch(endpoint + (endpoint.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(items => renderList(items))
            .catch(() => { list.innerHTML = '<li class="px-3 py-2 text-rose-600">Fehler bei der Suche</li>'; list.classList.remove('hidden'); });
    }

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(suchen, 200);
    });
    input.addEventListener('focus', () => {
        suchen(); // sofort Vorschläge anbieten
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-kindsuche="' + prefix + '"]')) {
            list.classList.add('hidden');
        }
    });
    clrBtn.addEventListener('click', clearSelected);
})();
</script>
@endpush

