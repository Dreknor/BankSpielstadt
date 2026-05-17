@extends('betrieb.layouts.app')

@section('content')
<div class="card">
    <div class="p-5 border-b-2 border-slate-100 flex items-center justify-between">
        <h2 class="text-2xl font-extrabold flex items-center gap-2">
            <i class="fa-solid fa-box text-emerald-600"></i> Produkte
        </h2>
        <a href="/betrieb/produkte/erstellen" class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Neues Produkt
        </a>
    </div>
    <div class="p-5">
        @if($produkte->isEmpty())
            <p class="text-slate-500 text-center py-8">Noch keine Produkte vorhanden.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="text-sm uppercase text-slate-500 border-b-2 border-slate-200">
                        <tr>
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Preis</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($produkte as $produkt)
                            <tr class="{{ $produkt->trashed() ? 'opacity-40' : '' }}">
                                <td class="px-3 py-3 font-semibold text-lg">{{ $produkt->name }}</td>
                                <td class="px-3 py-3 font-extrabold text-emerald-700">{{ $produkt->price }} Radi</td>
                                <td class="px-3 py-3">
                                    @if($produkt->trashed())
                                        <span class="bg-slate-200 text-slate-600 rounded-xl px-3 py-1 text-sm font-semibold">gelöscht</span>
                                    @elseif($produkt->active)
                                        <span class="bg-emerald-100 text-emerald-700 rounded-xl px-3 py-1 text-sm font-semibold">aktiv</span>
                                    @else
                                        <span class="bg-amber-100 text-amber-700 rounded-xl px-3 py-1 text-sm font-semibold">inaktiv</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 flex gap-2 justify-end">
                                    @if(!$produkt->trashed())
                                        <a href="/betrieb/produkte/{{ $produkt->id }}/bearbeiten" class="btn btn-ghost text-sm py-2 px-3">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="POST" action="/betrieb/produkte/{{ $produkt->id }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger text-sm py-2 px-3"
                                                onclick="return confirm('Produkt wirklich löschen?')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
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

