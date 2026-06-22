@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                📈 Radi-Börse — Admin-Übersicht
                @if($alarmCount > 0)
                    <span class="bg-rose-500 text-white text-sm font-bold px-3 py-1 rounded-full">🔴 {{ $alarmCount }} Alarm</span>
                @endif
            </h2>
            <div class="flex gap-2 flex-wrap">
                <a href="/admin/boerse/aktivieren"        class="btn btn-primary">➕ Betrieb aktivieren</a>
                <a href="/admin/boerse/dividende/neu"     class="btn btn-primary">🎉 Dividende ausschütten</a>
                <a href="/admin/boerse/abschluss/vorbereiten" class="btn">🏁 Schlussabrechnung</a>
                <a href="/admin/boerse/pin"               class="btn">🔑 PIN ändern</a>
                <a href="/admin/boerse/bericht"           class="btn">📋 Bericht</a>
                <a href="/admin/boerse/arbitrage"         class="btn">🔍 Arbitrage</a>
                <a href="/admin/boerse/handel-sperre"    class="btn">⛔ Handelssperren</a>
                <a href="/admin/boerse/aufgaben"          class="btn {{ $alarmCount > 0 ? 'bg-rose-500 text-white' : '' }}">🚨 Aufgaben-Status</a>
            </div>
        </div>
    </div>

    <div class="card p-6">
        <h3 class="text-xl font-bold mb-3">Aktive Betriebe</h3>
        <table class="w-full">
            <thead class="text-left text-slate-600">
                <tr><th class="py-2">Betrieb</th><th>Status</th><th>Kurs</th><th>Anteile (verkauft/gesamt)</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($betriebe as $b)
                <tr class="border-t border-slate-100">
                    <td class="py-2 font-bold">{{ $b->name }}</td>
                    <td>
                        @if($b->hatAktien())
                            <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded font-semibold">aktiv</span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="font-bold">{{ $b->aktien_kurs ?? '—' }} {{ $b->hatAktien() ? 'Radi' : '' }}</td>
                    <td>
                        @if($b->hatAktien())
                            {{ $b->anteileVerkauft() }} / {{ $b->aktien_gesamt }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right">
                        @if($b->hatAktien())
                            <a href="/admin/boerse/{{ $b->id }}/kurs" class="btn btn-sm">Kurs korrigieren</a>
                            <form method="POST" action="/admin/boerse/{{ $b->id }}/deaktivieren" class="inline"
                                  onsubmit="return confirm('Betrieb wirklich deaktivieren? Bestehende Anteile bleiben erhalten, der Kurs wird gestoppt.')">
                                @csrf
                                <button class="btn btn-sm bg-rose-100 text-rose-700">Deaktivieren</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

