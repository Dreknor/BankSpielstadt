@extends('betrieb.layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card p-8">
        <div class="text-center mb-6">
            <i class="fa-solid fa-store text-6xl text-emerald-600 mb-3"></i>
            <h1 class="text-3xl font-extrabold">Betriebs-Kasse</h1>
            <p class="text-slate-500 mt-1">Melde dich mit dem PIN deines Betriebs an</p>
        </div>

        <form method="POST" action="{{ route('betrieb.login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="betrieb_id" class="label">Betrieb auswählen</label>
                <select id="betrieb_id" name="betrieb_id" class="field" required>
                    <option value="" disabled selected>– bitte wählen –</option>
                    @foreach($betriebe as $b)
                        <option value="{{ $b->id }}" {{ old('betrieb_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->is_boerse ? '📈 ' : '' }}{{ $b->name }}{{ $b->is_boerse ? ' (Börse)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="pin" class="label">PIN</label>
                <input id="pin" type="password" name="pin" class="field text-2xl tracking-widest text-center" autofocus autocomplete="off" placeholder="••••">
            </div>
            <button type="submit" class="btn btn-success w-full text-xl py-4">
                <i class="fa-solid fa-right-to-bracket"></i> Anmelden
            </button>
        </form>
        <div class="mt-4 pt-4 border-t-2 border-slate-100 text-center">
            <a href="{{ route('login') }}" class="text-slate-500 hover:text-brand-600 font-semibold text-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> Ich bin ein Banker – zum Bank-Login
            </a>
        </div>
    </div>
</div>
@endsection


