@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Vorlagen-Download --}}
    <div class="card p-6 bg-sky-50 border-2 border-sky-200">
        <h3 class="font-extrabold text-sky-800 text-lg mb-3 flex items-center gap-2">
            <i class="fa-solid fa-file-excel text-emerald-600"></i>
            Vorlage herunterladen
        </h3>
        <p class="text-sm text-sky-700 mb-4">
            Lade die Vorlage herunter, fülle sie aus und lade sie dann unten hoch.
        </p>

        <a href="{{ route('import.vorlage') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl">
            <i class="fa-solid fa-download"></i> Vorlage herunterladen (.xlsx)
        </a>

        <div class="mt-5 overflow-x-auto">
            <table class="text-xs w-full border border-sky-200 rounded-xl overflow-hidden">
                <thead class="bg-indigo-600 text-white">
                    <tr>
                        <th class="px-3 py-2 text-left">Spalte</th>
                        <th class="px-3 py-2 text-left">Bedeutung</th>
                        <th class="px-3 py-2 text-left">Mögliche Werte</th>
                        <th class="px-3 py-2 text-left">Pflicht?</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-100 bg-white">
                    <tr>
                        <td class="px-3 py-2 font-mono font-bold">name</td>
                        <td class="px-3 py-2">Name des Kindes / Betriebs</td>
                        <td class="px-3 py-2 text-slate-500">Beliebiger Text</td>
                        <td class="px-3 py-2 text-rose-600 font-semibold">Ja</td>
                    </tr>
                    <tr class="bg-sky-50">
                        <td class="px-3 py-2 font-mono font-bold">buisness</td>
                        <td class="px-3 py-2">Ist es ein Betrieb?</td>
                        <td class="px-3 py-2 text-slate-500"><code>0</code> = Kind, <code>1</code> = Betrieb</td>
                        <td class="px-3 py-2 text-slate-400">Nein (Standard: leer)</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono font-bold">startkapital</td>
                        <td class="px-3 py-2">Startkapital in Radi</td>
                        <td class="px-3 py-2 text-slate-500">Zahl, z. B. <code>200</code></td>
                        <td class="px-3 py-2 text-slate-400">Nein (Standard aus Einstellungen)</td>
                    </tr>
                    <tr class="bg-sky-50">
                        <td class="px-3 py-2 font-mono font-bold">kredit</td>
                        <td class="px-3 py-2">Startkredit in Radi</td>
                        <td class="px-3 py-2 text-slate-500">Zahl, z. B. <code>50</code></td>
                        <td class="px-3 py-2 text-slate-400">Nein (Standard: 0)</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono font-bold">betrieb_pin</td>
                        <td class="px-3 py-2">PIN für Betriebs-Kasse <span class="text-emerald-700 font-semibold">(nur Betriebe)</span></td>
                        <td class="px-3 py-2 text-slate-500">mind. 4 Zeichen, z. B. <code>1234</code></td>
                        <td class="px-3 py-2 text-slate-400">Nein</td>
                    </tr>
                    <tr class="bg-sky-50">
                        <td class="px-3 py-2 font-mono font-bold">key</td>
                        <td class="px-3 py-2">Kontostand-Schlüssel <span class="text-slate-500">(für Kinder)</span></td>
                        <td class="px-3 py-2 text-slate-500">mind. 8 Zeichen oder leer</td>
                        <td class="px-3 py-2 text-slate-400">Nein</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono font-bold">export</td>
                        <td class="px-3 py-2">Für Tagesabschluss exportieren?</td>
                        <td class="px-3 py-2 text-slate-500"><code>0</code> = Nein, <code>1</code> = Ja</td>
                        <td class="px-3 py-2 text-slate-400">Nein (Standard: 0)</td>
                    </tr>
                    <tr class="bg-sky-50">
                        <td class="px-3 py-2 font-mono font-bold">is_boerse</td>
                        <td class="px-3 py-2">Betrieb ist die Börse <span class="text-amber-700 font-semibold">(nur Betriebe)</span></td>
                        <td class="px-3 py-2 text-slate-500"><code>0</code> = Nein, <code>1</code> = Ja</td>
                        <td class="px-3 py-2 text-slate-400">Nein (Standard: 0)</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono font-bold">is_fotostudio</td>
                        <td class="px-3 py-2">Betrieb hat Fotostudio <span class="text-purple-700 font-semibold">(nur Betriebe)</span></td>
                        <td class="px-3 py-2 text-slate-500"><code>0</code> = Nein, <code>1</code> = Ja</td>
                        <td class="px-3 py-2 text-slate-400">Nein (Standard: 0)</td>
                    </tr>
                    <tr class="bg-sky-50">
                        <td class="px-3 py-2 font-mono font-bold">aktien_gesamt</td>
                        <td class="px-3 py-2">Anzahl ausgegebener Aktien <span class="text-amber-700 font-semibold">(Börsen-Betriebe)</span></td>
                        <td class="px-3 py-2 text-slate-500">Zahl, z. B. <code>100</code>, oder leer</td>
                        <td class="px-3 py-2 text-slate-400">Nein</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono font-bold">start_kurs</td>
                        <td class="px-3 py-2">Startkurs in Radi <span class="text-amber-700 font-semibold">(Börsen-Betriebe)</span></td>
                        <td class="px-3 py-2 text-slate-500">Zahl, z. B. <code>10</code>, oder leer</td>
                        <td class="px-3 py-2 text-slate-400">Nein</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Upload --}}
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-file-import text-brand-600"></i> Import
        </h2>
        <form action="{{ route('import.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="file" class="label">Excel-Datei (.xlsx oder .xls)</label>
                <input type="file" name="file" id="file" accept=".xlsx,.xls"
                       class="field @error('file') border-rose-400 @enderror">
                @error('file') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-upload"></i> Importieren
            </button>
        </form>
    </div>

</div>
@endsection

@push('js')

@endpush
