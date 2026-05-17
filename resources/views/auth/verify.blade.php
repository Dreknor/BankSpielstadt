@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4">{{ __('Verify Your Email Address') }}</h2>
        @if (session('resent'))
            <div class="rounded-2xl bg-emerald-50 border-2 border-emerald-200 text-emerald-800 p-4 mb-4">
                {{ __('A fresh verification link has been sent to your email address.') }}
            </div>
        @endif
        <p class="mb-3">{{ __('Before proceeding, please check your email for a verification link.') }}</p>
        <p>{{ __('If you did not receive the email') }},
            <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="text-brand-600 hover:underline font-semibold">{{ __('click here to request another') }}</button>.
            </form>
        </p>
    </div>
</div>
@endsection
