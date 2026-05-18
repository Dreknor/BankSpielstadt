@extends('layouts.app')
@section('content')
<div class="card p-6 space-y-4">
    <h2 class="text-2xl font-extrabold">🚨 Aufgaben-Status der Börsen-Mitarbeiter</h2>

    @php
        $stylesAdmin = [
            'ok'    => 'bg-emerald-100 border-emerald-300 text-emerald-900',
            'warn'  => 'bg-amber-100 border-amber-400 text-amber-900',
            'alarm' => 'bg-rose-100 border-rose-400 text-rose-900',
        ];
        $labels = [
            'beobachtung'     => '🔭 Beobachtung der Angestelltenzahlen',
            'kassenkontrolle' => '💰 Bestätigung Kassenstand',
            'kurstafel'       => '📰 Aktualisierung der Kurstafel',
        ];
    @endphp

    <div class="space-y-3">
        @foreach($labels as $key => $label)
            @php
                $s   = $aufgabenStatus[$key];
                $cls = $stylesAdmin[$s['status']] ?? $stylesAdmin['ok'];
            @endphp
            <div class="rounded-xl border-2 p-4 {{ $cls }}">
                <div class="text-lg font-bold">{{ $label }}</div>
                <div class="text-sm">
                    Status: <b>{{ strtoupper($s['status']) }}</b>
                    @if(isset($s['minuten']) && $s['minuten'] < 999)
                        · zuletzt vor {{ $s['minuten'] }} Minuten
                    @endif
                    @if(!empty($s['mitarbeiter']))
                        · bestätigt von: <b>{{ $s['mitarbeiter'] }}</b>
                    @endif
                    @if(!empty($s['text'])) · {{ $s['text'] }} @endif
                </div>
            </div>
        @endforeach
    </div>

    <div><a href="/admin/boerse" class="btn">🔙 Zurück</a></div>
</div>
@endsection

