<?php


return [
    'startkapital' => 10,

    //Kontofuehrungsgebühr
    'konto_gebuehr' => 2,

    //Kosten Arbeitszeitberechnungen
    'kostenfreie_berechnungen' => 3, //je Tag
    'kosten_berechnungen' => 1, //Radi

    //Steuern
    'steuern' => 2, //Radi
    'gewinn_steuer' => 10, //%

    //Zinsen Kredit
    'zinsen'    => 10,

    'kontostand' => [
        'logout' => 5
    ],

    'lohn' => [
        'chef' => env('LOHN_CHEF', 7),
        'mitarbeiter' => env('LOHN_MITARBEITER', 6),
    ],

    'key' => env('USE_KEY', false),

    // ── Radi-Börse ──────────────────────────────────────────────────────────
    'aktien' => [
        'boerse_pin'             => env('BOERSE_PIN', '1234'),
        'standard_gesamt'        => 100,
        'min_kurs'               => 1,
        'kurs_teiler'            => 20,
        'angestellte_normal'     => 4,
        'max_sprung_prozent'     => 15,
        'dividende_prozent'      => 20,
        'kasse_warnschwelle'     => 50,
        'max_bargeld_invest'     => 50,
        'kauf_gebuehr'           => env('BOERSE_KAUF_GEBUEHR', 1), // Bar-Gebühr je Aktienkauf (zusätzlich zum Kurspreis)
        'aufgaben' => [
            'beobachtung_warn_min'      => 75,
            'beobachtung_alarm_min'     => 90,
            'kassenkontrolle_warn_min'  => 90,
            'kassenkontrolle_alarm_min' => 120,
            'kurstafel_warn_min'        => 15,
            'kurstafel_alarm_min'       => 30,
        ],
    ],

];
