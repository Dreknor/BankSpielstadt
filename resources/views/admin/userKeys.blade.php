@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-key text-brand-600"></i> {{ __('Key entfernen') }}
        </h2>
        <form action="{{ url('remove/key') }}" method="post" class="space-y-4">
            @csrf
            <div>
                <label for="search" class="label">Bitte Key eingeben</label>
                <input id="search" name="key" class="field" autofocus type="text" autocomplete="off">
            </div>
            <button class="btn btn-primary" type="submit">
                <i class="fa-solid fa-trash-can"></i> Key entfernen
            </button>
        </form>
    </div>

    <div class="card">
        <div class="px-5 py-4 border-b-2 border-slate-100 font-extrabold text-lg">
            <i class="fa-solid fa-list mr-2 text-brand-600"></i>Bewohner-Keys
        </div>
        <div class="overflow-x-auto p-2">
            <table class="min-w-full text-left" id="customerTable">
                <thead class="text-sm uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Key</th>
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
                            <td class="px-3 py-2 font-mono">{{ $customer->key }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
