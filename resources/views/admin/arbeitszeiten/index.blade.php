@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Seitentitel + Suche --}}
    <div class="card p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-sky-600"></i>Arbeitszeit-Auswertung
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Alle erfassten Arbeitszeiten pro Person. Tage mit weniger als 1,5 Stunden sind
                    <span class="text-amber-600 font-bold">gelb markiert</span>.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-secondary self-start sm:self-auto">
                <i class="fa-solid fa-arrow-left mr-1"></i> Zurück
            </a>
        </div>

        {{-- Suchformular --}}
        <form method="GET" action="{{ route('admin.arbeitszeiten') }}" class="mt-4 flex gap-2">
            <input
                type="text"
                name="suche"
                value="{{ $suche }}"
                placeholder="Person suchen …"
                class="input flex-1"
            >
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-magnifying-glass mr-1"></i>Suchen
            </button>
            @if($suche)
                <a href="{{ route('admin.arbeitszeiten') }}" class="btn-secondary">
                    <i class="fa-solid fa-xmark mr-1"></i>Zurücksetzen
                </a>
            @endif
        </form>
    </div>

    {{-- Legende --}}
    <div class="flex flex-wrap gap-4 text-sm">
        <div class="flex items-center gap-2">
            <span class="inline-block w-4 h-4 rounded bg-emerald-100 border border-emerald-300"></span>
            <span>Alle Tage ≥ 1,5 Stunden</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-4 h-4 rounded bg-amber-100 border border-amber-300"></span>
            <span>Mindestens ein Tag unter 1,5 Stunden</span>
        </div>
    </div>

    {{-- Tabelle --}}
    <div class="card overflow-x-auto">
        <table class="min-w-full text-left" id="arbeitszeitTable">
            <thead class="text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3 text-center">Arbeitstage</th>
                    <th class="px-4 py-3 text-center">Gesamtzeit</th>
                    <th class="px-4 py-3 text-center">Tage &lt; 1,5 h</th>
                    <th class="px-4 py-3 text-center">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($statistiken as $stat)
                    @php
                        $hatWarnung = $stat['tage_unter_90min'] > 0;
                        $gesamtStd  = floor($stat['minuten_gesamt'] / 60);
                        $gesamtMin  = $stat['minuten_gesamt'] % 60;
                        $rowClass   = $hatWarnung ? 'bg-amber-50' : '';
                    @endphp
                    <tr class="{{ $rowClass }} hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-semibold">
                            {{ $stat['customer']->name }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($stat['tage_gesamt'] > 0)
                                {{ $stat['tage_gesamt'] }}
                            @else
                                <span class="text-slate-400">–</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono">
                            @if($stat['minuten_gesamt'] > 0)
                                {{ $gesamtStd }}h {{ str_pad($gesamtMin, 2, '0', STR_PAD_LEFT) }}min
                            @else
                                <span class="text-slate-400">–</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($stat['tage_unter_90min'] > 0)
                                <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-sm font-bold px-2 py-1 rounded-full">
                                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                    {{ $stat['tage_unter_90min'] }}
                                </span>
                            @elseif($stat['tage_gesamt'] > 0)
                                <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-sm font-bold px-2 py-1 rounded-full">
                                    <i class="fa-solid fa-check text-xs"></i> OK
                                </span>
                            @else
                                <span class="text-slate-400">–</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.arbeitszeiten.person', $stat['customer']) }}"
                               class="inline-flex items-center gap-1 text-sky-600 hover:underline font-semibold text-sm">
                                <i class="fa-solid fa-eye"></i> Verlauf
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                            @if($suche)
                                Keine Person mit dem Namen „{{ $suche }}" gefunden.
                            @else
                                Noch keine Personen vorhanden.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection

@push('css')
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.7/datatables.min.css" rel="stylesheet">
@endpush

@push('js')
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.7/datatables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // DataTable nur initialisieren wenn keine Suche aktiv (sonst doppelte Filterung)
            @if(!$suche)
            $('#arbeitszeitTable').DataTable({
                language: {
                    search:   "Filtern:",
                    lengthMenu: "_MENU_ pro Seite",
                    info:     "_START_ bis _END_ von _TOTAL_ Personen",
                    paginate: { next: "Weiter", previous: "Zurück" },
                    zeroRecords: "Keine Einträge gefunden",
                    emptyTable: "Keine Daten vorhanden",
                },
                order: [[3, 'desc']],
            });
            @endif
        });
    </script>
@endpush

