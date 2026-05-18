<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLogController extends Controller
{
    private const ZEILEN_PRO_SEITE = 100;

    public function index(Request $request): View
    {
        $logDatei  = storage_path('logs/laravel.log');
        $eintraege = [];
        $filter    = $request->get('filter', 'all'); // all | error | warning | info

        if (file_exists($logDatei)) {
            $eintraege = $this->parseLog($logDatei, $filter);
        }

        // Neueste zuerst
        $eintraege = array_reverse($eintraege);

        // Einfache manuelle Paginierung
        $seite   = max(1, (int) $request->get('seite', 1));
        $gesamt  = count($eintraege);
        $offset  = ($seite - 1) * self::ZEILEN_PRO_SEITE;
        $seiten  = (int) ceil($gesamt / self::ZEILEN_PRO_SEITE);

        $eintraege = array_slice($eintraege, $offset, self::ZEILEN_PRO_SEITE);

        return view('admin.logs', [
            'eintraege'     => $eintraege,
            'filter'        => $filter,
            'seite'         => $seite,
            'seiten'        => $seiten,
            'gesamt'        => $gesamt,
            'logDateiName'  => basename($logDatei),
        ]);
    }

    /** Löscht die Log-Datei (Admin-Aktion) */
    public function leeren(): \Illuminate\Http\RedirectResponse
    {
        $logDatei = storage_path('logs/laravel.log');
        if (file_exists($logDatei)) {
            file_put_contents($logDatei, '');
        }

        return redirect()->route('admin.logs')->with([
            'type'    => 'success',
            'Meldung' => 'Log-Datei wurde geleert.',
        ]);
    }

    /** Parst die Logdatei und gibt strukturierte Einträge zurück */
    private function parseLog(string $pfad, string $filter): array
    {
        // Nur die letzten 500 KB lesen, um Speicher zu sparen
        $maxBytes = 500 * 1024;
        $inhalt   = '';
        $size     = filesize($pfad);
        if ($size > 0) {
            $fp = fopen($pfad, 'rb');
            if ($size > $maxBytes) {
                fseek($fp, -$maxBytes, SEEK_END);
                fgets($fp); // erste (ggf. halbe) Zeile verwerfen
            }
            $inhalt = stream_get_contents($fp);
            fclose($fp);
        }

        // Laravel-Log-Format: [YYYY-MM-DD HH:MM:SS] production.LEVEL: Nachricht
        $muster   = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+?)(?=^\[|\z)/ms';
        preg_match_all($muster, $inhalt, $treffer, PREG_SET_ORDER);

        $eintraege = [];
        foreach ($treffer as $t) {
            $level = strtolower($t[2]);

            if ($filter !== 'all' && $level !== $filter) {
                continue;
            }

            $eintraege[] = [
                'zeitpunkt' => $t[1],
                'level'     => $level,
                'nachricht' => trim($t[3]),
            ];
        }

        return $eintraege;
    }
}

