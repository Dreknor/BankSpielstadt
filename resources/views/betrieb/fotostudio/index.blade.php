@extends('betrieb.layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Kopfzeile --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-3xl font-extrabold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-camera text-emerald-600"></i> Fotostudio – Bildverwaltung
        </h1>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('betrieb.fotostudio.hochladen') }}"
               class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow text-lg flex items-center gap-2">
                <i class="fa-solid fa-upload"></i> Bilder hochladen
            </a>
        </div>
    </div>

    {{-- Slideshow-Link --}}
    @if($slideshowUrl)
    <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-4 space-y-4">
        <div class="flex flex-wrap items-start gap-6">
            {{-- Infos & Link --}}
            <div class="flex-1 min-w-0 space-y-2">
                <div class="font-bold text-emerald-800 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-tv"></i> Fernseher-Link (Slideshow)
                </div>
                <p class="text-slate-500 text-sm">
                    Einfachste Methode: <strong class="text-slate-700">QR-Code scannen</strong> – oder den kurzen Link eintippen:
                </p>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('fotostudio.landing') }}" target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xl rounded-2xl shadow">
                        <i class="fa-solid fa-display"></i>
                        <span class="font-mono">/fotos</span>
                    </a>
                    <span class="text-slate-400 text-sm">(direkte Slideshow → kein Tippen nötig!)</span>
                </div>
                <div class="text-xs text-slate-400 font-mono break-all bg-white border border-slate-200 rounded-xl px-3 py-2 select-all">
                    {{ $slideshowUrl }}
                </div>
            </div>
            {{-- QR-Code --}}
            <div class="flex flex-col items-center gap-2">
                <div id="qrcode" class="bg-white p-3 rounded-2xl shadow border border-emerald-200"></div>
                <p class="text-xs text-emerald-700 font-semibold text-center">QR-Code scannen<br>→ Slideshow startet</p>
            </div>
        </div>
    </div>
    @else
    <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 p-4 text-amber-800">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Kein Slideshow-Link vorhanden. Bitte einen Administrator bitten, das Fotostudio zu aktivieren.
    </div>
    @endif

    {{-- Bilderliste --}}
    @if($bilder->isEmpty())
    <div class="rounded-2xl border-2 border-slate-200 bg-white p-10 text-center text-slate-500 text-lg">
        <i class="fa-solid fa-image text-5xl mb-4 block text-slate-300"></i>
        Noch keine Bilder hochgeladen.
        <div class="mt-4">
            <a href="{{ route('betrieb.fotostudio.hochladen') }}"
               class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl inline-flex items-center gap-2">
                <i class="fa-solid fa-upload"></i> Erstes Bild hochladen
            </a>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($bilder as $bild)
        <div class="rounded-2xl border-2 {{ $bild->istAktiv() ? 'border-emerald-200 bg-white' : 'border-slate-200 bg-slate-50' }} overflow-hidden shadow-sm">
            <div class="relative">
                <img src="{{ $bild->url() }}" alt="{{ $bild->titel ?? $bild->dateiname }}"
                     class="w-full h-48 object-cover {{ $bild->istAktiv() ? '' : 'opacity-40 grayscale' }}">
                <div class="absolute top-2 right-2">
                    @if($bild->istAktiv())
                        <span class="bg-emerald-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow">
                            <i class="fa-solid fa-eye"></i> Aktiv
                        </span>
                    @else
                        <span class="bg-slate-400 text-white text-xs font-bold px-2 py-1 rounded-full shadow">
                            <i class="fa-solid fa-eye-slash"></i> Inaktiv
                        </span>
                    @endif
                </div>
            </div>
            <div class="p-3 space-y-2">
                <div class="font-bold text-slate-800 truncate">
                    {{ $bild->titel ?? '(kein Titel)' }}
                </div>
                <div class="text-xs text-slate-500 truncate">
                    <i class="fa-solid fa-clock mr-1"></i>{{ $bild->zeitLabel() }}
                </div>
                <div class="text-xs text-slate-400">
                    Reihenfolge: {{ $bild->reihenfolge }}
                </div>
                <div class="flex gap-2 pt-1">
                    <a href="{{ route('betrieb.fotostudio.bearbeiten', $bild) }}"
                       class="flex-1 text-center px-3 py-2 bg-sky-100 hover:bg-sky-200 text-sky-800 font-semibold rounded-xl text-sm">
                        <i class="fa-solid fa-pencil mr-1"></i>Bearbeiten
                    </a>
                    <form method="POST" action="{{ route('betrieb.fotostudio.destroy', $bild) }}"
                          onsubmit="return confirm('Bild wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-3 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 font-semibold rounded-xl text-sm">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
@if($slideshowUrl)
new QRCode(document.getElementById('qrcode'), {
    text: '{{ $slideshowUrl }}',
    width: 180,
    height: 180,
    colorDark: '#065f46',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
});
@endif
</script>
@endpush



