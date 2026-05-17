@extends('kontostand.layout')

@section('content')
<div class="bg-white/10 backdrop-blur-md rounded-3xl shadow-kid border-2 border-white/20 p-8">
    <div class="text-center mb-6">
        <i class="fa-solid fa-piggy-bank text-6xl mb-3"></i>
        <h1 class="text-4xl font-extrabold">Kontostand</h1>
    </div>

    <h4 id="hinweis" class="text-xl text-center mb-4 font-semibold">
        Bitte Chip scannen
    </h4>

    <form action="{{ route('kontostand.read_key') }}" method="post" autocomplete="off">
        @csrf
        <input id="key_input" type="password" name="key"
               class="w-full px-5 py-4 text-2xl rounded-2xl text-slate-800 border-4 border-white/40 focus:border-white focus:ring-4 focus:ring-white/30 outline-none"
               autofocus autocomplete="off" aria-autocomplete="none" placeholder="Chip scannen">
    </form>

    @if ($errors->any())
        <div class="mt-4 rounded-2xl bg-rose-500/90 text-white p-4 font-semibold">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('form').submit(function() {
            $('#key_input').hide();
            $('#hinweis').text('Bitte warten...');
        });
    });
</script>
@endpush
