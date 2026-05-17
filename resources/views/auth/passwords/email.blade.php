@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4">{{ __('Reset Password') }}</h2>
        @if (session('status'))
            <div class="rounded-2xl bg-emerald-50 border-2 border-emerald-200 text-emerald-800 p-4 mb-4">
                {{ session('status') }}
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="label">{{ __('Email Address') }}</label>
                <input id="email" type="email" class="field @error('email') border-rose-400 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn btn-primary w-full">{{ __('Send Password Reset Link') }}</button>
        </form>
    </div>
</div>
@endsection
