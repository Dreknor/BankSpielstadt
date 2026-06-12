@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost py-2 px-3 text-sm">
                <i class="fa-solid fa-arrow-left"></i> Zurück
            </a>
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-motorcycle text-emerald-500"></i>
                Lieferdienst – {{ $betrieb->name }}
            </h2>
        </div>

        {{-- Aktueller Status --}}
        <div class="mb-6 p-4 rounded-2xl border-2
             {{ $betrieb->is_lieferdienst ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
            <div class="font-bold text-lg mb-1">
                @if($betrieb->is_lieferdienst)
                    <i class="fa-solid fa-circle-check text-emerald-500 mr-2"></i>
                    Dieser Betrieb ist als <strong>Lieferdienst</strong> markiert.
                @else
                    <i class="fa-solid fa-circle-xmark text-slate-400 mr-2"></i>
                    Dieser Betrieb ist <strong>nicht</strong> als Lieferdienst markiert.
                @endif
            </div>

            @if($betrieb->is_lieferdienst)
                <p class="text-sm text-emerald-800 mt-2">
                    Öffentliche Bestell-Seite:
                    <a href="{{ route('lieferdienst.show', $betrieb) }}" target="_blank"
                       class="underline font-semibold">
                        {{ route('lieferdienst.show', $betrieb) }}
                    </a>
                </p>
                <p class="text-sm text-emerald-800 mt-1">
                    Lieferkosten: <strong>{{ $betrieb->lieferkosten ?? 0 }} Radi</strong>
                </p>
            @else
                @if(! $betrieb->betrieb_pin)
                    <p class="text-sm text-rose-700 mt-2">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i>
                        Dieser Betrieb hat noch keinen Betriebs-PIN. Bitte zuerst einen PIN vergeben.
                    </p>
                @endif
            @endif
        </div>

        {{-- Aktionsbuttons --}}
        <div class="flex flex-wrap gap-3">
            @if($betrieb->is_lieferdienst)
                <form method="POST" action="{{ route('admin.betriebe.lieferdienst.store', $betrieb) }}"
                      onsubmit="return confirm('Lieferdienst-Markierung wirklich entfernen?')">
                    @csrf
                    <input type="hidden" name="aktion" value="deaktivieren">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-toggle-off mr-1"></i> Lieferdienst deaktivieren
                    </button>
                </form>
            @elseif($betrieb->betrieb_pin)
                <form method="POST" action="{{ route('admin.betriebe.lieferdienst.store', $betrieb) }}">
                    @csrf
                    <input type="hidden" name="aktion" value="aktivieren">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-toggle-on mr-1"></i> Als Lieferdienst aktivieren
                    </button>
                </form>
            @else
                <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost">
                    <i class="fa-solid fa-key mr-1"></i> Zuerst PIN setzen
                </a>
            @endif
        </div>

        {{-- Lieferkosten setzen (nur wenn aktiv) --}}
        @if($betrieb->is_lieferdienst)
        <div class="mt-6 border-t pt-5">
            <h3 class="font-bold text-lg mb-3">Lieferkosten festlegen</h3>
            <form method="POST" action="{{ route('admin.betriebe.lieferdienst.store', $betrieb) }}"
                  class="flex items-end gap-3 flex-wrap">
                @csrf
                <input type="hidden" name="aktion" value="lieferkosten">
                <div>
                    <label class="block text-sm font-semibold mb-1">Lieferkosten (Radi)</label>
                    <input type="number" name="lieferkosten" min="0" max="9999"
                           value="{{ $betrieb->lieferkosten ?? 0 }}"
                           class="border rounded-lg px-3 py-2 w-32">
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
            <p class="text-xs text-slate-500 mt-2">0 Radi = kostenlose Lieferung</p>
        </div>
        @endif
    </div>

    {{-- Info-Box --}}
    <div class="card p-6 bg-sky-50 border-2 border-sky-200 space-y-2">
        <h3 class="font-extrabold text-sky-800 flex items-center gap-2">
            <i class="fa-solid fa-circle-info"></i>
            Wie funktioniert der Lieferdienst?
        </h3>
        <ul class="text-sm text-sky-800 space-y-1 list-disc list-inside">
            <li>Der Betrieb wählt in seiner Kasse aus, welche Produkte anderer Betriebe er liefert.</li>
            <li>Auf der öffentlichen Bestell-Seite können Kunden Produkte bestellen und Lieferort angeben.</li>
            <li>Neue Bestellungen erscheinen im Kassenbereich des Lieferdienstes.</li>
            <li>Mitarbeiter können einer Bestellung zugewiesen und der Status verfolgt werden.</li>
            <li>Lieferkosten werden automatisch zum Bestellbetrag addiert.</li>
        </ul>
    </div>
</div>
@endsection

