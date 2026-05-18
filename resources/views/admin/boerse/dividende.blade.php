@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-6">
    <h2 class="text-2xl font-extrabold">🎉 Dividende für alle Betriebe ausschütten</h2>
    <p class="text-slate-600">
        Für jeden Betrieb wird der <strong>heutige Tagesgewinn</strong> als Grundlage genutzt.
        Der eingestellte Prozentsatz bestimmt, wie viel vom Tagesgewinn als Dividende ausgezahlt wird.
        Betriebe ohne Tagesgewinn oder ohne Anteilsinhaber werden automatisch übersprungen.
    </p>

    <form method="POST" action="/admin/boerse/dividende" class="space-y-4">
        @csrf
        <div class="flex items-end gap-4">
            <div>
                <label class="label font-semibold">Dividendenanteil am Tagesgewinn</label>
                <div class="flex items-center gap-2 mt-1">
                    <input type="number" name="prozent" id="prozentInput"
                           min="1" max="100" required
                           value="{{ old('prozent', $prozent) }}"
                           class="field w-28 text-right text-lg font-bold">
                    <span class="text-lg font-bold">%</span>
                </div>
                @error('prozent')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="button" id="recalcBtn" class="btn">
                🔄 Vorschau aktualisieren
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-left">
                        <th class="p-2 border">Betrieb</th>
                        <th class="p-2 border text-right">Tagesgewinn</th>
                        <th class="p-2 border text-right">Dividende gesamt</th>
                        <th class="p-2 border text-right">Anteile verkauft</th>
                        <th class="p-2 border text-right">Radi / Anteil</th>
                        <th class="p-2 border text-right">Kontostand</th>
                        <th class="p-2 border text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($betriebe as $row)
                    @php $b = $row['betrieb']; @endphp
                    <tr class="vorschau-row {{ $row['radiProAnteil'] < 1 ? 'opacity-50' : '' }}"
                        data-tagesgewinn="{{ $row['tagesgewinn'] }}"
                        data-anteile="{{ $row['anteileVerkauft'] }}"
                        data-balance="{{ $b->balance }}">
                        <td class="p-2 border font-medium">{{ $b->name }}</td>
                        <td class="p-2 border text-right">{{ $row['tagesgewinn'] }} Radi</td>
                        <td class="p-2 border text-right js-div-gesamt">{{ $row['dividendeGesamt'] }} Radi</td>
                        <td class="p-2 border text-right">{{ $row['anteileVerkauft'] }}</td>
                        <td class="p-2 border text-right font-bold js-radi-anteil">{{ $row['radiProAnteil'] }}</td>
                        <td class="p-2 border text-right {{ !$row['zahlfaehig'] ? 'text-orange-600 font-semibold' : '' }}">
                            {{ $b->balance }} Radi
                        </td>
                        <td class="p-2 border text-center js-status">
                            @if($row['tagesgewinn'] <= 0)
                                <span class="text-slate-400">kein Gewinn</span>
                            @elseif($row['anteileVerkauft'] <= 0)
                                <span class="text-slate-400">keine Anteile</span>
                            @elseif($row['radiProAnteil'] < 1)
                                <span class="text-orange-500">&lt; 1 Radi</span>
                            @elseif(!$row['zahlfaehig'])
                                <span class="text-orange-600">⚠️ wird gekürzt</span>
                            @else
                                <span class="text-green-600 font-semibold">✅ wird ausgezahlt</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary text-lg px-6"
                    onclick="return confirm('Dividende für alle Betriebe mit Tagesgewinn jetzt ausschütten?')">
                🎉 Jetzt für alle ausschütten
            </button>
            <a href="/admin/boerse" class="btn">Abbrechen</a>
        </div>
    </form>
</div>

<script>
document.getElementById('recalcBtn').addEventListener('click', recalc);
document.getElementById('prozentInput').addEventListener('input', recalc);

function recalc() {
    const prozent = parseInt(document.getElementById('prozentInput').value) || 0;
    document.querySelectorAll('.vorschau-row').forEach(function(row) {
        const tagesgewinn   = parseInt(row.dataset.tagesgewinn) || 0;
        const anteile       = parseInt(row.dataset.anteile) || 0;
        const balance       = parseInt(row.dataset.balance) || 0;
        const divGesamt     = Math.floor(tagesgewinn * prozent / 100);
        const radiProAnteil = anteile > 0 ? Math.floor(divGesamt / anteile) : 0;
        const gesamtSumme   = anteile * radiProAnteil;
        const zahlfaehig    = balance >= gesamtSumme;

        row.querySelector('.js-div-gesamt').textContent  = divGesamt + ' Radi';
        row.querySelector('.js-radi-anteil').textContent = radiProAnteil;

        const status = row.querySelector('.js-status');
        if (tagesgewinn <= 0) {
            status.innerHTML = '<span class="text-slate-400">kein Gewinn</span>';
            row.classList.add('opacity-50');
        } else if (anteile <= 0) {
            status.innerHTML = '<span class="text-slate-400">keine Anteile</span>';
            row.classList.add('opacity-50');
        } else if (radiProAnteil < 1) {
            status.innerHTML = '<span class="text-orange-500">&lt; 1 Radi</span>';
            row.classList.add('opacity-50');
        } else if (!zahlfaehig) {
            status.innerHTML = '<span class="text-orange-600">⚠️ wird gekürzt</span>';
            row.classList.remove('opacity-50');
        } else {
            status.innerHTML = '<span class="text-green-600 font-semibold">✅ wird ausgezahlt</span>';
            row.classList.remove('opacity-50');
        }
    });
}
</script>
@endsection

