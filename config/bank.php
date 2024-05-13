<?php


return [
    'startkapital' => 3,

    //Kontofuehrungsgebühr
    'konto_gebuehr' => 1,

    //Kosten Arbeitszeitberechnungen
    'kostenfreie_berechnungen' => 3, //je Tag
    'kosten_berechnungen' => 1, //Radi

    //Steuern
    'steuern' => 1, //Radi
    'gewinn_steuer' => 10, //%

    //Zinsen Kredit
    'zinsen'    => 10,

    'kontostand' => [
        'logout' => 7
    ],

    'lohn' => [
        'chef' => env('LOHN_CHEF', 4),
        'mitarbeiter' => env('LOHN_MITARBEITER', 3),
    ],

];
