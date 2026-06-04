@extends('betrieb.layouts.app')

@section('content')
<div class="space-y-6">
    @if(session('betrieb')->id == 297)
        <div class="card p-5 bg-rose-50 border-rose-200 text-rose-800">
            <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Betrieb wird überwacht
            </h2>
            <p class="mb-3">Dieser Betrieb wird aufgrund von vorherigen Unregelmäßigkeiten genau beobachtet. Alle Aktionen werden protokolliert</p>
        </div>
    @endif

    {{-- Kassenbestand --}}
    <div class="card p-8 text-center">
        <div class="text-sm text-slate-500 uppercase font-bold mb-2">Aktueller Kassenbestand</div>
        <div class="text-7xl font-extrabold {{ $kassenbestand >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
            {{ $kassenbestand }} <span class="text-4xl">Radi</span>
        </div>
    </div>

    {{-- Schnell-Navigation --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('betrieb.kasse') }}"
           class="tile bg-emerald-100 hover:bg-emerald-200 text-emerald-900">
            <div>
                <i class="fa-solid fa-cash-register text-5xl mb-3"></i>
                <div class="text-3xl">Kasse</div>
                <div class="text-sm font-normal mt-1 opacity-70">Verkäufe buchen</div>
            </div>
        </a>

        <a href="{{ route('betrieb.produkte.index') }}"
           class="tile bg-sky-100 hover:bg-sky-200 text-sky-900">
            <div>
                <i class="fa-solid fa-box text-5xl mb-3"></i>
                <div class="text-3xl">Produkte</div>
                <div class="text-sm font-normal mt-1 opacity-70">{{ $produkte->count() }} aktiv</div>
            </div>
        </a>

        <a href="{{ route('betrieb.abrechnung') }}"
           class="tile bg-amber-100 hover:bg-amber-200 text-amber-900">
            <div>
                <i class="fa-solid fa-chart-bar text-5xl mb-3"></i>
                <div class="text-3xl">Abrechnung</div>
                <div class="text-sm font-normal mt-1 opacity-70">Tagesjournal & Druck</div>
            </div>
        </a>
    </div>

    {{-- Produktvorschau --}}
    @if($produkte->isNotEmpty())
        <div class="card p-5">
            <h3 class="text-xl font-extrabold mb-3 flex items-center gap-2">
                <i class="fa-solid fa-box text-emerald-600"></i> Aktive Produkte
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($produkte as $produkt)
                    <div class="rounded-2xl bg-slate-50 border-2 border-slate-200 p-3 text-center">
                        <div class="font-semibold">{{ $produkt->name }}</div>
                        <div class="text-emerald-700 font-extrabold text-lg">{{ $produkt->price }} Radi</div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="card p-6 text-center text-slate-500">
            <i class="fa-solid fa-box-open text-4xl mb-2"></i>
            <p class="mb-3">Noch keine Produkte angelegt.</p>
            <a href="{{ route('betrieb.produkte.create') }}" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Erstes Produkt anlegen
            </a>
        </div>
    @endif

</div>
@endsection

