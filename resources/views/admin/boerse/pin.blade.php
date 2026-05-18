@extends('layouts.app')
@section('content')
<div class="space-y-6">

    <div class="card p-6 space-y-4">
        <h2 class="text-2xl font-extrabold">🏢 Welcher Betrieb IST die Börse?</h2>
        <p class="text-slate-600">
            Wenn ein Betrieb als „Börse" markiert ist, kommen seine Mitarbeiter
            beim Anmelden mit ihrem Betriebs-PIN automatisch ins
            <b>Börsen-Frontend</b> (nicht in die normale Kasse).
        </p>

        @if($boerseBetrieb)
            <div class="rounded-xl border-2 border-amber-300 bg-amber-50 p-4">
                <div class="text-sm text-amber-700">Aktuell markiert als Börse:</div>
                <div class="text-2xl font-extrabold text-amber-800">{{ $boerseBetrieb->name }}</div>
                <div class="flex gap-3 mt-3 flex-wrap">
                    <a href="{{ route('admin.betriebe.boerse', $boerseBetrieb) }}" class="btn btn-ghost text-sm">
                        <i class="fa-solid fa-gear mr-1"></i> Einstellungen anzeigen
                    </a>
                    <form method="POST" action="/admin/boerse/markierung/entfernen"
                          onsubmit="return confirm('Markierung wirklich entfernen?')">
                        @csrf
                        <button class="btn bg-rose-100 text-rose-700">Markierung entfernen</button>
                    </form>
                </div>
            </div>
        @else
            <div class="text-slate-500">Aktuell ist <b>kein</b> Betrieb als Börse markiert.</div>
        @endif

        <p class="text-sm text-slate-500 pt-2">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Die Börse-Zuweisung kann auch direkt auf der Betrieb-Detailseite vorgenommen werden:
            <a href="{{ route('admin.betriebe.pin') }}" class="underline">Betriebe verwalten</a>
            → Betrieb auswählen → <strong>Börse</strong>-Button.
        </p>

        <form method="POST" action="/admin/boerse/markierung" class="space-y-3 pt-2 border-t border-slate-200">
            @csrf
            <div>
                <label class="label">Betrieb direkt hier markieren (muss bereits einen Betriebs-PIN haben)</label>
                <select name="customer_id" required class="field">
                    <option value="">— bitte wählen —</option>
                    @foreach($betriebe as $b)
                        <option value="{{ $b->id }}" {{ $boerseBetrieb && $boerseBetrieb->id == $b->id ? 'selected' : '' }}>
                            {{ $b->name }} {{ $b->is_boerse ? '(aktuelle Börse)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">Als Börse markieren</button>
        </form>
    </div>

    <div class="card p-6 space-y-4">
        <h2 class="text-2xl font-extrabold">🔑 Alternativer Börsen-PIN (ohne Betrieb)</h2>
        <p class="text-slate-600 text-sm">
            Wird verwendet, wenn die Mitarbeiter über <code>/boerse/login</code> direkt einsteigen.
            Bei markierter Börse oben loggen sich Mitarbeiter besser über <code>/betrieb/login</code> ein
            (mit dem Betriebs-PIN dieses Betriebs).
        </p>
        <p>Aktueller PIN: <code class="bg-slate-100 px-2 py-1 rounded">{{ $pin }}</code></p>

        <form method="POST" action="/admin/boerse/pin" class="space-y-3">
            @csrf
            <div>
                <label class="label">Neuer PIN (4–20 Zeichen)</label>
                <input type="text" name="pin" minlength="4" maxlength="20" required class="field w-60">
            </div>
            <button class="btn btn-primary">PIN setzen</button>
            <a href="/admin/boerse" class="btn">Zurück</a>
        </form>
    </div>
</div>
@endsection

