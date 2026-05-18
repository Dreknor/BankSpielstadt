@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Kopfzeile --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-extrabold flex items-center gap-2">
            <i class="fa-solid fa-scroll text-brand-600"></i>
            Log-Protokoll
            <span class="text-sm font-normal text-slate-400">{{ $logDateiName }}</span>
        </h1>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Filter --}}
            <form method="GET" action="{{ route('admin.logs') }}" class="flex gap-1">
                @foreach(['all' => 'Alle', 'error' => 'Fehler', 'warning' => 'Warnungen', 'info' => 'Info', 'debug' => 'Debug'] as $wert => $label)
                    <button type="submit" name="filter" value="{{ $wert }}"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border
                                   {{ $filter === $wert ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </form>

            {{-- Log leeren --}}
            <form method="POST" action="{{ route('admin.logs.leeren') }}"
                  onsubmit="return confirm('Log-Datei wirklich leeren? Alle Einträge werden gelöscht!')">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 border border-rose-200">
                    <i class="fa-solid fa-trash mr-1"></i>Log leeren
                </button>
            </form>
        </div>
    </div>

    {{-- Statistik --}}
    <p class="text-sm text-slate-500">
        {{ $gesamt }} {{ $gesamt === 1 ? 'Eintrag' : 'Einträge' }} gefunden
        @if($seiten > 1) – Seite {{ $seite }} von {{ $seiten }} @endif
    </p>

    {{-- Einträge --}}
    @forelse($eintraege as $eintrag)
        @php
            $farben = [
                'error'     => 'border-rose-400 bg-rose-50 text-rose-900',
                'critical'  => 'border-rose-600 bg-rose-100 text-rose-900',
                'alert'     => 'border-rose-600 bg-rose-100 text-rose-900',
                'emergency' => 'border-rose-800 bg-rose-200 text-rose-900',
                'warning'   => 'border-amber-400 bg-amber-50 text-amber-900',
                'notice'    => 'border-sky-400 bg-sky-50 text-sky-900',
                'info'      => 'border-sky-300 bg-sky-50 text-sky-800',
                'debug'     => 'border-slate-300 bg-slate-50 text-slate-700',
            ];
            $cls = $farben[$eintrag['level']] ?? 'border-slate-300 bg-white text-slate-700';
        @endphp
        <div class="rounded-xl border-l-4 p-3 {{ $cls }} font-mono text-xs space-y-1">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="font-semibold uppercase tracking-widest opacity-70 text-xs">{{ $eintrag['level'] }}</span>
                <span class="opacity-60">{{ $eintrag['zeitpunkt'] }}</span>
            </div>
            <pre class="whitespace-pre-wrap break-all leading-relaxed">{{ $eintrag['nachricht'] }}</pre>
        </div>
    @empty
        <div class="card p-10 text-center text-slate-400">
            <i class="fa-solid fa-circle-check text-4xl text-emerald-300 mb-3"></i>
            <p class="font-semibold">Keine Einträge vorhanden.</p>
        </div>
    @endforelse

    {{-- Paginierung --}}
    @if($seiten > 1)
        <div class="flex items-center justify-center gap-2 flex-wrap pt-2">
            @for($i = 1; $i <= $seiten; $i++)
                <a href="{{ route('admin.logs', ['filter' => $filter, 'seite' => $i]) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg
                          {{ $seite === $i ? 'bg-brand-600 text-white' : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                    {{ $i }}
                </a>
            @endfor
        </div>
    @endif
</div>
@endsection

