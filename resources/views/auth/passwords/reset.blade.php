@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4">{{ __('Reset Password') }}</h2>
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="label">{{ __('Email Address') }}</label>
                <input id="email" type="email" class="field @error('email') border-rose-400 @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                @error('email') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="label">{{ __('Password') }}</label>
                <input id="password" type="password" class="field @error('password') border-rose-400 @enderror" name="password" required autocomplete="new-password">
                @error('password') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password-confirm" class="label">{{ __('Confirm Password') }}</label>
                <input id="password-confirm" type="password" class="field" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary w-full">{{ __('Reset Password') }}</button>
        </form>
    </div>
</div>
@endsection
