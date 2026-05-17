@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="p-6 border-b-2 border-slate-100">
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-brand-600"></i>
                {{ __('Kunde erstellen') }}
            </h2>
        </div>
        <div class="p-6">
            @if (session('status'))
                <div class="rounded-2xl bg-emerald-50 border-2 border-emerald-200 text-emerald-800 p-4 mb-4">
                    {{ session('status') }}
                </div>
            @endif
            <form autocomplete="off" method="post" action="{{ url('customer/store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="name">Name</label>
                    <input id="name" class="field" name="name" type="text" autofocus autocomplete="off">
                </div>
                <div>
                    <label class="label" for="buisness">Ist ein Betrieb?</label>
                    <select id="buisness" class="field" name="buisness">
                        <option value="0">nein</option>
                        <option value="1">ja</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="startkapital">Startkapital</label>
                    <input id="startkapital" class="field" name="startkapital" type="number" min="0">
                </div>
                <div>
                    <label class="label" for="key">Key-Nummer</label>
                    <input id="key" class="field" name="key" type="text">
                </div>
                <button type="submit" class="btn btn-success w-full text-xl">
                    <i class="fa-solid fa-floppy-disk"></i> speichern
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

