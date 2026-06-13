<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WorkingTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminArbeitszeitController extends Controller
{
    /**
     * Übersicht: alle Personen (keine Betriebe) mit Arbeitszeit-Statistik.
     * Suche über GET-Parameter 'suche'.
     */
    public function index(Request $request)
    {
        $suche = $request->get('suche', '');

        // Alle nicht-Betriebe laden (mit eager-load working_times inkl. Betrieb)
        $query = Customer::where(function ($q) {
            $q->where('buisness', 0)->orWhereNull('buisness');
        })->with(['working_times.buisness']);

        if ($suche !== '') {
            $query->where('name', 'like', '%' . $suche . '%');
        }

        $customers = $query->get();

        // Pro Kunde die Statistik berechnen
        $statistiken = $customers->map(function (Customer $customer) {
            $times = $customer->working_times;

            if ($times->isEmpty()) {
                return [
                    'customer'         => $customer,
                    'tage_gesamt'      => 0,
                    'minuten_gesamt'   => 0,
                    'tage_unter_90min' => 0,
                    'tage_details'     => collect(),
                ];
            }

            // Gruppieren nach Datum (nur Datum, keine Uhrzeit)
            $nachTag = $times->groupBy(fn($wt) => $wt->start->toDateString());

            $tageDetails = $nachTag->map(function ($eintraege, $datum) {
                $minuten = $eintraege->sum('duration');
                return [
                    'datum'    => $datum,
                    'minuten'  => $minuten,
                    'eintraege' => $eintraege,
                    'warnung'  => $minuten < 90,
                ];
            })->sortByDesc('datum')->values();

            return [
                'customer'         => $customer,
                'tage_gesamt'      => $tageDetails->count(),
                'minuten_gesamt'   => $times->sum('duration'),
                'tage_unter_90min' => $tageDetails->where('warnung', true)->count(),
                'tage_details'     => $tageDetails,
            ];
        });

        // Sortierung: zuerst Personen mit Warnungen (tage_unter_90min > 0), dann alphabetisch
        $statistiken = $statistiken->sortByDesc('tage_unter_90min')->values();

        return view('admin.arbeitszeiten.index', [
            'statistiken' => $statistiken,
            'suche'       => $suche,
        ]);
    }

    /**
     * Detail-Ansicht: Arbeitszeit-Historie einer einzelnen Person.
     */
    public function person(Customer $customer)
    {
        $times = $customer->working_times()->with('buisness')->orderBy('start', 'desc')->get();

        // Gruppieren nach Datum
        $nachTag = $times->groupBy(fn($wt) => $wt->start->toDateString());

        $tageDetails = $nachTag->map(function ($eintraege, $datum) {
            $minuten = $eintraege->sum('duration');
            return [
                'datum'    => Carbon::parse($datum),
                'minuten'  => $minuten,
                'stunden'  => floor($minuten / 60),
                'restmin'  => $minuten % 60,
                'eintraege' => $eintraege->sortBy('start'),
                'warnung'  => $minuten < 90,
            ];
        })->sortByDesc('datum')->values();

        $gesamtMinuten = $times->sum('duration');

        return view('admin.arbeitszeiten.person', [
            'customer'      => $customer,
            'tageDetails'   => $tageDetails,
            'gesamtMinuten' => $gesamtMinuten,
            'gesamtStunden' => floor($gesamtMinuten / 60),
            'gesamtRestMin' => $gesamtMinuten % 60,
        ]);
    }
}

