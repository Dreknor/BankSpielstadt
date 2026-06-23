@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="card p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">
                    <i class="fa-solid fa-pen-to-square mr-2 text-amber-500"></i>
                    Arbeitszeit korrigieren
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Für <strong>{{ $customer->name }}</strong> —
                    {{ $wt->start->translatedFormat('l, d.m.Y') }}
                </p>
            </div>
            <a href="{{ route('admin.arbeitszeiten.person', $customer) }}" class="btn-secondary self-start sm:self-auto">
                <i class="fa-solid fa-arrow-left mr-1"></i> Zurück
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="card p-4 bg-red-50 border-2 border-red-300">
        <p class="font-bold text-red-700 mb-1">❌ Bitte korrigiere folgende Fehler:</p>
        <ul class="list-disc list-inside text-red-700 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.arbeitszeiten.update', [$customer, $wt]) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Datum --}}
            <div>
                <label class="label font-semibold" for="datum">Datum</label>
                <input type="date" id="datum" name="datum" class="field"
                       value="{{ old('datum', $wt->start->format('Y-m-d')) }}" required>
            </div>

            {{-- Betrieb --}}
            <div>
                <label class="label font-semibold" for="buisness_id">Betrieb</label>
                <select name="buisness_id" id="buisness_id" class="field" required>
                    @foreach($betriebe as $b)
                        <option value="{{ $b->id }}" @selected(old('buisness_id', $wt->buisness_id) == $b->id)>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Rolle --}}
            <div>
                <label class="label font-semibold" for="manager">Rolle</label>
                <select name="manager" id="manager" class="field">
                    <option value="0" @selected(old('manager', $wt->is_manager) == 0)>Mitarbeiter</option>
                    <option value="1" @selected(old('manager', $wt->is_manager) == 1)>Chef</option>
                </select>
            </div>

            {{-- Zeiten --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Anfangszeit --}}
                <div class="bg-slate-50 rounded-2xl p-4 ring-1 ring-slate-200">
                    <h4 class="text-lg font-bold text-center mb-3">Anfangszeit</h4>
                    <div class="flex items-end justify-center gap-2">
                        <div>
                            <label class="label text-sm" for="start_hour">Stunde</label>
                            <input type="number" id="start_hour" name="start_hour" min="0" max="23"
                                   value="{{ old('start_hour', $wt->start->format('H')) }}"
                                   class="field w-24 text-center" required>
                        </div>
                        <div class="text-3xl font-extrabold pb-2">:</div>
                        <div>
                            <label class="label text-sm" for="start_minute">Minute</label>
                            <input type="number" id="start_minute" name="start_minute" min="0" max="59" step="5"
                                   value="{{ old('start_minute', $wt->start->format('i')) }}"
                                   class="field w-24 text-center" required>
                        </div>
                    </div>
                </div>

                {{-- Endzeit --}}
                <div class="bg-slate-50 rounded-2xl p-4 ring-1 ring-slate-200">
                    <h4 class="text-lg font-bold text-center mb-3">Endzeit</h4>
                    <div class="flex items-end justify-center gap-2">
                        <div>
                            <label class="label text-sm" for="end_hour">Stunde</label>
                            <input type="number" id="end_hour" name="end_hour" min="0" max="23"
                                   value="{{ old('end_hour', $wt->end->format('H')) }}"
                                   class="field w-24 text-center" required>
                        </div>
                        <div class="text-3xl font-extrabold pb-2">:</div>
                        <div>
                            <label class="label text-sm" for="end_minute">Minute</label>
                            <input type="number" id="end_minute" name="end_minute" min="0" max="59" step="5"
                                   value="{{ old('end_minute', $wt->end->format('i')) }}"
                                   class="field w-24 text-center" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hinweis --}}
            <div class="rounded-xl bg-amber-50 border border-amber-300 p-3 text-sm text-amber-800">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                Die alten Lohn-Zahlungen werden storniert und neu berechnet.
            </div>

            <button type="submit" class="btn btn-primary w-full text-lg py-3">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Korrektur speichern
            </button>
        </form>
    </div>
</div>
@endsection
