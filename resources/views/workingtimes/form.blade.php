@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card">
        @include('customer._header')

        <div class="p-6 bg-sky-50">

            {{-- Fehler-Zusammenfassung --}}
            @if($errors->any())
            <div class="mb-4 rounded-xl border-2 border-red-400 bg-red-50 p-4">
                <p class="font-bold text-red-700 mb-1">❌ Bitte korrigiere die folgenden Fehler:</p>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ url('arbeitszeit') }}" method="post" class="space-y-5">
                @csrf

                {{-- Wochentag --}}
                <div>
                    <label for="day" class="label">An welchem Tag war die Arbeitszeit?</label>
                    <select id="day" class="field @error('day') border-red-500 ring-2 ring-red-300 @enderror" name="day">
                        <option @if(old('day', $day) == 1) selected @endif value="1">Montag</option>
                        @if(\Carbon\Carbon::today()->dayOfWeek > 1)
                            <option @if(old('day', $day) == 2) selected @endif value="2">Dienstag</option>
                        @endif
                        @if(\Carbon\Carbon::today()->dayOfWeek > 2)
                            <option @if(old('day', $day) == 3) selected @endif value="3">Mittwoch</option>
                        @endif
                        @if(\Carbon\Carbon::today()->dayOfWeek > 3)
                            <option @if(old('day', $day) == 4) selected @endif value="4">Donnerstag</option>
                        @endif
                        @if(\Carbon\Carbon::today()->dayOfWeek > 4)
                            <option @if(old('day', $day) == 5) selected @endif value="5">Freitag</option>
                        @endif
                    </select>
                    @error('day')
                        <p class="text-red-600 text-sm mt-1">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                {{-- Betrieb --}}
                <div>
                    <label for="buisness" class="label">In welchem Betrieb wurde gearbeitet?</label>
                    <select id="buisness" class="field @error('buisness') border-red-500 ring-2 ring-red-300 @enderror" name="buisness" required>
                        <option disabled @if(!old('buisness')) selected @endif value="">– bitte wählen –</option>
                        @foreach($buisnesses as $buisness)
                            <option value="{{ $buisness->id }}" @if(old('buisness') == $buisness->id) selected @endif>
                                {{ $buisness->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('buisness')
                        <p class="text-red-600 text-sm mt-1">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                {{-- Chef --}}
                <div>
                    <label for="manager" class="label">Ist der Kunde Chef in diesem Betrieb?</label>
                    <select id="manager" class="field @error('manager') border-red-500 ring-2 ring-red-300 @enderror" name="manager">
                        <option value="0" @if(old('manager', '0') == '0') selected @endif>nein</option>
                        <option value="1" @if(old('manager') == '1') selected @endif>ja</option>
                    </select>
                    @error('manager')
                        <p class="text-red-600 text-sm mt-1">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                {{-- Zeiten --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Anfangszeit --}}
                    <div class="bg-white rounded-2xl p-4 ring-1 @if($errors->hasAny(['start_hour','start_minute'])) ring-red-400 ring-2 @else ring-slate-100 @endif">
                        <h4 class="text-xl font-bold text-center mb-3 @if($errors->hasAny(['start_hour','start_minute'])) text-red-700 @endif">
                            @if($errors->hasAny(['start_hour','start_minute'])) ❌ @endif Anfangszeit
                        </h4>
                        <div class="flex items-end justify-center gap-2">
                            <div>
                                <label class="label text-sm" for="start_hour">Stunde</label>
                                <input id="start_hour" type="number" min="8" max="13"
                                       name="start_hour"
                                       value="{{ old('start_hour') }}"
                                       class="field w-24 text-center @error('start_hour') border-red-500 ring-2 ring-red-300 @enderror"
                                       required>
                                @error('start_hour')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="text-3xl font-extrabold pb-2">:</div>
                            <div>
                                <label class="label text-sm" for="start_minute">Minute</label>
                                <input id="start_minute" type="number" min="0" max="59" step="5"
                                       name="start_minute"
                                       value="{{ old('start_minute') }}"
                                       class="field w-24 text-center @error('start_minute') border-red-500 ring-2 ring-red-300 @enderror"
                                       required>
                                @error('start_minute')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Endzeit --}}
                    <div class="bg-white rounded-2xl p-4 ring-1 @if($errors->hasAny(['end_hour','end_minute'])) ring-red-400 ring-2 @else ring-slate-100 @endif">
                        <h4 class="text-xl font-bold text-center mb-3 @if($errors->hasAny(['end_hour','end_minute'])) text-red-700 @endif">
                            @if($errors->hasAny(['end_hour','end_minute'])) ❌ @endif Endzeit
                        </h4>
                        <div class="flex items-end justify-center gap-2">
                            <div>
                                <label class="label text-sm" for="end_hour">Stunde</label>
                                <input id="end_hour" type="number" min="8" max="13"
                                       name="end_hour"
                                       value="{{ old('end_hour') }}"
                                       class="field w-24 text-center @error('end_hour') border-red-500 ring-2 ring-red-300 @enderror"
                                       required>
                                @error('end_hour')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="text-3xl font-extrabold pb-2">:</div>
                            <div>
                                <label class="label text-sm" for="end_minute">Minute</label>
                                <input id="end_minute" type="number" min="0" max="59" step="5"
                                       name="end_minute"
                                       value="{{ old('end_minute') }}"
                                       class="field w-24 text-center @error('end_minute') border-red-500 ring-2 ring-red-300 @enderror"
                                       required>
                                @error('end_minute')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-full text-2xl py-5">
                    <i class="fa-solid fa-floppy-disk"></i> speichern
                </button>
            </form>
        </div>

        @include('customer._back')
    </div>
</div>
@endsection
