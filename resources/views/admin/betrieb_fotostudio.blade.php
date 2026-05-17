@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.betriebe.pin') }}" class="btn btn-ghost py-2 px-3 text-sm">
                <i class="fa-solid fa-arrow-left"></i> Zurück
            </a>
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-camera text-purple-600"></i>
                Fotostudio – {{ $betrieb->name }}
            </h2>
        </div>

        {{-- Aktueller Status --}}
        <div class="mb-6 p-4 rounded-2xl border-2 {{ $betrieb->is_fotostudio ? 'border-purple-200 bg-purple-50' : 'border-slate-200 bg-slate-50' }}">
            <div class="font-bold text-lg mb-1">
                @if($betrieb->is_fotostudio)
                    <i class="fa-solid fa-circle-check text-purple-600 mr-2"></i>Fotostudio ist <strong>aktiviert</strong>
                @else
                    <i class="fa-solid fa-circle-xmark text-slate-400 mr-2"></i>Fotostudio ist <strong>deaktiviert</strong>
                @endif
            </div>
            @if($betrieb->is_fotostudio && $betrieb->fotostudio_token)
                <div class="mt-2 space-y-3">
                    <div class="flex flex-wrap items-center gap-4">
                        <div>
                            <span class="text-slate-600 text-sm font-semibold block mb-1">Kurzer Link für Kinder:</span>
                            <a href="{{ route('fotostudio.landing') }}" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-lg rounded-xl">
                                <i class="fa-solid fa-display"></i> /fotos
                            </a>
                        </div>
                        <div id="qr-admin" class="bg-white p-2 rounded-xl border border-purple-200 shadow"></div>
                    </div>
                    <div class="text-xs text-slate-400 font-mono break-all bg-white border border-slate-200 rounded-xl px-3 py-1 select-all">
                        {{ url('/fotostudio/' . $betrieb->fotostudio_token) }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Toggle + Token neu generieren --}}
        <div class="flex flex-wrap gap-3">
            @if($betrieb->is_fotostudio)
                <form method="POST" action="{{ route('admin.betriebe.fotostudio.store', $betrieb) }}"
                      onsubmit="return confirm('Fotostudio wirklich deaktivieren? Der Slideshow-Link wird ungültig!')">
                    @csrf
                    <input type="hidden" name="aktion" value="deaktivieren">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-toggle-off mr-1"></i> Fotostudio deaktivieren
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.betriebe.fotostudio.store', $betrieb) }}"
                      onsubmit="return confirm('Neuen Link generieren? Der alte Link funktioniert dann nicht mehr!')">
                    @csrf
                    <input type="hidden" name="aktion" value="token_neu">
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-rotate mr-1"></i> Neuen Link generieren
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.betriebe.fotostudio.store', $betrieb) }}">
                    @csrf
                    <input type="hidden" name="aktion" value="aktivieren">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-toggle-on mr-1"></i> Fotostudio aktivieren
                    </button>
                </form>
            @endif
        </div>

        {{-- Bildübersicht (Admin, nur lesen) --}}
        @if($betrieb->is_fotostudio && $bilder->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-lg font-bold text-slate-700 mb-3">
                <i class="fa-solid fa-images mr-2 text-purple-500"></i>
                Hochgeladene Bilder ({{ $bilder->count() }})
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($bilder as $bild)
                <div class="rounded-xl overflow-hidden border border-slate-200 {{ $bild->istAktiv() ? 'ring-2 ring-purple-400' : 'opacity-60' }}">
                    <img src="{{ $bild->url() }}" alt="{{ $bild->titel ?? '' }}"
                         class="w-full h-24 object-cover">
                    <div class="p-2 bg-white text-xs">
                        <div class="font-semibold truncate">{{ $bild->titel ?? '–' }}</div>
                        <div class="text-slate-400">{{ $bild->istAktiv() ? 'Aktiv' : 'Inaktiv' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @elseif($betrieb->is_fotostudio)
        <div class="mt-6 text-slate-400 text-sm">Noch keine Bilder hochgeladen.</div>
        @endif
    </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
@if($betrieb->is_fotostudio && $betrieb->fotostudio_token)
document.addEventListener('DOMContentLoaded', function () {
    new QRCode(document.getElementById('qr-admin'), {
        text: '{{ url('/fotostudio/' . $betrieb->fotostudio_token) }}',
        width: 140,
        height: 140,
        colorDark: '#581c87',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
});
@endif
</script>
@endpush




