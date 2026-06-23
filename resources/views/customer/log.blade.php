@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="card">
        @include('customer._header')

        {{-- Summen --}}
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4 border-b-2 border-slate-100">
            <div class="rounded-2xl bg-emerald-50 p-4">
                <div class="text-sm text-emerald-700 font-semibold uppercase">Einzahlungen</div>
                <div class="text-2xl font-extrabold text-emerald-700">
                    {{ session('customer')->payments->where('comment', 'LIKE', 'Einzahlung')->sum('amount') }} Radi
                </div>
            </div>
            <div class="rounded-2xl bg-rose-50 p-4">
                <div class="text-sm text-rose-700 font-semibold uppercase">Auszahlungen</div>
                <div class="text-2xl font-extrabold text-rose-700">
                    {{ session('customer')->payments->where('comment', 'LIKE', 'Auszahlung')->sum('amount') }}
                </div>
            </div>
            <div class="rounded-2xl bg-amber-50 p-4">
                <div class="text-sm text-amber-700 font-semibold uppercase">Steuer</div>
                <div class="text-2xl font-extrabold text-amber-700">
                    {{ session('customer')->payments->where('comment', 'LIKE', )->sum('amount') }}
                </div>
            </div>
        </div>

        <div class="p-6 bg-yellow-50 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Buchungen --}}
            <div>
                <h3 class="text-2xl font-extrabold mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-brand-600"></i> Buchungen
                </h3>
                <ul class="divide-y divide-slate-200 bg-white rounded-2xl shadow-kid overflow-hidden">
                    @foreach($payments as $payment)
                        <li class="p-3 {{ $payment->amount > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm text-slate-500 w-32">{{ optional($payment->created_at)->format('d.m.Y H:i') }}</span>
                                <span class="flex-1 font-semibold">{{ $payment->comment }}</span>
                                <span class="font-extrabold w-24 text-right">{{ $payment->amount }} Radi</span>
                                <span class="text-sm text-slate-500 w-28 truncate">{{ optional($payment->banker)->name }}</span>
                                <span class="w-10 text-right">
                                    @if(auth()->user()->is_manager() and $payment->amount != 0 and $payment->comment != "Kredit")
                                        <form method="post" action="{{ url('payments/delete/'.$payment->id) }}" class="inline">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 p-2 rounded-lg hover:bg-rose-50"
                                                    title="Buchung löschen"
                                                    onclick="return confirm('Diese Buchung wirklich löschen?');">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </form>
                                    @endif
                                </span>
                            </div>
                        </li>
                    @endforeach
                    <li class="p-3">
                        {{ $payments->links() }}
                    </li>
                </ul>
            </div>

            {{-- Arbeitszeiten --}}
            <div>
                <h3 class="text-2xl font-extrabold mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-brand-600"></i> Arbeitszeiten
                </h3>
                <ul class="divide-y divide-slate-200 bg-white rounded-2xl shadow-kid overflow-hidden">
                    @foreach($working_times as $working_time)
                        <li class="p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm text-slate-600 flex-1">
                                    {{ optional($working_time->start)->format('d.m.Y H:i') }} – {{ optional($working_time->end)->format('d.m.Y H:i') }}
                                </span>
                                <span class="font-semibold w-32 truncate">{{ $working_time->buisness->name }}</span>
                                <span class="text-sm text-slate-500 w-24 truncate">{{ $working_time->user->name }}</span>
                                <span class="flex items-center gap-1 justify-end shrink-0">
                                    @if(auth()->user()->is_admin())
                                        <a href="{{ route('admin.arbeitszeiten.edit', [$customer, $working_time]) }}"
                                           class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50"
                                           title="Arbeitszeit bearbeiten">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    @endif

                                @if(auth()->user()->is_admin or auth()->user()->is_admin == 1)
                                        <form method="post" action="{{ url('working_times/delete/'.$working_time->id) }}" class="inline">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 p-2 rounded-lg hover:bg-rose-50"
                                                    title="Arbeitszeit löschen"
                                                    onclick="return confirm('Diese Arbeitszeit wirklich löschen?');">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Aktien-Transaktionen --}}
            @if($aktienTransaktionen->isNotEmpty())
            <div class="lg:col-span-2">
                <h3 class="text-2xl font-extrabold mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-brand-600"></i> Anteile (Käufe &amp; Verkäufe)
                </h3>
                <div class="bg-white rounded-2xl shadow-kid overflow-hidden">
                    <table class="min-w-full text-sm">
                        <thead class="bg-brand-50 text-brand-800 uppercase text-xs font-bold border-b-2 border-brand-100">
                            <tr>
                                <th class="px-4 py-3 text-left">Datum</th>
                                <th class="px-4 py-3 text-left">Betrieb</th>
                                <th class="px-4 py-3 text-center">Vorgang</th>
                                <th class="px-4 py-3 text-right">Stück</th>
                                <th class="px-4 py-3 text-right">Kurs</th>
                                <th class="px-4 py-3 text-right">Summe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($aktienTransaktionen as $tx)
                                @php $istKauf = in_array($tx->typ, ['kauf', 'rueckkauf_kind']); @endphp
                                <tr class="{{ $istKauf ? 'bg-emerald-50' : 'bg-rose-50' }}">
                                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                        {{ $tx->created_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">
                                        {{ $tx->betrieb->name ?? '–' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($istKauf)
                                            <span class="inline-block bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full text-xs">
                                                <i class="fa-solid fa-arrow-down mr-1"></i>Kauf
                                            </span>
                                        @else
                                            <span class="inline-block bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded-full text-xs">
                                                <i class="fa-solid fa-arrow-up mr-1"></i>Verkauf
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-700">{{ $tx->stueck }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600">{{ $tx->kurs }} Radi</td>
                                    <td class="px-4 py-3 text-right font-extrabold {{ $istKauf ? 'text-rose-700' : 'text-emerald-700' }}">
                                        {{ $istKauf ? '-' : '+' }}{{ $tx->summe }} Radi
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        @include('customer._back')
    </div>
</div>
@endsection
