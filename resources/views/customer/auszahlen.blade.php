@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card">
        @include('customer._header')

        <div class="p-6 bg-rose-50">
            <form action="{{ url('auszahlen') }}" method="post" class="space-y-5">
                @csrf
                <div>
                    <label for="amount" class="label">Wieviele Radi möchte der Kunde abheben?</label>
                    <input id="amount" type="number" step="1" min="1"
                           max="{{ session('customer')->balance }}"
                           class="field text-2xl" name="amount" autofocus>
                </div>
                <button type="submit" class="btn btn-danger w-full text-2xl py-5">
                    <i class="fa-solid fa-floppy-disk"></i> speichern
                </button>
            </form>
        </div>

        @include('customer._back')
    </div>
</div>
@endsection
