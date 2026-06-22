@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-2xl font-extrabold">⛔ Handelssperren verwalten</h2>
        <a href="/admin/boerse" class="btn">🔙 Zurück zur Börse</a>
    </div>

    <p class="text-slate-600 text-sm">
        Gesperrte Kinder können keine Anteile kaufen oder verkaufen.
        Eine Sperre kann jederzeit wieder aufgehoben werden.
    </p>

    @php
        $gesperrt   = $kinder->where('boerse_handel_gesperrt', true);
        $erlaubt    = $kinder->where('boerse_handel_gesperrt', false)->where('boerse_handel_gesperrt', '!=', null);
    @endphp

    @if($gesperrt->isNotEmpty())
    <div class="rounded-xl border-2 border-rose-200 bg-rose-50 p-4">
        <h3 class="font-bold text-rose-800 mb-3">⛔ Aktuell gesperrte Kinder ({{ $gesperrt->count() }})</h3>
        <table class="w-full text-sm">
            <thead class="text-left text-rose-700">
                <tr>
                    <th class="py-2 pr-4">Name</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($gesperrt->sortBy('name') as $kind)
                <tr class="border-t border-rose-100">
                    <td class="py-2 pr-4 font-semibold">{{ $kind->name }}</td>
                    <td class="py-2 text-right">
                        <form method="POST" action="/admin/boerse/{{ $kind->id }}/handel-sperre" class="inline">
                            @csrf
                            <button class="btn btn-sm bg-emerald-100 text-emerald-800 hover:bg-emerald-200">
                                ✅ Sperre aufheben
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <div class="rounded-xl border-2 border-slate-200 bg-slate-50 p-4 text-slate-500">
            Aktuell sind keine Kinder gesperrt.
        </div>
    @endif

    <div>
        <h3 class="font-bold text-lg mb-3">Alle Kinder ({{ $kinder->count() }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2 text-center">Status</th>
                        <th class="px-3 py-2 text-right">Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kinder->sortBy('name') as $kind)
                    @php $gesperrt = $kind->handelGesperrt(); @endphp
                    <tr class="border-t border-slate-100 {{ $gesperrt ? 'bg-rose-50' : '' }}">
                        <td class="px-3 py-2 font-semibold">{{ $kind->name }}</td>
                        <td class="px-3 py-2 text-center">
                            @if($gesperrt)
                                <span class="inline-block bg-rose-100 text-rose-800 text-xs font-bold px-2 py-0.5 rounded-full">⛔ Gesperrt</span>
                            @else
                                <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-bold px-2 py-0.5 rounded-full">✅ Erlaubt</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <form method="POST" action="/admin/boerse/{{ $kind->id }}/handel-sperre" class="inline">
                                @csrf
                                @if($gesperrt)
                                    <button class="btn btn-sm bg-emerald-100 text-emerald-800 hover:bg-emerald-200">
                                        ✅ Entsperren
                                    </button>
                                @else
                                    <button class="btn btn-sm bg-rose-100 text-rose-700 hover:bg-rose-200"
                                            onclick="return confirm('{{ $kind->name }} vom Handel ausschließen?')">
                                        ⛔ Sperren
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

