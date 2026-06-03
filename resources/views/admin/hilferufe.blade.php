@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Kopf --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-3xl font-extrabold flex items-center gap-2">
            <i class="fa-solid fa-bell text-rose-500"></i> Hilferufe – Übersicht
        </h1>
        <div class="flex gap-2">
            @if($stats['erledigt'] > 0)
                <form method="POST" action="{{ route('admin.hilferufe.leeren') }}"
                      onsubmit="return confirm('Alle {{ $stats['erledigt'] }} erledigten Hilferufe wirklich löschen?')">
                    @csrf
                    <button type="submit" class="btn btn-ghost text-sm text-slate-500">
                        <i class="fa-solid fa-trash-can mr-1"></i> Erledigte löschen ({{ $stats['erledigt'] }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Statistik-Karten --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('admin.hilferufe') }}"
           class="card p-5 border-l-8 border-slate-400 hover:shadow-lg transition {{ $status === '' && $datum === '' ? 'ring-2 ring-slate-400' : '' }}">
            <div class="text-xs font-bold text-slate-500 uppercase">Gesamt</div>
            <div class="text-4xl font-extrabold mt-1">{{ $stats['gesamt'] }}</div>
        </a>
        <a href="{{ route('admin.hilferufe', ['status' => 'offen']) }}"
           class="card p-5 border-l-8 border-rose-500 hover:shadow-lg transition {{ $status === 'offen' ? 'ring-2 ring-rose-400' : '' }}">
            <div class="text-xs font-bold text-rose-600 uppercase">🔴 Neu / Offen</div>
            <div class="text-4xl font-extrabold mt-1 text-rose-700">{{ $stats['offen'] }}</div>
        </a>
        <a href="{{ route('admin.hilferufe', ['status' => 'in_bearbeitung']) }}"
           class="card p-5 border-l-8 border-amber-500 hover:shadow-lg transition {{ $status === 'in_bearbeitung' ? 'ring-2 ring-amber-400' : '' }}">
            <div class="text-xs font-bold text-amber-600 uppercase">🟡 In Bearbeitung</div>
            <div class="text-4xl font-extrabold mt-1 text-amber-700">{{ $stats['in_bearbeitung'] }}</div>
        </a>
        <a href="{{ route('admin.hilferufe', ['status' => 'erledigt']) }}"
           class="card p-5 border-l-8 border-emerald-500 hover:shadow-lg transition {{ $status === 'erledigt' ? 'ring-2 ring-emerald-400' : '' }}">
            <div class="text-xs font-bold text-emerald-600 uppercase">🟢 Erledigt</div>
            <div class="text-4xl font-extrabold mt-1 text-emerald-700">{{ $stats['erledigt'] }}</div>
        </a>
    </div>

    {{-- Filter --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.hilferufe') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="label text-sm">Status</label>
                <select name="status" class="field text-sm">
                    <option value="" {{ $status === '' ? 'selected' : '' }}>Alle</option>
                    <option value="offen"          {{ $status === 'offen'          ? 'selected' : '' }}>🔴 Offen</option>
                    <option value="in_bearbeitung" {{ $status === 'in_bearbeitung' ? 'selected' : '' }}>🟡 In Bearbeitung</option>
                    <option value="erledigt"       {{ $status === 'erledigt'       ? 'selected' : '' }}>🟢 Erledigt</option>
                </select>
            </div>
            <div>
                <label class="label text-sm">Datum</label>
                <input type="date" name="datum" value="{{ $datum }}" class="field text-sm">
            </div>
            <button type="submit" class="btn btn-primary text-sm">
                <i class="fa-solid fa-filter mr-1"></i> Filtern
            </button>
            @if($status !== '' || $datum !== '')
                <a href="{{ route('admin.hilferufe') }}" class="btn btn-ghost text-sm">
                    <i class="fa-solid fa-xmark mr-1"></i> Filter zurücksetzen
                </a>
            @endif
        </form>
    </div>

    {{-- Tabelle --}}
    <div class="card overflow-x-auto">
        <table class="min-w-full text-left text-sm" id="hilferufeTable">
            <thead class="text-xs uppercase text-slate-500 border-b-2 border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">Zeit</th>
                    <th class="px-4 py-3">Betrieb</th>
                    <th class="px-4 py-3">Nachricht</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Bearbeiter</th>
                    <th class="px-4 py-3">Notiz</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($hilferufe as $hilferuf)
                    <tr class="hover:bg-slate-50
                        {{ $hilferuf->status === 'offen' ? 'bg-rose-50/40' : '' }}
                        {{ $hilferuf->status === 'in_bearbeitung' ? 'bg-amber-50/40' : '' }}">

                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                            <div class="font-semibold text-slate-700">{{ $hilferuf->created_at->format('d.m.Y') }}</div>
                            <div>{{ $hilferuf->created_at->format('H:i') }} Uhr</div>
                            <div class="text-xs text-slate-400">{{ $hilferuf->created_at->diffForHumans() }}</div>
                        </td>

                        <td class="px-4 py-3">
                            <span class="font-bold text-base">{{ $hilferuf->betrieb->name ?? '–' }}</span>
                        </td>

                        <td class="px-4 py-3 max-w-xs">
                            @if($hilferuf->nachricht)
                                <span class="text-slate-700">{{ $hilferuf->nachricht }}</span>
                            @else
                                <span class="text-slate-400 italic">keine</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $badgeClass = match($hilferuf->status) {
                                    'offen'          => 'bg-rose-100 text-rose-800 border border-rose-300',
                                    'in_bearbeitung' => 'bg-amber-100 text-amber-800 border border-amber-300',
                                    'erledigt'       => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
                                    default          => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="inline-block rounded-xl px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                                {{ $hilferuf->statusLabel() }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            {{ $hilferuf->bearbeiter ?? '–' }}
                        </td>

                        <td class="px-4 py-3 max-w-xs text-slate-600">
                            {{ $hilferuf->notiz ?? '–' }}
                        </td>

                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.hilferufe.delete', $hilferuf) }}"
                                  onsubmit="return confirm('Diesen Hilferuf wirklich löschen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-rose-500 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-50"
                                        title="Löschen">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                            <i class="fa-solid fa-circle-check text-3xl mb-2 block text-emerald-400"></i>
                            Keine Hilferufe für diese Auswahl.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection

