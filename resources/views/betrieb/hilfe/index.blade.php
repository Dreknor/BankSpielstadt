@extends('betrieb.layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Überschrift --}}
    <div class="card p-6 text-center">
        <div class="text-5xl mb-2">🆘</div>
        <h1 class="text-4xl font-extrabold text-slate-800">Hilferufe</h1>
        <p class="text-slate-500 mt-1">Hier seht ihr, welche Betriebe Hilfe brauchen.</p>
        @if($anzahlOffen > 0)
            <div class="mt-3 inline-block bg-rose-100 border-2 border-rose-400 text-rose-800 font-extrabold text-xl px-5 py-2 rounded-2xl animate-pulse">
                🔴 {{ $anzahlOffen }} {{ $anzahlOffen === 1 ? 'Betrieb braucht' : 'Betriebe brauchen' }} jetzt Hilfe!
            </div>
        @else
            <div class="mt-3 inline-block bg-emerald-100 border-2 border-emerald-400 text-emerald-800 font-bold text-lg px-5 py-2 rounded-2xl">
                🟢 Alles gut – keine offenen Hilferufe.
            </div>
        @endif
    </div>

    {{-- Hilferufe-Liste --}}
    @forelse($hilferufe as $hilferuf)
        <div class="card border-4 {{ $hilferuf->statusKlasse() }} p-5 space-y-4">

            {{-- Kopfzeile: Betrieb + Status + Zeit --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="text-4xl">🏪</div>
                    <div>
                        <div class="text-2xl font-extrabold">{{ $hilferuf->betrieb->name }}</div>
                        <div class="text-sm text-slate-500">
                            <i class="fa-regular fa-clock mr-1"></i>
                            {{ $hilferuf->created_at->diffForHumans() }}
                            ({{ $hilferuf->created_at->format('H:i') }} Uhr)
                        </div>
                    </div>
                </div>
                <div class="text-xl font-extrabold px-4 py-2 rounded-2xl border-2 {{ $hilferuf->statusKlasse() }}">
                    {{ $hilferuf->statusLabel() }}
                </div>
            </div>

            {{-- Nachricht des Betriebs --}}
            @if($hilferuf->nachricht)
                <div class="bg-white/60 rounded-2xl p-4 border border-slate-200">
                    <div class="text-sm font-bold text-slate-500 mb-1"><i class="fa-solid fa-comment mr-1"></i>Nachricht vom Betrieb:</div>
                    <div class="text-lg">{{ $hilferuf->nachricht }}</div>
                </div>
            @endif

            {{-- Bearbeitungs-Formular --}}
            <form action="{{ route('betrieb.hilfe.update', $hilferuf) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-600 mb-1">
                            <i class="fa-solid fa-circle-dot mr-1"></i> Status
                        </label>
                        <select name="status"
                                class="w-full rounded-xl border-2 border-slate-300 focus:border-emerald-400 focus:outline-none p-2 font-semibold text-base">
                            <option value="offen"          {{ $hilferuf->status === 'offen'          ? 'selected' : '' }}>🔴 Neu</option>
                            <option value="in_bearbeitung" {{ $hilferuf->status === 'in_bearbeitung' ? 'selected' : '' }}>🟡 Wird bearbeitet</option>
                            <option value="erledigt"       {{ $hilferuf->status === 'erledigt'       ? 'selected' : '' }}>🟢 Erledigt</option>
                        </select>
                    </div>

                    {{-- Bearbeiter --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-600 mb-1">
                            <i class="fa-solid fa-person mr-1"></i> Wer hilft? <span class="font-normal text-slate-400">(optional)</span>
                        </label>
                        <input type="text"
                               name="bearbeiter"
                               value="{{ old('bearbeiter', $hilferuf->bearbeiter) }}"
                               maxlength="100"
                               placeholder="Dein Name"
                               class="w-full rounded-xl border-2 border-slate-300 focus:border-emerald-400 focus:outline-none p-2 text-base">
                    </div>

                    {{-- Notiz --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-600 mb-1">
                            <i class="fa-solid fa-pen mr-1"></i> Notiz <span class="font-normal text-slate-400">(optional)</span>
                        </label>
                        <input type="text"
                               name="notiz"
                               value="{{ old('notiz', $hilferuf->notiz) }}"
                               maxlength="500"
                               placeholder="Was wurde gemacht?"
                               class="w-full rounded-xl border-2 border-slate-300 focus:border-emerald-400 focus:outline-none p-2 text-base">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit"
                            class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold px-6 py-3 rounded-xl text-lg transition-all">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Speichern
                    </button>
                </div>
            </form>

        </div>
    @empty
        <div class="card p-10 text-center text-slate-400">
            <div class="text-6xl mb-3">😊</div>
            <div class="text-2xl font-bold">Noch keine Hilferufe.</div>
            <div class="mt-1">Alle Betriebe kommen gut zurecht!</div>
        </div>
    @endforelse

</div>
@endsection

@push('js')
<script>
    // Seite alle 15 Sekunden neu laden, damit neue Hilferufe sofort erscheinen
    setTimeout(function () { window.location.reload(); }, 15000);
</script>
@endpush

