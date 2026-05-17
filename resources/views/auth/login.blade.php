@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    {{-- Betrieb-Login (prominent oben) --}}
    <div class="card overflow-hidden">
        <div class="bg-emerald-600 text-white px-6 py-4 flex items-center gap-3">
            <i class="fa-solid fa-store text-2xl"></i>
            <div>
                <div class="font-extrabold text-xl">Ich bin ein Betrieb</div>
                <div class="text-sm text-emerald-100">Kasse, Produkte & Abrechnung</div>
            </div>
        </div>
        <div class="p-6">
            <a href="{{ route('betrieb.login') }}" class="btn btn-success w-full text-xl py-4">
                <i class="fa-solid fa-cash-register"></i> Zur Betriebs-Kasse
            </a>
        </div>
    </div>

    {{-- Trennlinie --}}
    <div class="flex items-center gap-3 text-slate-400">
        <div class="flex-1 h-px bg-slate-200"></div>
        <span class="text-sm font-semibold">oder</span>
        <div class="flex-1 h-px bg-slate-200"></div>
    </div>

    {{-- Bank-Banker-Login --}}
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-piggy-bank text-brand-600"></i> Ich bin ein Banker
        </h2>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="label">{{ __('Email Address') }}</label>
                <input id="email" type="email" class="field @error('email') border-rose-400 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="label">{{ __('Password') }}</label>
                <input id="password" type="password" class="field @error('password') border-rose-400 @enderror" name="password" required autocomplete="current-password">
                @error('password') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="w-5 h-5 rounded" {{ old('remember') ? 'checked' : '' }}>
                <span>{{ __('Remember Me') }}</span>
            </label>
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn btn-primary">{{ __('Login') }}</button>
                @if (Route::has('password.request'))
                    <a class="text-brand-600 hover:underline font-semibold" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

</div>
@endsection
