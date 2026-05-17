@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card">
        @include('customer._header')

        <div class="p-6 bg-amber-50">
            <form action="{{ url('kredit') }}" method="post" class="space-y-5">
                @csrf
                <div>
                    <label for="kredit" class="label">Wieviel Kredit soll gegeben werden?</label>
                    <input id="kredit" type="number" step="0.5" min="1"
                           max="{{ session('customer')->startkapital * 1.5 }}"
                           class="field text-2xl" name="kredit" autofocus>
                </div>
                <button type="submit" class="btn btn-warning w-full text-2xl py-5">
                    <i class="fa-solid fa-floppy-disk"></i> speichern
                </button>
            </form>
        </div>

        @include('customer._back')
    </div>
</div>
@endsection
