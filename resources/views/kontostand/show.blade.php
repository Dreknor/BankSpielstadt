@extends('kontostand.layout')

@section('content')
<div class="bg-white/10 backdrop-blur-md rounded-3xl shadow-kid border-2 border-white/20 p-8 text-center">
    <h1 id="hinweis" class="text-4xl font-extrabold mb-2">
        Hallo {{ $user->name }},
    </h1>

    <div class="my-8">
        <div class="text-lg uppercase tracking-wider opacity-80">Aktueller Kontostand</div>
        <div class="text-6xl font-extrabold mt-2">{{ $user->balance }} <span class="text-3xl">Radi</span></div>
    </div>

    <div class="text-sm opacity-90 mb-2">Läuft ab in:</div>
    <div class="h-3 w-1/2 mx-auto rounded-full bg-white/20 overflow-hidden">
        <div id="progressbar" class="h-full bg-rose-400" style="width: 100%"></div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#progressbar').animate({ width: '0' }, {{ config('bank.kontostand.logout') * 1000 }}, 'linear', function () {
            window.location.href = "{{ route('kontostand') }}";
        });
    });
</script>
@endpush
