@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="card p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">
                    <i class="fa-solid fa-user-clock mr-2 text-sky-600"></i>
                    Arbeitszeiten von <span class="text-brand-600">{{ $customer->name }}</span>
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Gesamt:
                    <strong class="font-mono text-slate-700">{{ $gesamtStunden }}h {{ str_pad($gesamtRestMin, 2, '0', STR_PAD_LEFT) }}min</strong>
                    über {{ $tageDetails->count() }} {{ $tageDetails->count() === 1 ? 'Tag' : 'Tage' }}
                </p>
            </div>
            <a href="{{ route('admin.arbeitszeiten') }}" class="btn-secondary self-start sm:self-auto">
                <i class="fa-solid fa-arrow-left mr-1"></i> Zur Übersicht
            </a>
        </div>
    </div>

    {{-- Legende --}}
    <div class="flex flex-wrap gap-4 text-sm">
        <div class="flex items-center gap-2">
            <span class="inline-block w-4 h-4 rounded bg-emerald-100 border border-emerald-300"></span>
            <span>≥ 1,5 Stunden an diesem Tag</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-4 h-4 rounded bg-amber-100 border border-amber-300"></span>
            <span>Weniger als 1,5 Stunden an diesem Tag</span>
        </div>
    </div>

    @if($tageDetails->isEmpty())
        <div class="card p-8 text-center text-slate-400">
            <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 block"></i>
            Für diese Person wurden noch keine Arbeitszeiten erfasst.
        </div>
    @else

    {{-- Tage-Liste --}}
    <div class="space-y-4">
        @foreach($tageDetails as $tag)
            @php
                $bgCard  = $tag['warnung'] ? 'border-l-4 border-amber-400 bg-amber-50'  : 'border-l-4 border-emerald-400 bg-white';
                $badgeCls = $tag['warnung']
                    ? 'bg-amber-100 text-amber-800 border border-amber-300'
                    : 'bg-emerald-100 text-emerald-700 border border-emerald-300';
            @endphp
            <div class="card {{ $bgCard }} overflow-hidden">
                {{-- Tag-Header --}}
                <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100">
                    <div class="font-extrabold text-lg text-slate-800">
                        <i class="fa-regular fa-calendar mr-2 text-slate-400"></i>
                        {{ $tag['datum']->translatedFormat('l, d.m.Y') }}
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-slate-600 text-sm">
                            {{ $tag['stunden'] }}h {{ str_pad($tag['restmin'], 2, '0', STR_PAD_LEFT) }}min
                        </span>
                        @if($tag['warnung'])
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full {{ $badgeCls }}">
                                <i class="fa-solid fa-triangle-exclamation"></i> Weniger als 1,5 Stunden!
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full {{ $badgeCls }}">
                                <i class="fa-solid fa-check"></i> OK
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Einzel-Einträge --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="text-xs uppercase text-slate-400 bg-slate-50">
                            <tr>
                                <th class="px-4 py-2">Betrieb</th>
                                <th class="px-4 py-2">Von</th>
                                <th class="px-4 py-2">Bis</th>
                                <th class="px-4 py-2">Dauer</th>
                                <th class="px-4 py-2">Rolle</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tag['eintraege'] as $eintrag)
                                @php
                                    $dauer = $eintrag->duration;
                                    $std   = floor($dauer / 60);
                                    $min   = $dauer % 60;
                                @endphp
                                <tr class="hover:bg-white transition">
                                    <td class="px-4 py-2 font-semibold text-slate-700">
                                        {{ optional($eintrag->buisness)->name ?? '–' }}
                                    </td>
                                    <td class="px-4 py-2 font-mono text-slate-600">
                                        {{ $eintrag->start->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-2 font-mono text-slate-600">
                                        {{ $eintrag->end->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-2 font-mono font-bold text-slate-700">
                                        @if($std > 0){{ $std }}h @endif{{ str_pad($min, 2, '0', STR_PAD_LEFT) }}min
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($eintrag->is_manager())
                                            <span class="bg-violet-100 text-violet-700 text-xs font-bold px-2 py-1 rounded-full">Chef</span>
                                        @else
                                            <span class="bg-sky-100 text-sky-700 text-xs font-bold px-2 py-1 rounded-full">Mitarbeiter</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.arbeitszeiten.edit', [$customer, $eintrag]) }}"
                                           class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-lg bg-amber-100 text-amber-800 hover:bg-amber-200 transition mr-1">
                                            <i class="fa-solid fa-pen-to-square"></i> Korrigieren
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.arbeitszeiten.destroy', [$customer, $eintrag]) }}"
                                              class="inline"
                                              onsubmit="return confirm('Eintrag wirklich löschen? Die zugehörigen Zahlungen werden ebenfalls rückgängig gemacht.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 transition">
                                                <i class="fa-solid fa-trash"></i> Löschen
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-xs text-slate-500 uppercase">Tages-Summe</td>
                                <td class="px-4 py-2 font-mono font-extrabold text-slate-800" colspan="2">
                                    {{ $tag['stunden'] }}h {{ str_pad($tag['restmin'], 2, '0', STR_PAD_LEFT) }}min
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    @endif
</div>
@endsection

