@extends('betrieb.layouts.app')

@section('content')
<div class="max-w-lg mx-auto card p-6">
    <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
        <i class="fa-solid fa-plus text-emerald-600"></i> Neues Produkt
    </h2>
    <form method="POST" action="{{ route('betrieb.produkte.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="label" for="name">Produktname</label>
            <input id="name" type="text" name="name" class="field" value="{{ old('name') }}" required autofocus>
        </div>
        <div>
            <label class="label" for="price">Preis in Radi</label>
            <input id="price" type="number" name="price" min="1" class="field text-2xl" value="{{ old('price') }}" required>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-success flex-1">
                <i class="fa-solid fa-floppy-disk"></i> Speichern
            </button>
            <a href="/betrieb/produkte" class="btn btn-ghost flex-1">Abbrechen</a>
        </div>
    </form>
</div>
@endsection

