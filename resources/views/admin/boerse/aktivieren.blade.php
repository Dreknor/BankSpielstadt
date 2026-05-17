@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-4">
    <h2 class="text-2xl font-extrabold">➕ Betrieb für die Börse freischalten</h2>

    @if($betriebe->isEmpty())
        <p class="text-slate-500">Alle Betriebe sind bereits an der Börse aktiv.</p>
    @else
        <form method="POST" action="/admin/boerse/aktivieren" class="space-y-4">
            @csrf
            <div>
                <label class="label">Betrieb</label>
                <select name="customer_id" required class="field">
                    <option value="">— bitte wählen —</option>
                    @foreach($betriebe as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Gesamtstückzahl (Default {{ config('bank.aktien.standard_gesamt') }})</label>
                    <input type="number" name="aktien_gesamt" min="1" max="1000"
                           value="{{ old('aktien_gesamt', config('bank.aktien.standard_gesamt')) }}" required class="field">
                </div>
                <div>
                    <label class="label">Startkurs (Radi)</label>
                    <input type="number" name="aktien_kurs" min="1" value="{{ old('aktien_kurs', 10) }}" required class="field">
                </div>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-primary">✅ Freischalten</button>
                <a href="/admin/boerse" class="btn">Abbrechen</a>
            </div>
        </form>
    @endif
</div>
@endsection

