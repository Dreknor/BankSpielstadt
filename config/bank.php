<?php


return [
    'startkapital' => env('BANK_STARTKAPITAL', 10),

    //Kontofuehrungsgebühr
    'konto_gebuehr' => env('BANK_KONTO_GEBUEHR', 2),

    //Kosten Arbeitszeitberechnungen
    'kostenfreie_berechnungen' => env('BANK_KOSTENFREIE_BERECHNUNGEN', 3), //je Tag
    'kosten_berechnungen'      => env('BANK_KOSTEN_BERECHNUNGEN', 1),      //Radi

    //Steuern
    'steuern'      => env('BANK_STEUERN', 2),        //Radi
    'gewinn_steuer' => env('BANK_GEWINN_STEUER', 10), //%

    //Zinsen Kredit
    'zinsen' => env('BANK_ZINSEN', 10),

    'kontostand' => [
        'logout' => env('BANK_KONTOSTAND_LOGOUT', 5), // Minuten bis Auto-Logout
    ],

    'lohn' => [
        'chef' => env('LOHN_CHEF', 7),
        'mitarbeiter' => env('LOHN_MITARBEITER', 6),
    ],

    'key' => env('USE_KEY', false),

    // ── Radi-Börse ──────────────────────────────────────────────────────────
    'aktien' => [
        'boerse_pin'             => env('BOERSE_PIN', '1234'),
        'standard_gesamt'        => env('BOERSE_STANDARD_GESAMT', 100),    // Gesamtanzahl Aktien je Unternehmen
        'min_kurs'               => env('BOERSE_MIN_KURS', 1),             // Mindestkurs in Radi
        'kurs_teiler'            => env('BOERSE_KURS_TEILER', 20),         // Teiler für Kursberechnung
        'angestellte_normal'     => env('BOERSE_ANGESTELLTE_NORMAL', 4),   // Normaler Personalstand
        'max_sprung_prozent'     => env('BOERSE_MAX_SPRUNG_PROZENT', 15),  // Maximale Kursänderung in %
        'dividende_prozent'      => env('BOERSE_DIVIDENDE_PROZENT', 20),   // Dividendenanteil am Gewinn in %
        'kasse_warnschwelle'     => env('BOERSE_KASSE_WARNSCHWELLE', 50),  // Börsen-Kassenbestand Warnschwelle (Radi)
        'max_bargeld_invest'     => env('BOERSE_MAX_BARGELD_INVEST', 50),  // Max. Bargeld-Investition je Kauf (Radi)
        'kauf_gebuehr'           => env('BOERSE_KAUF_GEBUEHR', 1),         // Bar-Gebühr je Aktienkauf (zusätzlich zum Kurspreis)
        'aufgaben' => [
            'beobachtung_warn_min'      => env('BOERSE_BEOBACHTUNG_WARN_MIN', 75),
            'beobachtung_alarm_min'     => env('BOERSE_BEOBACHTUNG_ALARM_MIN', 90),
            'kassenkontrolle_warn_min'  => env('BOERSE_KASSENKONTROLLE_WARN_MIN', 90),
            'kassenkontrolle_alarm_min' => env('BOERSE_KASSENKONTROLLE_ALARM_MIN', 120),
            'kurstafel_warn_min'        => env('BOERSE_KURSTAFEL_WARN_MIN', 15),
            'kurstafel_alarm_min'       => env('BOERSE_KURSTAFEL_ALARM_MIN', 30),
        ],
    ],

];
