<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID-Schlüssel für Web-Push-Benachrichtigungen
    |--------------------------------------------------------------------------
    |
    | Mit `php artisan push:vapid-generieren` neue Schlüssel erzeugen und
    | in die .env eintragen. Schlüssel nur einmal generieren!
    |
    */
    'vapid_public_key'  => env('VAPID_PUBLIC_KEY', ''),
    'vapid_private_key' => env('VAPID_PRIVATE_KEY', ''),

    /*
    | E-Mail-Adresse, die bei Push-Anfragen als Kontakt angegeben wird
    | (RFC-Pflichtfeld für VAPID).
    */
    'vapid_subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),
];

