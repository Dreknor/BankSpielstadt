@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-4">
    <h2 class="text-2xl font-extrabold">🏁 Schlussabrechnung — Spielende</h2>
    <p class="text-slate-600">
        Beim Klick auf „Jetzt abrechnen" werden <b>alle Anteile</b> in Bargeld umgewandelt.
        Der Auszahlungspreis richtet sich nach dem <b>Einkaufspreis</b>: War der Einkaufspreis
        unter dem Mindest-Aktienpreis, wird nur der Einkaufspreis ausgezahlt —
        andernfalls gilt der aktuelle Kurs.
    </p>

    <div class="grid grid-cols-2 gap-4">
        <div class="rounded-xl border-2 p-4 bg-slate-50">
            <div class="text-sm text-slate-500">Aktueller Bargeldbestand Börse</div>
            <div class="text-3xl font-extrabold">{{ $kassenstand }} Radi</div>
        </div>
        <div class="rounded-xl border-2 p-4 {{ $kassenstand >= $gesamtAuszahlung ? 'bg-emerald-50 border-emerald-300' : 'bg-rose-50 border-rose-400' }}">
            <div class="text-sm text-slate-500">Voraussichtlich auszuzahlen</div>
            <div class="text-3xl font-extrabold">{{ $gesamtAuszahlung }} Radi</div>
        </div>
    </div>

    @if($kassenstand < $gesamtAuszahlung)
        <div class="rounded-xl bg-rose-100 border-2 border-rose-400 text-rose-900 p-4 font-bold">
            ⚠️ Die Börsen-Kasse hat nicht genug Geld. Bitte zuerst {{ $gesamtAuszahlung - $kassenstand }} Radi einlegen (über den Kassenwart).
        </div>
    @endif

    <form method="POST" action="/admin/boerse/abschluss"
          onsubmit="return confirm('Wirklich alle Anteile zum aktuellen Kurs auflösen? Das kann nicht rückgängig gemacht werden.')">
        @csrf
        <button class="btn btn-primary" {{ $kassenstand < $gesamtAuszahlung ? 'disabled' : '' }}>
            ✅ Jetzt abrechnen
        </button>
        <a href="/admin/boerse" class="btn">Abbrechen</a>
    </form>
</div>
@endsection

