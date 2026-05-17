@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card">
        @include('customer._header')

        <div class="p-6 bg-rose-50">
            <form action="{{ url('strafe') }}" method="post" class="space-y-5">
                @csrf
                <div>
                    <label for="amount" class="label">Wieviele Radi muss der Kunde Strafe zahlen?</label>
                    <input id="amount" type="number" step="1" min="1" class="field text-2xl" name="amount" autofocus>
                </div>
                <div>
                    <label for="comment" class="label">Wofür ist die Strafe?</label>
                    <input id="comment" type="text" class="field" name="comment">
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
