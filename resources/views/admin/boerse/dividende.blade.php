@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-4">
    <h2 class="text-2xl font-extrabold">🎉 Dividende ausschütten</h2>
    <p class="text-slate-600">
        Pro Anteilsinhaber werden 2 verknüpfte <b>Payment</b>-Zeilen erzeugt
        (Betriebskonto → Kinderkonto). Reicht das Betriebskonto nicht, wird der
        Dividendenbetrag automatisch anteilig gekürzt.
    </p>

    <form method="POST" action="/admin/boerse/dividende" class="space-y-3">
        @csrf
        <div>
            <label class="label">Betrieb</label>
            <select name="buisness_id" required class="field">
                <option value="">— bitte wählen —</option>
                @foreach($betriebe as $b)
                    <option value="{{ $b->id }}">{{ $b->name }} (Kontostand: {{ $b->balance }} Radi · {{ $b->anteileVerkauft() }} Anteile verkauft)</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Dividende pro Anteil (Radi)</label>
            <input type="number" name="radi_pro_anteil" min="1" required class="field w-40">
        </div>
        <button class="btn btn-primary">Dividende auszahlen</button>
        <a href="/admin/boerse" class="btn">Abbrechen</a>
    </form>
</div>
@endsection

