@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 {{ $warnung ? 'border-rose-400' : 'border-amber-200' }}">
    <h1 class="text-3xl font-extrabold text-amber-700">💰 Börsen-Kasse</h1>
    <div class="mt-3 text-center">
        <div class="text-sm text-slate-500">Aktueller Bargeldbestand</div>
        <div class="text-6xl font-extrabold {{ $warnung ? 'text-rose-600' : 'text-emerald-600' }}">{{ $kassenstand }} Radi</div>
        @if($warnung)
            <div class="mt-2 bg-rose-100 border-2 border-rose-400 text-rose-900 rounded-xl p-3 font-bold">
                ⚠️ Wenig Bargeld! Bitte Lehrkraft informieren.
            </div>
        @endif
    </div>

    <form method="POST" action="/boerse/kasse/bestaetigen" class="mt-4">
        @csrf
        <div class="mb-3">
            <label class="block text-sm font-semibold text-slate-600 mb-1 text-center">
                Dein Name (wer bestätigt?)
            </label>
            <input type="text" name="mitarbeiter" required maxlength="80"
                   placeholder="z. B. Lena"
                   value="{{ old('mitarbeiter') }}"
                   class="w-full text-xl text-center border-2 {{ $errors->has('mitarbeiter') ? 'border-rose-400' : 'border-slate-300' }} rounded-xl px-4 py-3 focus:outline-none focus:border-amber-400">
            @error('mitarbeiter')
                <p class="text-rose-600 text-sm text-center mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>
        <div class="text-center">
            <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-xl font-bold px-8 py-4 rounded-xl shadow-kid">
                ✅ Kassenstand bestätigen
            </button>
            <div class="text-sm text-slate-500 mt-1">Bitte stündlich klicken!</div>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-emerald-200">
        <h2 class="text-xl font-bold text-emerald-700 mb-3">➕ Einlage erfassen</h2>
        <form method="POST" action="/boerse/kasse/einlage" class="space-y-3">
            @csrf
            <input type="number" name="betrag" min="1" required placeholder="Radi"
                   class="w-full text-2xl text-center border-2 border-slate-300 rounded-xl px-3 py-2">
            <input type="text" name="notiz" maxlength="120" placeholder="Notiz (optional)"
                   class="w-full border-2 border-slate-300 rounded-xl px-3 py-2">
            <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl">Einlage buchen</button>
        </form>
    </div>
    <div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-rose-200">
        <h2 class="text-xl font-bold text-rose-700 mb-3">➖ Entnahme erfassen</h2>
        <form method="POST" action="/boerse/kasse/entnahme" class="space-y-3">
            @csrf
            <input type="number" name="betrag" min="1" required placeholder="Radi"
                   class="w-full text-2xl text-center border-2 border-slate-300 rounded-xl px-3 py-2">
            <input type="text" name="notiz" maxlength="120" placeholder="Grund"
                   class="w-full border-2 border-slate-300 rounded-xl px-3 py-2">
            <button class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3 rounded-xl">Entnahme buchen</button>
        </form>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-amber-200">
    <h2 class="text-xl font-bold text-amber-700 mb-3">📜 Letzte Bewegungen</h2>
    @if($transaktionen->isEmpty())
        <p class="text-slate-500">Noch nichts gebucht.</p>
    @else
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500"><tr>
                <th>Zeit</th><th>Typ</th><th>Betrag</th><th>Notiz</th>
            </tr></thead>
            <tbody>
                @foreach($transaktionen as $t)
                    @php
                        $minus = in_array($t->typ, ['verkauf_auszahlung','rueckkauf_auszahlung','entnahme','abschluss_auszahlung']);
                    @endphp
                    <tr class="border-t border-amber-100">
                        <td class="py-1">{{ $t->created_at?->format('H:i') }}</td>
                        <td>{{ $t->typ }}</td>
                        <td class="font-bold {{ $minus ? 'text-rose-600' : 'text-emerald-600' }}">{{ $minus ? '-' : '+' }}{{ $t->betrag }}</td>
                        <td class="text-slate-600">{{ $t->notiz }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

