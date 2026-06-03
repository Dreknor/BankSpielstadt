@extends('betrieb.layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('betrieb.fotostudio.index') }}"
           class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold">
            <i class="fa-solid fa-arrow-left mr-1"></i>Zurück
        </a>
        <h1 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-pencil text-emerald-600"></i> Bild bearbeiten
        </h1>
    </div>

    {{-- Bildvorschau --}}
    <div class="rounded-2xl overflow-hidden border-2 border-slate-200 bg-slate-900">
        <img src="{{ $bild->url() }}" alt="{{ $bild->titel ?? $bild->dateiname }}"
             class="w-full max-h-72 object-contain mx-auto block">
    </div>

    <form method="POST" action="{{ route('betrieb.fotostudio.update', $bild) }}"
          class="bg-white rounded-2xl border-2 border-slate-200 p-6 space-y-5">
        @csrf @method('PUT')

        {{-- Titel --}}
        <div>
            <label for="titel" class="block font-bold text-slate-700 mb-1">
                Titel <span class="text-slate-400 font-normal">(optional – erscheint in der Slideshow)</span>
            </label>
            <input type="text" name="titel" id="titel" maxlength="120"
                   value="{{ old('titel', $bild->titel) }}"
                   placeholder="z. B. Klassenfoto Klasse 4a"
                   class="w-full rounded-xl border-2 border-slate-200 px-4 py-2 text-lg focus:border-emerald-400 focus:outline-none">
            @error('titel')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Sichtbar --}}
        <div class="flex items-center gap-3">
            <input type="hidden" name="sichtbar" value="0">
            <input type="checkbox" name="sichtbar" id="sichtbar" value="1"
                   {{ old('sichtbar', $bild->sichtbar ? '1' : '0') == '1' ? 'checked' : '' }}
                   class="w-5 h-5 rounded accent-emerald-600">
            <label for="sichtbar" class="font-bold text-slate-700 text-lg cursor-pointer">
                In der Slideshow anzeigen
            </label>
        </div>

        {{-- Zeitbegrenzung --}}
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 space-y-4">
            <div class="font-bold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-clock text-amber-500"></i>
                Zeitbegrenzung <span class="text-slate-400 font-normal text-sm">(leer lassen = unbegrenzt)</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="anzeige_von" class="block text-slate-600 font-semibold mb-1">Anzeigen ab</label>
                    <input type="datetime-local" name="anzeige_von" id="anzeige_von"
                           value="{{ old('anzeige_von', $bild->anzeige_von?->format('Y-m-d\TH:i')) }}"
                           class="w-full rounded-xl border-2 border-slate-200 px-3 py-2 focus:border-emerald-400 focus:outline-none">
                    @error('anzeige_von')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="anzeige_bis" class="block text-slate-600 font-semibold mb-1">Anzeigen bis</label>
                    <input type="datetime-local" name="anzeige_bis" id="anzeige_bis"
                           value="{{ old('anzeige_bis', $bild->anzeige_bis?->format('Y-m-d\TH:i')) }}"
                           class="w-full rounded-xl border-2 border-slate-200 px-3 py-2 focus:border-emerald-400 focus:outline-none">
                    @error('anzeige_bis')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Reihenfolge --}}
        <div>
            <label for="reihenfolge" class="block font-bold text-slate-700 mb-1">
                Reihenfolge <span class="text-slate-400 font-normal">(kleinere Zahl = zuerst)</span>
            </label>
            <input type="number" name="reihenfolge" id="reihenfolge" min="0" max="999"
                   value="{{ old('reihenfolge', $bild->reihenfolge) }}"
                   class="w-32 rounded-xl border-2 border-slate-200 px-4 py-2 text-lg focus:border-emerald-400 focus:outline-none">
        </div>

        {{-- Anzeigedauer --}}
        <div>
            <label for="anzeige_dauer" class="block font-bold text-slate-700 mb-1">
                Anzeigedauer <span class="text-slate-400 font-normal">(Sekunden, Standard: 5s)</span>
            </label>
            <input type="number" name="anzeige_dauer" id="anzeige_dauer" min="0" max="999"
                   value="{{ old('anzeige_dauer', $bild->anzeige_dauer) }}"
                   class="w-32 rounded-xl border-2 border-slate-200 px-4 py-2 text-lg focus:border-emerald-400 focus:outline-none">
        </div>

        <button type="submit"
                class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xl rounded-2xl shadow-lg flex items-center justify-center gap-2">
            <i class="fa-solid fa-floppy-disk"></i> Änderungen speichern
        </button>
    </form>

    {{-- Löschen --}}
    <form method="POST" action="{{ route('betrieb.fotostudio.destroy', $bild) }}"
          onsubmit="return confirm('Bild wirklich löschen? Das kann nicht rückgängig gemacht werden!')">
        @csrf @method('DELETE')
        <button type="submit"
                class="w-full py-3 bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold rounded-2xl flex items-center justify-center gap-2">
            <i class="fa-solid fa-trash"></i> Bild löschen
        </button>
    </form>
</div>
@endsection

