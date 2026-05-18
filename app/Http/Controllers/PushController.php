<?php

namespace App\Http\Controllers;

use App\Models\PushAbonnement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushController extends Controller
{
    /** Speichert ein neues Push-Abonnement des eingeloggten Admins */
    public function abonnieren(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'          => 'required|url',
            'keys.p256dh'       => 'required|string',
            'keys.auth'         => 'required|string',
            'contentEncoding'   => 'nullable|string',
        ]);

        // Vorhandenes Abonnement aktualisieren oder neu anlegen
        PushAbonnement::updateOrCreate(
            ['user_id' => auth()->id(), 'endpoint' => $request->endpoint],
            [
                'public_key'       => $request->input('keys.p256dh'),
                'auth_token'       => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding', 'aesgcm'),
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    /** Löscht das Abonnement (Browser-Opt-out) */
    public function abbestellen(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string']);

        PushAbonnement::where('user_id', auth()->id())
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json(['status' => 'ok']);
    }

    /** Gibt den VAPID-Public-Key zurück (für Browser-Subscription) */
    public function vapidKey(): JsonResponse
    {
        return response()->json(['publicKey' => config('push.vapid_public_key')]);
    }
}

