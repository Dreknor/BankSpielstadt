@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-user-plus text-brand-600"></i> {{ __('Register') }}
        </h2>
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="label">{{ __('Name') }}</label>
                <input id="name" type="text" class="field @error('name') border-rose-400 @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                @error('name') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="label">{{ __('Email Address') }}</label>
                <input id="email" type="email" class="field @error('email') border-rose-400 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
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
            <button type="submit" class="btn btn-primary w-full">{{ __('Register') }}</button>
        </form>
    </div>
</div>
@endsection
