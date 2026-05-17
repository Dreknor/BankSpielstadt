@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-4">
    <h2 class="text-2xl font-extrabold">Kurs korrigieren – {{ $customer->name }}</h2>
    <p>Aktueller Kurs: <b>{{ $customer->aktien_kurs }} Radi</b></p>

    <form method="POST" action="/admin/boerse/{{ $customer->id }}/kurs" class="space-y-3">
        @csrf
        <div>
            <label class="label">Neuer Kurs (Radi)</label>
            <input type="number" name="kurs" min="1" required value="{{ $customer->aktien_kurs }}" class="field w-40">
        </div>
        <div>
            <label class="label">Grund (für Kursverlauf)</label>
            <input type="text" name="grund" required maxlength="120" class="field" placeholder="z. B. Sonderpreis durch Lehrer">
        </div>
        <div class="flex gap-2">
            <button class="btn btn-primary">Kurs setzen</button>
            <a href="/admin/boerse" class="btn">Abbrechen</a>
        </div>
    </form>

    <hr>
    <h3 class="text-lg font-bold">Letzte 10 Änderungen</h3>
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500"><tr>
            <th>Zeit</th><th>Vorher</th><th>Neu</th><th>Grund</th>
        </tr></thead>
        <tbody>
        @foreach($kursverlauf as $k)
            <tr class="border-t border-slate-100">
                <td>{{ $k->created_at?->format('d.m. H:i') }}</td>
                <td>{{ $k->vorher }}</td>
                <td><b>{{ $k->kurs }}</b></td>
                <td>{{ $k->grund }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

