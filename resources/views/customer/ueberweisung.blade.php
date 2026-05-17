@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card">
        @include('customer._header')

        <div class="p-6 bg-sky-50">
            <form action="{{ url('ueberweisung') }}" method="post" class="space-y-5">
                @csrf
                <div>
                    <label for="buisness" class="label">An welchen Betrieb soll Geld geschickt werden?</label>
                    <select id="buisness" class="field" name="buisness" required>
                        <option disabled selected value="">– bitte wählen –</option>
                        @foreach($buisnesses as $buisness)
                            <option value="{{ $buisness->id }}">{{ $buisness->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="reason" class="label">Wofür wird das Geld überwiesen?</label>
                    <input id="reason" class="field" name="reason" type="text" required>
                </div>
                <div>
                    <label for="amount" class="label">Wieviel Radi sollen überwiesen werden?</label>
                    <input id="amount" type="number" step="1" min="1"
                           max="{{ session('customer')->balance }}"
                           class="field text-2xl" name="amount" required>
                </div>
                <button type="submit" class="btn btn-success w-full text-2xl py-5">
                    <i class="fa-solid fa-paper-plane"></i> Geld senden
                </button>
            </form>
        </div>

        @include('customer._back')
    </div>
</div>
@endsection
