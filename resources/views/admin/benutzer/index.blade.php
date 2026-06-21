@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold flex items-center gap-2">
            <i class="fa-solid fa-users-gear text-brand-600"></i>
            Benutzer verwalten
        </h1>
        <a href="{{ route('admin.benutzer.create') }}"
           class="btn-primary flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            Neuer Benutzer
        </a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">E-Mail</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Admin</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Manager</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Einzahlen</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Auszahlen</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Arbeitszeit</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($benutzer as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span class="ml-1 text-xs text-brand-600 font-semibold">(ich)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($user->is_admin)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded-full">
                                    <i class="fa-solid fa-shield-halved"></i> Ja
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->is_manager)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                                    <i class="fa-solid fa-user-tie"></i> Ja
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->kann_einzahlen)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 bg-sky-50 px-2 py-1 rounded-full">
                                    <i class="fa-solid fa-arrow-down-to-line"></i> Ja
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->kann_auszahlen)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded-full">
                                    <i class="fa-solid fa-arrow-up-from-line"></i> Ja
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->kann_arbeitszeit)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-violet-600 bg-violet-50 px-2 py-1 rounded-full">
                                    <i class="fa-solid fa-clock"></i> Ja
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.benutzer.edit', $user) }}"
                                   class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-sky-100 text-sky-700 hover:bg-sky-200">
                                    <i class="fa-solid fa-pen mr-1"></i>Bearbeiten
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.benutzer.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Benutzer {{ $user->name }} wirklich löschen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200">
                                            <i class="fa-solid fa-trash mr-1"></i>Löschen
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                            Noch keine Benutzer vorhanden.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

