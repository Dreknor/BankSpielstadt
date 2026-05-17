@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card">
        @include('customer._header')

        <div class="p-6 bg-sky-50">
            <form action="{{ url('arbeitszeit') }}" method="post" class="space-y-5">
                @csrf
                <div>
                    <label for="day" class="label">An welchem Tag war die Arbeitszeit?</label>
                    <select id="day" class="field" name="day">
                        <option @if($day == 1) selected @endif value="1">Montag</option>
                        @if(\Carbon\Carbon::today()->dayOfWeek > 1)
                            <option @if($day == 2) selected @endif value="2">Dienstag</option>
                        @endif
                        @if(\Carbon\Carbon::today()->dayOfWeek > 2)
                            <option @if($day == 3) selected @endif value="3">Mittwoch</option>
                        @endif
                        @if(\Carbon\Carbon::today()->dayOfWeek > 3)
                            <option @if($day == 4) selected @endif value="4">Donnerstag</option>
                        @endif
                        @if(\Carbon\Carbon::today()->dayOfWeek > 4)
                            <option @if($day == 5) selected @endif value="5">Freitag</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="buisness" class="label">In welchem Betrieb wurde gearbeitet?</label>
                    <select id="buisness" class="field" name="buisness" required>
                        <option disabled selected value="">– bitte wählen –</option>
                        @foreach($buisnesses as $buisness)
                            <option value="{{ $buisness->id }}">{{ $buisness->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="manager" class="label">Ist der Kunde Chef in diesem Betrieb?</label>
                    <select id="manager" class="field" name="manager">
                        <option value="0">nein</option>
                        <option value="1">ja</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-2xl p-4 ring-1 ring-slate-100">
                        <h4 class="text-xl font-bold text-center mb-3">Anfangszeit</h4>
                        <div class="flex items-end justify-center gap-2">
                            <div>
                                <label class="label text-sm" for="start_hour">Stunde</label>
                                <input id="start_hour" type="number" min="8" max="13" name="start_hour" class="field w-24 text-center" required>
                            </div>
                            <div class="text-3xl font-extrabold pb-2">:</div>
                            <div>
                                <label class="label text-sm" for="start_minute">Minute</label>
                                <input id="start_minute" type="number" min="0" max="60" step="5" name="start_minute" class="field w-24 text-center" required>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 ring-1 ring-slate-100">
                        <h4 class="text-xl font-bold text-center mb-3">Endzeit</h4>
                        <div class="flex items-end justify-center gap-2">
                            <div>
                                <label class="label text-sm" for="end_hour">Stunde</label>
                                <input id="end_hour" type="number" min="8" max="13" name="end_hour" class="field w-24 text-center" required>
                            </div>
                            <div class="text-3xl font-extrabold pb-2">:</div>
                            <div>
                                <label class="label text-sm" for="end_minute">Minute</label>
                                <input id="end_minute" type="number" min="0" max="60" step="5" name="end_minute" class="field w-24 text-center" required>
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
