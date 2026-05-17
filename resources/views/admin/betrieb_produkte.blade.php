@extends('layouts.app')

@section('content')
<div class="mb-4 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-2xl font-extrabold flex items-center gap-2">
        <i class="fa-solid fa-box text-brand-600"></i> Produkte: {{ $betrieb->name }}
    </h2>
    <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost py-2">← Zurück</a>
</div>

<div class="card">
    <div class="p-5">
        @if($produkte->isEmpty())
            <p class="text-slate-500 text-center py-6">Keine Produkte angelegt.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="text-sm uppercase text-slate-500 border-b-2 border-slate-200">
                        <tr>
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Preis</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($produkte as $p)
                            <tr class="{{ $p->trashed() ? 'opacity-40' : '' }}">
                                <td class="px-3 py-3 font-semibold">{{ $p->name }}</td>
                                <td class="px-3 py-3 font-bold text-emerald-700">{{ $p->price }} Radi</td>
                                <td class="px-3 py-3">
                                    @if($p->trashed())
                                        <span class="bg-slate-200 text-slate-600 rounded-xl px-3 py-1 text-sm font-semibold">gelöscht</span>
                                    @elseif($p->active)
                                        <span class="bg-emerald-100 text-emerald-700 rounded-xl px-3 py-1 text-sm font-semibold">aktiv</span>
                                    @else
                                        <span class="bg-amber-100 text-amber-700 rounded-xl px-3 py-1 text-sm font-semibold">inaktiv</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

