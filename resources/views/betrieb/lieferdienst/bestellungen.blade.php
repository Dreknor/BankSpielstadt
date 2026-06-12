@extends('betrieb.layouts.app')

@section('content')
<div class="space-y-5">

    {{-- Kopfzeile --}}
    <div class="card p-5 flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-2xl font-extrabold flex items-center gap-2">
            <i class="fa-solid fa-list-check text-emerald-600"></i>
            Lieferbestellungen
        </h1>
        <a href="{{ route('betrieb.lieferprodukte') }}" class="btn btn-ghost">
            <i class="fa-solid fa-boxes-stacked mr-1"></i> Lieferprodukte verwalten
        </a>
    </div>

    {{-- Statistik --}}
    @php
        $neu           = $bestellungen->where('status', 'neu')->count();
        $inBearbeitung = $bestellungen->where('status', 'in_bearbeitung')->count();
        $erledigt      = $bestellungen->where('status', 'erledigt')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="card p-4 text-center border-2 border-rose-200 bg-rose-50">
            <div class="text-3xl font-extrabold text-rose-600">{{ $neu }}</div>
            <div class="text-sm font-semibold text-rose-700 mt-1">🔴 Neu</div>
        </div>
        <div class="card p-4 text-center border-2 border-amber-200 bg-amber-50">
            <div class="text-3xl font-extrabold text-amber-600">{{ $inBearbeitung }}</div>
            <div class="text-sm font-semibold text-amber-700 mt-1">🟡 In Bearbeitung</div>
        </div>
        <div class="card p-4 text-center border-2 border-emerald-200 bg-emerald-50">
            <div class="text-3xl font-extrabold text-emerald-600">{{ $erledigt }}</div>
            <div class="text-sm font-semibold text-emerald-700 mt-1">🟢 Erledigt</div>
        </div>
    </div>

    @if($bestellungen->isEmpty())
        <div class="card p-10 text-center text-slate-400">
            <i class="fa-solid fa-motorcycle text-5xl mb-3"></i>
            <p class="text-xl font-semibold">Noch keine Bestellungen eingegangen.</p>
            <p class="mt-2 text-sm">Wenn jemand auf der Bestell-Seite bestellt, erscheint es hier.</p>
        </div>
    @else

    {{-- Bestellungen --}}
    <div class="space-y-4">
        @foreach($bestellungen as $bestellung)
        @php
            $borderColor = match($bestellung->status) {
                'neu'            => 'border-rose-300',
                'in_bearbeitung' => 'border-amber-300',
                'erledigt'       => 'border-emerald-300',
            };
            $bgColor = match($bestellung->status) {
                'neu'            => 'bg-rose-50',
                'in_bearbeitung' => 'bg-amber-50',
                'erledigt'       => 'bg-emerald-50',
            };
        @endphp
        <div class="card p-5 border-2 {{ $borderColor }} {{ $bgColor }}">
            <div class="flex items-start justify-between flex-wrap gap-3 mb-4">
                <div>
                    <div class="text-lg font-extrabold">
                        {{ $bestellung->statusLabel() }}
                        &nbsp;·&nbsp;
                        <span class="text-slate-700">{{ $bestellung->besteller_name }}</span>
                    </div>
                    <div class="text-slate-600 mt-1 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-rose-400"></i>
                        <strong>{{ $bestellung->lieferort }}</strong>
                    </div>
                    <div class="text-slate-500 text-sm mt-1">
                        <i class="fa-regular fa-clock mr-1"></i>
                        {{ $bestellung->created_at->format('d.m.Y H:i') }} Uhr
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-extrabold text-emerald-700">{{ $bestellung->gesamtbetrag }} Radi</div>
                    @if($bestellung->mitarbeiter)
                        <div class="text-sm text-slate-600 mt-1">
                            <i class="fa-solid fa-person-biking mr-1 text-emerald-600"></i>
                            {{ $bestellung->mitarbeiter->name }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Positionen --}}
            <div class="bg-white/60 rounded-xl p-3 mb-4">
                <div class="font-semibold text-sm text-slate-500 mb-2">Bestellte Artikel:</div>
                <ul class="space-y-1">
                    @foreach($bestellung->positionen as $pos)
                    <li class="flex justify-between text-sm">
                        <span>{{ $pos->menge }}× {{ $pos->product->name ?? '(gelöscht)' }}</span>
                        <span class="font-bold">{{ $pos->menge * $pos->einzelpreis }} Radi</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Aktionen --}}
            <div class="flex flex-wrap gap-3">

                {{-- Status ändern --}}
                <form method="POST" action="{{ route('betrieb.lieferbestellung.status', $bestellung) }}"
                      class="flex items-center gap-2">
                    @csrf
                    <select name="status" class="field text-sm py-2 pr-8">
                        <option value="neu"            {{ $bestellung->status === 'neu' ? 'selected' : '' }}>🔴 Neu</option>
                        <option value="in_bearbeitung" {{ $bestellung->status === 'in_bearbeitung' ? 'selected' : '' }}>🟡 In Bearbeitung</option>
                        <option value="erledigt"       {{ $bestellung->status === 'erledigt' ? 'selected' : '' }}>🟢 Erledigt</option>
                    </select>
                    <button type="submit" class="btn btn-success text-sm py-2">
                        <i class="fa-solid fa-check"></i> Status speichern
                    </button>
                </form>

                {{-- Mitarbeiter zuweisen --}}
                <form method="POST" action="{{ route('betrieb.lieferbestellung.mitarbeiter', $bestellung) }}"
                      class="flex items-center gap-2">
                    @csrf
                    <select name="mitarbeiter_id" class="field text-sm py-2 pr-8">
                        <option value="">– Kein Mitarbeiter –</option>
                        @foreach($kinder as $kind)
                            <option value="{{ $kind->id }}"
                                    {{ $bestellung->mitarbeiter_id == $kind->id ? 'selected' : '' }}>
                                {{ $kind->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary text-sm py-2">
                        <i class="fa-solid fa-person-biking"></i> Zuweisen
                    </button>
                </form>

            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

