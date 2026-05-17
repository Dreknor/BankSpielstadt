@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4">{{ __('Confirm Password') }}</h2>
        <p class="mb-4">{{ __('Please confirm your password before continuing.') }}</p>
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf
            <div>
                <label for="password" class="label">{{ __('Password') }}</label>
                <input id="password" type="password" class="field @error('password') border-rose-400 @enderror" name="password" required autocomplete="current-password">
                @error('password') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="btn btn-primary">{{ __('Confirm Password') }}</button>
                @if (Route::has('password.request'))
                    <a class="text-brand-600 hover:underline font-semibold" href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
