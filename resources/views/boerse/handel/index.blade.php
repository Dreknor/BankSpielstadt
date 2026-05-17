@extends('boerse.layouts.app')
@section('content')
<div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200">
    <h1 class="text-3xl font-extrabold text-amber-700">🤝 Handel</h1>
    <p class="text-slate-700">Wähle einen Betrieb aus, um Anteile zu kaufen, zu verkaufen oder zurückzukaufen.</p>
    @php $gebuehr = (int) config('bank.aktien.kauf_gebuehr', 1); @endphp
    @if($gebuehr > 0)
        <div class="mt-2 inline-block bg-amber-100 border border-amber-300 text-amber-900 rounded-lg px-3 py-1 text-sm font-semibold">
            💡 Beim Kauf erhebt die Börse <b>{{ $gebuehr }} Radi</b> Gebühr je Vorgang. Diese geht in die Börsen-Kasse.
        </div>
    @endif
</div>

@if($betriebe->isEmpty())
    <div class="bg-white rounded-2xl shadow-kid p-6 border-2 border-amber-200 text-center text-slate-500">
        Noch keine Betriebe an der Börse. Die Lehrkraft muss erst Betriebe freischalten.
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($betriebe as $b)
            <div class="bg-white rounded-2xl shadow-kid p-5 border-2 border-amber-200">
                <div class="text-xl font-extrabold">{{ $b->name }}</div>
                <div class="text-3xl font-bold text-amber-600 my-2">{{ $b->aktien_kurs }} Radi</div>
                <div class="text-sm text-slate-600">
                    Freie Anteile: <b>{{ $b->anteileEigen() }}</b> von {{ $b->aktien_gesamt }}
                </div>
                <div class="mt-4 grid grid-cols-1 gap-2">
                    <a href="/boerse/handel/{{ $b->id }}/kaufen"
                       class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 rounded-xl text-center">🛒 Kauf erfassen</a>
                    <a href="/boerse/handel/{{ $b->id }}/verkaufen"
                       class="bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 rounded-xl text-center">💵 Verkauf erfassen</a>
                    <a href="/boerse/handel/{{ $b->id }}/rueckkauf"
                       class="bg-violet-500 hover:bg-violet-600 text-white font-bold py-2 rounded-xl text-center">🔄 Rückkauf durch Betrieb</a>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection


