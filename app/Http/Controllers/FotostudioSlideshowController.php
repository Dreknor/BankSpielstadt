<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class FotostudioSlideshowController extends Controller
{
    private function findBetrieb(string $token): Customer
    {
        $betrieb = Customer::where('fotostudio_token', $token)
            ->where('is_fotostudio', true)
            ->firstOrFail();
        return $betrieb;
    }

    /** Öffentliche Landing-Page: alle aktiven Fotostudios als große Buttons */
    public function landing()
    {
        $studios = Customer::where('is_fotostudio', true)
            ->whereNotNull('fotostudio_token')
            ->get();

        // Genau ein Studio → direkt weiterleiten
        if ($studios->count() === 1) {
            return redirect()->route('fotostudio.slideshow', $studios->first()->fotostudio_token);
        }

        return view('fotostudio.landing', compact('studios'));
    }

    /** Vollbild-Slideshow-Seite für Fernseher */
    public function show(string $token)
    {
        $betrieb = $this->findBetrieb($token);
        return view('fotostudio.slideshow', compact('betrieb', 'token'));
    }

    /** JSON-Endpunkt: gibt aktuell aktive Bilder zurück */
    public function daten(string $token): JsonResponse
    {
        $betrieb = $this->findBetrieb($token);

        $bilder = $betrieb->fotostudioBilder()
            ->where('sichtbar', true)
            ->where(function ($q) {
                $jetzt = now();
                $q->whereNull('anzeige_von')->orWhere('anzeige_von', '<=', $jetzt);
            })
            ->where(function ($q) {
                $jetzt = now();
                $q->whereNull('anzeige_bis')->orWhere('anzeige_bis', '>=', $jetzt);
            })
            ->orderBy('reihenfolge')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($b) => [
                'id'    => $b->id,
                'url'   => $b->url(),
                'titel' => $b->titel,
                'dauer' => $b->anzeige_dauer ?? 5,  // Sekunden, Fallback 5
            ]);

        return response()->json([
            'betrieb' => $betrieb->name,
            'bilder'  => $bilder,
        ]);
    }
}


