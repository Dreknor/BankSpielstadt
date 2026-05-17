@extends('betrieb.layouts.app')

@section('content')
<div class="max-w-lg mx-auto card p-6">
    <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
        <i class="fa-solid fa-pen text-emerald-600"></i> Produkt bearbeiten
    </h2>
    <form method="POST" action="{{ route('betrieb.produkte.update', $product) }}" class="space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="label" for="name">Produktname</label>
            <input id="name" type="text" name="name" class="field" value="{{ old('name', $product->name) }}" required autofocus>
        </div>
        <div>
            <label class="label" for="price">Preis in Radi</label>
            <input id="price" type="number" name="price" min="1" class="field text-2xl" value="{{ old('price', $product->price) }}" required>
        </div>
        <div class="flex items-center gap-3">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" id="active" name="active" value="1" class="w-5 h-5 rounded"
                   {{ old('active', $product->active) ? 'checked' : '' }}>
            <label for="active" class="label mb-0">In der Kasse anzeigen (aktiv)</label>
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

