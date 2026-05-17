@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Kunden-Kopfkarte --}}
    <div class="card">
        <div class="p-6 flex flex-col md:flex-row items-center gap-6 border-b-2 border-slate-100">
            <img src="{{ asset('storage/images/'.$customer->name.'.jpg') }}" alt=""
                 class="w-20 h-20 rounded-full object-cover bg-slate-100 ring-4 ring-brand-100"
                 onerror="this.style.display='none'">
            <div class="flex-1 text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-extrabold flex items-center gap-2 justify-center md:justify-start">
                    @if($customer->is_buisness())
                        <i class="fa-solid fa-store text-brand-600"></i>
                    @else
                        <i class="fa-solid fa-user text-brand-600"></i>
                    @endif
                    {{ $customer->name }}
                </h1>
            </div>
            <div class="text-center md:text-right">
                <div class="text-sm text-slate-500 uppercase font-semibold">Kontostand</div>
                <div class="text-3xl font-extrabold {{ $customer->balance > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $customer->balance }} Radi
                </div>
                @if($customer->kredit > 0)
                    <div class="text-rose-600 font-bold mt-1">Kredit: {{ $customer->kredit }} Radi</div>
                @endif
            </div>
        </div>

        {{-- Key fehlt (Kinderkonto) --}}
        @if(config('bank.key') and !$customer->is_buisness() and (!$customer->key or $customer->key = null ))
            <div class="p-6 bg-rose-50 border-b-2 border-rose-100">
                <div class="rounded-2xl bg-rose-100 text-rose-800 p-4 mb-4 font-bold text-lg">
                    <i class="fa-solid fa-key mr-2"></i>Bitte gib deine Key-Nummer ein
                </div>
                <form method="post" action="{{ url('set/key') }}" class="space-y-3">
                    @csrf
                    <label class="label" for="key">Key-Nummer</label>
                    <input id="key" class="field" name="key" type="text" autofocus>
                    <button type="submit" class="btn btn-success w-full md:w-auto">
                        <i class="fa-solid fa-floppy-disk"></i> speichern
                    </button>
                </form>
            </div>
        @endif

        {{-- Aktions-Kacheln --}}
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

            <a href="{{ url('einzahlen') }}" class="tile bg-emerald-100 hover:bg-emerald-200 text-emerald-900">
                <div>
                    <i class="fa-solid fa-arrow-down-to-bracket text-4xl mb-2"></i>
                    <div class="text-3xl">Einzahlen</div>
                </div>
            </a>

            @if($customer->balance > 0)
                <a href="{{ url('auszahlen') }}" class="tile bg-rose-100 hover:bg-rose-200 text-rose-900">
                    <div>
                        <i class="fa-solid fa-arrow-up-from-bracket text-4xl mb-2"></i>
                        <div class="text-3xl">Auszahlen</div>
                    </div>
                </a>
            @else
                <div class="tile bg-slate-100 text-slate-500 cursor-not-allowed">
                    <div>
                        <i class="fa-solid fa-ban text-3xl mb-2"></i>
                        <div class="text-xl">keine Auszahlung</div>
                    </div>
                </div>
            @endif

            @if(!$customer->is_buisness())
                <a href="{{ url('arbeitszeit') }}" class="tile bg-sky-100 hover:bg-sky-200 text-sky-900">
                    <div>
                        <i class="fa-solid fa-clock text-4xl mb-2"></i>
                        <div class="text-3xl">Arbeitszeit</div>
                    </div>
                </a>
            @elseif($customer->kredit > 0)
                <div class="tile bg-slate-100 text-slate-500 cursor-not-allowed">
                    <div>
                        <i class="fa-solid fa-ban text-3xl mb-2"></i>
                        <div class="text-xl">kein Kredit</div>
                    </div>
                </div>
            @else
                <a href="{{ url('kredit') }}" class="tile bg-amber-100 hover:bg-amber-200 text-amber-900">
                    <div>
                        <i class="fa-solid fa-hand-holding-dollar text-4xl mb-2"></i>
                        <div class="text-3xl">Kredit</div>
                    </div>
                </a>
            @endif

            <a href="{{ url('new/customer') }}" class="tile bg-slate-200 hover:bg-slate-300 text-slate-800 sm:col-span-2 min-h-[90px]">
                <div>
                    <i class="fa-solid fa-arrow-right-arrow-left text-2xl mb-1"></i>
                    <div class="text-2xl">anderer Kunde</div>
                </div>
            </a>

            <a href="{{ url('log') }}" class="tile bg-yellow-100 hover:bg-yellow-200 text-yellow-900 sm:col-span-2">
                <div>
                    <i class="fa-solid fa-list text-4xl mb-2"></i>
                    <div class="text-3xl">Log</div>
                </div>
            </a>

            @if(auth()->user()->is_admin and !$customer->is_buisness())
                <a href="{{ url('strafe') }}" class="tile bg-rose-200 hover:bg-rose-300 text-rose-900 sm:col-span-2">
                    <div>
                        <i class="fa-solid fa-gavel text-4xl mb-2"></i>
                        <div class="text-3xl">Strafe</div>
                    </div>
                </a>
            @endif

            @if($customer->is_buisness())
                <a href="{{ url('ueberweisung') }}" class="tile bg-sky-200 hover:bg-sky-300 text-sky-900 sm:col-span-2">
                    <div>
                        <i class="fa-solid fa-paper-plane text-4xl mb-2"></i>
                        <div class="text-3xl">Überweisung</div>
                    </div>
                </a>
            @endif


        </div>
    </div>

    {{-- Aktien-Portfolio --}}
    @if($portfolio->isNotEmpty())
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-brand-600"></i> Meine Anteile
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($portfolio as $pos)
                @php
                    $kurs      = $pos->betrieb->aktien_kurs ?? 0;
                    $gesamtwert = $pos->stueck * $kurs;
                @endphp
                <div class="rounded-2xl border-2 border-brand-100 bg-brand-50 p-4 flex flex-col gap-1">
                    <div class="font-extrabold text-lg text-brand-800 flex items-center gap-2">
                        <i class="fa-solid fa-building-columns"></i>
                        {{ $pos->betrieb->name }}
                    </div>
                    <div class="flex items-center justify-between text-slate-700">
                        <span class="text-sm">Anteile:</span>
                        <span class="font-bold text-brand-700">{{ $pos->stueck }} Stück</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-700">
                        <span class="text-sm">Kurs:</span>
                        <span class="font-semibold">{{ $kurs }} Radi</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-brand-200 pt-2 mt-1">
                        <span class="text-sm font-semibold">Gesamtwert:</span>
                        <span class="font-extrabold text-brand-700">{{ $gesamtwert }} Radi</span>
                    </div>
                </div>
            @endforeach
        </div>
        @php
            $portfolioGesamt = $portfolio->sum(fn($p) => $p->stueck * ($p->betrieb->aktien_kurs ?? 0));
        @endphp
        <div class="mt-4 rounded-2xl bg-brand-600 text-white p-4 flex items-center justify-between">
            <span class="font-bold text-lg">Gesamt-Portfoliowert:</span>
            <span class="font-extrabold text-2xl">{{ $portfolioGesamt }} Radi</span>
        </div>
    </div>
    @endif

    {{-- Bonus-Liste bei Betrieben --}}
    @if($customer->is_buisness() and $customer->bonus->count() > 0)
        <div class="card p-6">
            <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-gift text-brand-600"></i> Bonus
            </h2>
            <ul class="divide-y divide-slate-200">
                @foreach($customer->bonus as $bonus)
                    <li class="py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-1">
                        <span class="font-semibold">{{ $bonus->start }} – {{ $bonus->end }}</span>
                        <span class="text-slate-700">
                            <span class="font-bold">{{ $bonus->bonus }} Radi</span>
                            ({{ $bonus->bonus_type == 'hourly' ? 'je Stunde' : 'einmalig' }})
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
