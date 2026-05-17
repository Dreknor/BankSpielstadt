@extends('betrieb.layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('betrieb.fotostudio.index') }}"
           class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold">
            <i class="fa-solid fa-arrow-left mr-1"></i>Zurück
        </a>
        <h1 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-upload text-emerald-600"></i> Bilder hochladen
        </h1>
    </div>

    <form method="POST" action="{{ route('betrieb.fotostudio.store') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl border-2 border-slate-200 p-6 space-y-5">
        @csrf

        {{-- Dateiauswahl --}}
        <div>
            <label class="block font-bold text-slate-700 text-lg mb-2">
                <i class="fa-solid fa-images mr-1 text-emerald-600"></i>
                Bilder auswählen <span class="text-rose-500">*</span>
            </label>
            <div id="dropzone"
                 class="border-4 border-dashed border-emerald-300 rounded-2xl p-8 text-center cursor-pointer hover:border-emerald-500 transition-colors bg-emerald-50">
                <i class="fa-solid fa-cloud-arrow-up text-5xl text-emerald-400 mb-3 block"></i>
                <p class="text-slate-600 font-semibold text-lg">Bilder hierher ziehen</p>
                <p class="text-slate-400 text-sm mt-1">oder klicken zum Auswählen</p>
                <input type="file" name="bilder[]" id="bilder" multiple accept="image/*"
                       class="hidden">
            </div>
            <div id="vorschau" class="mt-3 grid grid-cols-3 gap-2 hidden"></div>
            @error('bilder')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            @error('bilder.*')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Titel (gilt für alle hochgeladenen Bilder) --}}
        <div>
            <label for="titel_0" class="block font-bold text-slate-700 mb-1">
                Titel <span class="text-slate-400 font-normal">(optional – erscheint in der Slideshow)</span>
            </label>
            <input type="text" name="titel[0]" id="titel_0" maxlength="120"
                   value="{{ old('titel.0') }}"
                   placeholder="z. B. Klassenfoto Klasse 4a"
                   class="w-full rounded-xl border-2 border-slate-200 px-4 py-2 text-lg focus:border-emerald-400 focus:outline-none">
        </div>

        {{-- Sichtbar --}}
        <div class="flex items-center gap-3">
            <input type="hidden" name="sichtbar" value="0">
            <input type="checkbox" name="sichtbar" id="sichtbar" value="1"
                   {{ old('sichtbar', '1') == '1' ? 'checked' : '' }}
                   class="w-5 h-5 rounded accent-emerald-600">
            <label for="sichtbar" class="font-bold text-slate-700 text-lg cursor-pointer">
                Sofort in der Slideshow anzeigen
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
                           value="{{ old('anzeige_von') }}"
                           class="w-full rounded-xl border-2 border-slate-200 px-3 py-2 focus:border-emerald-400 focus:outline-none">
                    @error('anzeige_von')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="anzeige_bis" class="block text-slate-600 font-semibold mb-1">Anzeigen bis</label>
                    <input type="datetime-local" name="anzeige_bis" id="anzeige_bis"
                           value="{{ old('anzeige_bis') }}"
                           class="w-full rounded-xl border-2 border-slate-200 px-3 py-2 focus:border-emerald-400 focus:outline-none">
                    @error('anzeige_bis')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Reihenfolge --}}
        <div>
            <label for="reihenfolge" class="block font-bold text-slate-700 mb-1">
                Reihenfolge <span class="text-slate-400 font-normal">(kleinere Zahl = zuerst, 0 = am Anfang)</span>
            </label>
            <input type="number" name="reihenfolge" id="reihenfolge" min="0" max="999"
                   value="{{ old('reihenfolge', 0) }}"
                   class="w-32 rounded-xl border-2 border-slate-200 px-4 py-2 text-lg focus:border-emerald-400 focus:outline-none">
        </div>

        <button type="submit"
                class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xl rounded-2xl shadow-lg flex items-center justify-center gap-2">
            <i class="fa-solid fa-upload"></i> Bilder hochladen
        </button>
    </form>
</div>

@push('js')
<script>
const dropzone = document.getElementById('dropzone');
const input    = document.getElementById('bilder');
const vorschau = document.getElementById('vorschau');

dropzone.addEventListener('click', () => input.click());
dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('border-emerald-500','bg-emerald-100'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-emerald-500','bg-emerald-100'));
dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('border-emerald-500','bg-emerald-100');
    const dt = new DataTransfer();
    [...e.dataTransfer.files].forEach(f => dt.items.add(f));
    input.files = dt.files;
    zeigeVorschau(input.files);
});
input.addEventListener('change', () => zeigeVorschau(input.files));

function zeigeVorschau(files) {
    vorschau.innerHTML = '';
    if (!files.length) { vorschau.classList.add('hidden'); return; }
    vorschau.classList.remove('hidden');
    [...files].forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-24 object-cover rounded-xl border border-slate-200';
            vorschau.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush
@endsection

