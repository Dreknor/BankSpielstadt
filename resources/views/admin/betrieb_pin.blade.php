@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-key text-brand-600"></i> Betriebs-PINs verwalten
        </h2>

        <form method="POST" action="{{ route('admin.betriebe.pin.store') }}" class="space-y-4 mb-6 pb-6 border-b-2 border-slate-100">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Betrieb auswählen</label>
                    <select name="betrieb_id" class="field" required>
                        <option value="" disabled selected>– bitte wählen –</option>
                        @foreach($betriebe as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Neuer PIN</label>
                    <input type="text" name="pin" class="field" minlength="4" maxlength="20" required placeholder="mind. 4 Zeichen">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> PIN setzen
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="text-sm uppercase text-slate-500 border-b-2 border-slate-200">
                    <tr>
                        <th class="px-3 py-2">Betrieb</th>
                        <th class="px-3 py-2">PIN</th>
                        <th class="px-3 py-2">Kassenbestand</th>
                        <th class="px-3 py-2">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($betriebe as $b)
                        <tr>
                            <td class="px-3 py-3 font-semibold">{{ $b->name }}</td>
                            <td class="px-3 py-3 font-mono">
                                @if($b->betrieb_pin)
                                    <span class="bg-emerald-100 text-emerald-800 rounded-xl px-3 py-1">{{ $b->betrieb_pin }}</span>
                                @else
                                    <span class="text-slate-400">kein PIN</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 font-bold text-emerald-700">{{ $b->kassenbestand() }} Radi</td>
                             <td class="px-3 py-3 flex gap-2 flex-wrap">
                                <a href="{{ route('admin.betriebe.kasse', $b) }}" class="btn btn-info text-sm py-2 px-3">
                                    <i class="fa-solid fa-cash-register"></i> Kasse
                                </a>
                                <a href="{{ route('admin.betriebe.produkte', $b) }}" class="btn btn-ghost text-sm py-2 px-3">
                                    <i class="fa-solid fa-box"></i> Produkte
                                </a>
                                <a href="{{ route('admin.betriebe.fotostudio', $b) }}" class="btn btn-ghost text-sm py-2 px-3 {{ $b->is_fotostudio ? 'bg-purple-100 text-purple-800' : '' }}">
                                    <i class="fa-solid fa-camera"></i> Fotostudio
                                </a>
                                <a href="{{ route('admin.betriebe.boerse', $b) }}" class="btn btn-ghost text-sm py-2 px-3 {{ $b->is_boerse ? 'bg-amber-100 text-amber-800' : '' }}">
                                    <i class="fa-solid fa-chart-line"></i> Börse
                                    @if($b->is_boerse)
                                        <span class="ml-1 text-xs font-bold">✓</span>
                                    @endif
                                </a>
                                 <a href="{{ route('admin.betriebe.support', $b) }}" class="btn btn-ghost text-sm py-2 px-3 {{ $b->is_support ? 'bg-rose-100 text-rose-800' : '' }}">
                                     <i class="fa-solid fa-bell"></i> Support
                                     @if($b->is_support)
                                         <span class="ml-1 text-xs font-bold">✓</span>
                                     @endif
                                 </a>
                                 <a href="{{ route('admin.betriebe.lieferdienst', $b) }}" class="btn btn-ghost text-sm py-2 px-3 {{ $b->is_lieferdienst ? 'bg-emerald-100 text-emerald-800' : '' }}">
                                     <i class="fa-solid fa-motorcycle"></i> Lieferdienst
                                     @if($b->is_lieferdienst)
                                         <span class="ml-1 text-xs font-bold">✓</span>
                                     @endif
                                 </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

