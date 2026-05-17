@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Statistik-Karten --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card p-5 border-l-8 border-brand-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-brand-600 uppercase">Kunden</div>
                    <div class="text-3xl font-extrabold mt-1">{{ $customer_count }}</div>
                </div>
                <i class="fa-solid fa-users text-3xl text-brand-200"></i>
            </div>
        </div>
        <div class="card p-5 border-l-8 border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-emerald-600 uppercase">Betriebe</div>
                    <div class="text-3xl font-extrabold mt-1">{{ $buisness_count }}</div>
                </div>
                <i class="fa-solid fa-building text-3xl text-emerald-200"></i>
            </div>
        </div>
        <div class="card p-5 border-l-8 border-sky-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-sky-600 uppercase">Arbeitszeiten</div>
                    <div class="text-xl font-extrabold mt-1">
                        {{ $working_times_today_count }} <span class="text-sm text-slate-500">(heute)</span>
                    </div>
                    <div class="text-xl font-extrabold">
                        {{ $working_times }} <span class="text-sm text-slate-500">(gesamt)</span>
                    </div>
                </div>
                <i class="fa-solid fa-clipboard-list text-3xl text-sky-200"></i>
            </div>
        </div>
        <div class="card p-5 border-l-8 border-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-amber-600 uppercase">Lohnzahlungen (heute)</div>
                    <div class="text-3xl font-extrabold mt-1">{{ $lohn }} Radi</div>
                </div>
                <i class="fa-solid fa-hand-holding-dollar text-3xl text-amber-200"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Betriebe --}}
        <div class="card">
            <div class="px-5 py-4 border-b-2 border-slate-100 font-extrabold text-lg">
                <i class="fa-solid fa-building mr-2 text-emerald-600"></i>Kontostand Betriebe
            </div>
            <div class="overflow-x-auto p-2">
                <table class="min-w-full text-left">
                    <thead class="text-sm uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Kontostand</th>
                            <th class="px-3 py-2">heute</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($buisnesses as $buisness)
                            <tr class="{{ $buisness->balance < abs($buisness->daily_balance()) ? 'text-rose-600' : '' }}">
                                <td class="px-3 py-2">
                                    <a href="{{ url('choose/customer/'.$buisness->id) }}" class="font-semibold text-brand-600 hover:underline">
                                        {{ $buisness->name }}
                                    </a>
                                </td>
                                <td class="px-3 py-2 font-bold">{{ $buisness->balance }}</td>
                                <td class="px-3 py-2">{{ $buisness->daily_balance() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Bewohner --}}
        <div class="card">
            <div class="px-5 py-4 border-b-2 border-slate-100 font-extrabold text-lg">
                <i class="fa-solid fa-users mr-2 text-brand-600"></i>Kontostand Bewohner
            </div>
            <div class="overflow-x-auto p-2">
                <table class="min-w-full text-left" id="customerTable">
                    <thead class="text-sm uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Kontostand</th>
                            <th class="px-3 py-2">Arbeitszeiten</th>
                            <th class="px-3 py-2">Arbeitszeit (min)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($customers as $customer)
                            <tr>
                                <td class="px-3 py-2">
                                    <a href="{{ url('choose/customer/'.$customer->id) }}" class="font-semibold text-brand-600 hover:underline">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td class="px-3 py-2 font-bold">{{ $customer->balance }}</td>
                                <td class="px-3 py-2">{{ $customer->working_times->count() }}</td>
                                <td class="px-3 py-2">
                                    @if($customer->working_times->count() > 0)
                                        {{ $customer->working_times->sum('duration') }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Aktionen --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <a href="{{ url('start') }}" class="card p-5 hover:shadow-lg transition text-center font-bold text-brand-700">
            <i class="fa-solid fa-coins block text-2xl mb-2"></i>Startkapital verteilen
        </a>
        <a href="{{ url('gebuehr') }}" class="card p-5 hover:shadow-lg transition text-center font-bold text-brand-700">
            <i class="fa-solid fa-percent block text-2xl mb-2"></i>Kontoführungsgebühr und Zinsen kassieren
        </a>
        <a href="{{ url('export') }}" class="card p-5 hover:shadow-lg transition text-center font-bold text-brand-700">
            <i class="fa-solid fa-file-export block text-2xl mb-2"></i>Export
        </a>
        <a href="{{ url('deleteStart') }}" class="card p-5 hover:shadow-lg transition text-center font-bold text-rose-700">
            <i class="fa-solid fa-eraser block text-2xl mb-2"></i>Startgeld löschen
        </a>
        <a href="{{ route('admin.betriebe.pin') }}" class="card p-5 hover:shadow-lg transition text-center font-bold text-emerald-700">
            <i class="fa-solid fa-store block text-2xl mb-2"></i>Betriebs-PINs & Kassen
        </a>
    </div>
</div>
@endsection

@push('css')
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.7/datatables.min.css" rel="stylesheet">
@endpush

@push('js')
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.7/datatables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script>
        $('#customerTable').DataTable({
            layout: { top: { buttons: ['copy', 'csv', 'excel', 'pdf', 'print'] } },
        });
    </script>
@endpush
