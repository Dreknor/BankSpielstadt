@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost py-2 px-3 text-sm">
                <i class="fa-solid fa-arrow-left"></i> Zurück
            </a>
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-bell text-rose-500"></i>
                Support-Betrieb – {{ $betrieb->name }}
            </h2>
        </div>

        {{-- Aktueller Status --}}
        <div class="mb-6 p-4 rounded-2xl border-2
             {{ $betrieb->is_support ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-slate-50' }}">
            <div class="font-bold text-lg mb-1">
                @if($betrieb->is_support)
                    <i class="fa-solid fa-circle-check text-rose-500 mr-2"></i>
                    Dieser Betrieb ist als <strong>Support-Betrieb</strong> markiert.
                @else
                    <i class="fa-solid fa-circle-xmark text-slate-400 mr-2"></i>
                    Dieser Betrieb ist <strong>nicht</strong> als Support-Betrieb markiert.
                @endif
            </div>

            @if($betrieb->is_support)
                <p class="text-sm text-rose-800 mt-2">
                    Mitarbeiter dieses Betriebs sehen in ihrer Kasse alle Hilferufe anderer Betriebe
                    und können diese bearbeiten. Andere Betriebe können über ihren Startbildschirm
                    einen <strong>Hilferuf</strong> absenden.
                </p>
            @else
                @if($supportBetrieb && $supportBetrieb->id !== $betrieb->id)
                    <p class="text-sm text-amber-700 mt-2">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        Aktuell ist <strong>{{ $supportBetrieb->name }}</strong> als Support-Betrieb markiert.
                        Das Aktivieren hier entfernt diese Markierung automatisch.
                    </p>
                @endif

                @if(! $betrieb->betrieb_pin)
                    <p class="text-sm text-rose-700 mt-2">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i>
                        Dieser Betrieb hat noch keinen Betriebs-PIN.
                        Bitte zuerst einen PIN vergeben.
                    </p>
                @endif
            @endif
        </div>

        {{-- Aktionsbuttons --}}
        <div class="flex flex-wrap gap-3">
            @if($betrieb->is_support)
                <form method="POST" action="{{ route('admin.betriebe.support.store', $betrieb) }}"
                      onsubmit="return confirm('Support-Markierung wirklich entfernen?')">
                    @csrf
                    <input type="hidden" name="aktion" value="deaktivieren">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-toggle-off mr-1"></i> Support deaktivieren
                    </button>
                </form>
            @elseif($betrieb->betrieb_pin)
                <form method="POST" action="{{ route('admin.betriebe.support.store', $betrieb) }}">
                    @csrf
                    <input type="hidden" name="aktion" value="aktivieren">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-toggle-on mr-1"></i> Als Support-Betrieb aktivieren
                    </button>
                </form>
            @else
                <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost">
                    <i class="fa-solid fa-key mr-1"></i> Zuerst PIN setzen
                </a>
            @endif
        </div>
    </div>

    {{-- Info-Box --}}
    <div class="card p-6 bg-sky-50 border-2 border-sky-200 space-y-2">
        <h3 class="font-extrabold text-sky-800 flex items-center gap-2">
            <i class="fa-solid fa-circle-info"></i>
            Wie funktioniert der Support-Betrieb?
        </h3>
        <ul class="text-sm text-sky-800 space-y-1 list-disc list-inside">
            <li>Genau <strong>ein</strong> Betrieb kann als Support-Betrieb markiert sein.</li>
            <li>Alle anderen Betriebe sehen auf ihrer Startseite einen großen <strong>„Hilfe rufen"-Button</strong>.</li>
            <li>Der Support-Betrieb sieht alle offenen Hilferufe mit Betriebsname, Zeit und optionaler Nachricht.</li>
            <li>Status-Optionen: 🔴 Neu · 🟡 Wird bearbeitet · 🟢 Erledigt</li>
            <li>Die Support-Ansicht aktualisiert sich automatisch alle 15 Sekunden.</li>
        </ul>
    </div>
</div>
@endsection

