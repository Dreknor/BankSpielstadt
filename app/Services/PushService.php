<?php

namespace App\Services;

use App\Models\PushAbonnement;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushService
{
    public function senden(string $titel, string $nachricht, string $url = '/'): void
    {
        $publicKey  = config('push.vapid_public_key');
        $privateKey = config('push.vapid_private_key');

        if (blank($publicKey) || blank($privateKey)) {
            return; // VAPID nicht konfiguriert – still ignorieren
        }

        $abonnements = PushAbonnement::all();
        if ($abonnements->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => config('push.vapid_subject'),
                    'publicKey'  => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);
            $webPush->setAutomaticPadding(false);

            $payload = json_encode([
                'title' => $titel,
                'body'  => $nachricht,
                'url'   => $url,
                'icon'  => '/favicon.ico',
            ]);

            foreach ($abonnements as $abo) {
                $subscription = Subscription::create([
                    'endpoint'        => $abo->endpoint,
                    'keys'            => [
                        'p256dh' => $abo->public_key,
                        'auth'   => $abo->auth_token,
                    ],
                    'contentEncoding' => $abo->content_encoding ?? 'aesgcm',
                ]);
                $webPush->queueNotification($subscription, $payload);
            }

            foreach ($webPush->flush() as $report) {
                if (! $report->isSuccess()) {
                    // Ungültige Abonnements automatisch löschen (410 Gone)
                    if ($report->getResponse() && $report->getResponse()->getStatusCode() === 410) {
                        PushAbonnement::where('endpoint', $report->getEndpoint())->delete();
                    }
                }
            }
        } catch (\Throwable $e) {
            // Push-Fehler nicht weiter eskalieren – nur loggen
            \Illuminate\Support\Facades\Log::warning('Push fehlgeschlagen: ' . $e->getMessage());
        }
    }
}

