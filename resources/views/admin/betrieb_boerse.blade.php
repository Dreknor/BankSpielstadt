@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost py-2 px-3 text-sm">
                <i class="fa-solid fa-arrow-left"></i> Zurück
            </a>
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-amber-500"></i>
                Radi-Börse – {{ $betrieb->name }}
            </h2>
        </div>

        {{-- aktueller Status --}}
        <div class="mb-6 p-4 rounded-2xl border-2
             {{ $betrieb->is_boerse ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-slate-50' }}">
            <div class="font-bold text-lg mb-1">
                @if($betrieb->is_boerse)
                    <i class="fa-solid fa-circle-check text-amber-500 mr-2"></i>
                    Dieser Betrieb ist als <strong>Börse</strong> markiert.
                @else
                    <i class="fa-solid fa-circle-xmark text-slate-400 mr-2"></i>
                    Dieser Betrieb ist <strong>nicht</strong> als Börse markiert.
                @endif
            </div>

            @if($betrieb->is_boerse)
                <p class="text-sm text-amber-800 mt-2">
                    Mitarbeiter dieses Betriebs kommen beim Anmelden per Betriebs-PIN
                    automatisch ins <strong>Börsen-Frontend</strong> (<code>/boerse</code>),
                    nicht in die normale Kasse.
                </p>

                @if($betrieb->betrieb_pin)
                    <div class="mt-3 inline-flex items-center gap-2 bg-white border border-amber-200 rounded-xl px-4 py-2 text-sm">
                        <i class="fa-solid fa-key text-amber-500"></i>
                        <span class="text-slate-600">Betriebs-PIN:</span>
                        <code class="font-mono font-bold text-amber-700">{{ $betrieb->betrieb_pin }}</code>
                    </div>
                @endif
            @else
                @if($boerseBetrieb && $boerseBetrieb->id !== $betrieb->id)
                    <p class="text-sm text-amber-700 mt-2">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        Aktuell ist <strong>{{ $boerseBetrieb->name }}</strong> als Börse markiert.
                        Das Aktivieren hier entfernt diese Markierung automatisch.
                    </p>
                @endif

                @if(! $betrieb->betrieb_pin)
                    <p class="text-sm text-rose-700 mt-2">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i>
                        Dieser Betrieb hat noch keinen Betriebs-PIN.
                        Bitte zuerst einen PIN vergeben, bevor die Börse aktiviert werden kann.
                    </p>
                @endif
            @endif
        </div>

        {{-- Aktionsbuttons --}}
        <div class="flex flex-wrap gap-3">
            @if($betrieb->is_boerse)
                <form method="POST" action="{{ route('admin.betriebe.boerse.store', $betrieb) }}"
                      onsubmit="return confirm('Boerse-Markierung wirklich entfernen? Die Mitarbeiter kommen dann wieder in die normale Betriebs-Kasse.')">
                    @csrf
                    <input type="hidden" name="aktion" value="deaktivieren">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-toggle-off mr-1"></i> Börse deaktivieren
                    </button>
                </form>
            @elseif($betrieb->betrieb_pin)
                <form method="POST" action="{{ route('admin.betriebe.boerse.store', $betrieb) }}">
                    @csrf
                    <input type="hidden" name="aktion" value="aktivieren">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-toggle-on mr-1"></i> Als Börse aktivieren
                    </button>
                </form>
            @else
                <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost">
                    <i class="fa-solid fa-key mr-1"></i> Zuerst PIN setzen
                </a>
            @endif
        </div>
    </div>

    {{-- Info-Box: Wie funktioniert die Börse? --}}
    <div class="card p-6 bg-sky-50 border-2 border-sky-200 space-y-2">
        <h3 class="font-extrabold text-sky-800 flex items-center gap-2">
            <i class="fa-solid fa-circle-info"></i>
            Wie funktioniert die Börse?
        </h3>
        <ul class="text-sm text-sky-800 space-y-1 list-disc list-inside">
            <li>Genau <strong>ein</strong> Betrieb kann als Börse markiert sein.</li>
            <li>Börsenmitarbeiter melden sich wie gewohnt bei <code>/betrieb/login</code> an.</li>
            <li>Bei erkanntem Börsen-Betrieb werden sie automatisch zum Börsen-Frontend weitergeleitet.</li>
            <li>Im Börsen-Frontend können Kinder Anteile kaufen, verkaufen und Kurse einsehen.</li>
            <li>Die Börsen-Kasse (Bargeld) ist vom Betriebskonto (Radi-Konto) getrennt.</li>
        </ul>

        <div class="pt-2">
            <a href="{{ route('admin.boerse') }}" class="btn btn-ghost text-sm">
                <i class="fa-solid fa-chart-line mr-1 text-amber-600"></i>
                Admin-Bereich Radi-Börse
            </a>
        </div>
    </div>
</div>
@endsection

