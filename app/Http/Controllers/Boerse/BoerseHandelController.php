<?php

namespace App\Http\Controllers\Boerse;

use App\Http\Controllers\Controller;
use App\Http\Requests\AktienKaufRequest;
use App\Http\Requests\AktienVerkaufRequest;
use App\Http\Requests\AktienRueckkaufRequest;
use App\Exceptions\BoerseException;
use App\Models\AktienBestand;
use App\Models\AktienTransaktion;
use App\Models\BoerseKasse;
use App\Models\Customer;
use App\Models\Payment;
use App\Services\BoerseAufgabenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoerseHandelController extends Controller
{
    private function aufgabenStatus(): array
    {
        return app(BoerseAufgabenService::class)->status();
    }

    public function index()
    {
        $betriebe       = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        $aufgabenStatus = $this->aufgabenStatus();
        return view('boerse.handel.index', compact('betriebe', 'aufgabenStatus'));
    }

    /** Autocomplete-Suche für Kinder (Kauf-Flow). JSON: id + name */
    public function searchKinder(\Illuminate\Http\Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $kinder = Customer::where(function ($query) {
                $query->where('buisness', 0)->orWhereNull('buisness');
            })
            ->when(mb_strlen($q) >= 1, fn($query) => $query->where('name', 'LIKE', '%' . $q . '%'))
            ->limit(100)
            ->get(['id', 'name']);
        return response()->json($kinder);
    }

    /**
     * Autocomplete-Suche unter den aktuellen Anteilseignern eines Betriebs.
     * Wird beim Verkauf + Rückkauf benutzt. JSON: id + name + stueck
     */
    public function searchInhaber(\Illuminate\Http\Request $request, Customer $customer)
    {
        $q = trim((string) $request->get('q', ''));
        $query = AktienBestand::where('buisness_id', $customer->id)
            ->where('stueck', '>', 0)
            ->with('kind');
        if (mb_strlen($q) >= 1) {
            $query->whereHas('kind', fn($w) => $w->where('name', 'LIKE', '%' . $q . '%'));
        }

        $minKurs    = (int) config('bank.aktien.min_kurs', 4);
        $spread     = (int) config('bank.aktien.verkauf_spread', 1);
        $normalKurs = max($minKurs, (int) $customer->aktien_kurs - $spread);

        $result = $query->limit(20)->get()->map(function ($b) use ($normalKurs) {
            $avgKauf     = $b->kind ? $b->kind->avgKaufKurs($b->buisness_id) : 0;
            $verkaufKurs = ($avgKauf > 0 && $avgKauf < $normalKurs) ? $avgKauf : $normalKurs;
            return [
                'id'           => $b->customer_id,
                'name'         => $b->kind?->name ?? '—',
                'stueck'       => $b->stueck,
                'verkauf_kurs' => $verkaufKurs,
            ];
        });
        return response()->json($result);
    }

    public function kaufenForm(Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);
        $aufgabenStatus = $this->aufgabenStatus();
        return view('boerse.handel.kaufen', compact('customer', 'aufgabenStatus'));
    }

    public function kaufen(AktienKaufRequest $request, Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);

        $stueck   = (int) $request->stueck;
        $kurs     = (int) $customer->aktien_kurs;        // Anzeige/Vorab-Schätzung
        $gebuehr  = (int) config('bank.aktien.kauf_gebuehr', 1);
        $gesamt   = $stueck * $kurs + $gebuehr;
        $kind     = Customer::findOrFail($request->customer_id);

        // Börsen-Konto muss eingerichtet sein
        $boerse = Customer::boerseBetrieb();
        if (!$boerse) {
            return back()->with(['type' => 'error',
                'Meldung' => '⚠️ Kein Börsen-Konto eingerichtet! Bitte den Admin kontaktieren (Admin → Börse → Einstellungen).']);
        }

        // Handelssperre prüfen
        if ($kind->handelGesperrt()) {
            return back()->with(['type' => 'error',
                'Meldung' => "⛔ {$kind->name} ist vom Börsenhandel ausgeschlossen und darf keine Anteile kaufen."]);
        }

        // Vorab-Prüfung Eigenbestand (für schnelle UX; die harte Prüfung folgt unter Lock)
        if ($customer->anteileEigen() < $stueck) {
            return back()->with(['type' => 'error',
                'Meldung' => "Das {$customer->name} hat nicht genug freie Anteile! Noch verfügbar: {$customer->anteileEigen()}."]);
        }

        // Vorab-Prüfung Obergrenze je Kind
        $maxAnteile   = (int) config('bank.aktien.max_anteile_je_kind', 10);
        $vorabBestand = (int) (AktienBestand::where('customer_id', $kind->id)
            ->where('buisness_id', $customer->id)->value('stueck') ?? 0);
        if ($vorabBestand + $stueck > $maxAnteile) {
            $nochMoeglich = max(0, $maxAnteile - $vorabBestand);
            return back()->with(['type' => 'error',
                'Meldung' => "{$kind->name} darf höchstens {$maxAnteile} Anteile von {$customer->name} besitzen "
                           . "(aktuell: {$vorabBestand}, noch möglich: {$nochMoeglich})."]);
        }

        // Soft-Limit Warnung (kein harter Stop) – inkl. Gebühr
        $maxInvest = config('bank.aktien.max_bargeld_invest', 50);
        if ($gesamt > $maxInvest && !$request->bestaetigt) {
            return back()
                ->withInput()
                ->with(['type' => 'warning',
                    'Meldung' => "Das sind {$gesamt} Radi (inkl. {$gebuehr} Radi Gebühr) — das ist viel Geld! Bitte nochmal bestätigen.",
                    'bestaetigung_noetig' => true]);
        }

        $ergebnis = [];

        try {
            DB::transaction(function () use ($customer, $kind, $stueck, $gebuehr, $boerse, &$ergebnis) {
                // Row-Lock auf den Betrieb serialisiert alle Trades dieses Betriebs
                // (verhindert Überverkauf bei gleichzeitigen Käufen).
                $betrieb = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();

                // Kurs erst hier (unter Lock) lesen — verbindlicher Preis.
                $kurs  = (int) $betrieb->aktien_kurs;
                $summe = $stueck * $kurs;

                // Harte Eigenbestands-Prüfung unter Lock.
                $verkauft = (int) AktienBestand::where('buisness_id', $betrieb->id)
                    ->lockForUpdate()->sum('stueck');
                $eigen = (int) ($betrieb->aktien_gesamt ?? 0) - $verkauft;
                if ($eigen < $stueck) {
                    throw new BoerseException(
                        "Das {$betrieb->name} hat nicht genug freie Anteile! Noch verfügbar: {$eigen}.");
                }

                // Harte Obergrenze je Kind unter Lock.
                $maxAnteile     = (int) config('bank.aktien.max_anteile_je_kind', 10);
                $bestandVorher  = AktienBestand::where('customer_id', $kind->id)
                    ->where('buisness_id', $betrieb->id)->lockForUpdate()->first();
                $aktuellerBestand = (int) ($bestandVorher?->stueck ?? 0);
                if ($aktuellerBestand + $stueck > $maxAnteile) {
                    $nochMoeglich = max(0, $maxAnteile - $aktuellerBestand);
                    throw new BoerseException(
                        "{$kind->name} darf höchstens {$maxAnteile} Anteile von {$betrieb->name} besitzen "
                        . "(aktuell: {$aktuellerBestand}, noch möglich: {$nochMoeglich}).");
                }

                $transaktion = AktienTransaktion::create([
                    'customer_id' => $kind->id,
                    'buisness_id' => $betrieb->id,
                    'typ'         => 'kauf',
                    'stueck'      => $stueck,
                    'kurs'        => $kurs,
                    'summe'       => $summe,
                    'boerse_rolle'=> 'haendler',
                    'notiz'       => "Kauf für {$kind->name}" . ($gebuehr > 0 ? " · Gebühr {$gebuehr} Radi" : ''),
                ]);

                // Bargeld-Tracking: Kaufpreis kommt physisch an der Kasse an
                BoerseKasse::buchen([
                    'typ'                   => 'kauf_einnahme',
                    'betrag'                => $summe,
                    'notiz'                 => "{$stueck} Anteile {$betrieb->name}, Kind: {$kind->name}",
                    'aktien_transaktion_id' => $transaktion->id,
                ]);

                // Börse behält die Kaufgebühr als Einnahme (Lohn für Angestellte)
                if ($gebuehr > 0) {
                    BoerseKasse::buchen([
                        'typ'                   => 'gebuehr_einnahme',
                        'betrag'                => $gebuehr,
                        'notiz'                 => "Kaufgebühr ({$stueck} Anteile {$betrieb->name}, Kind: {$kind->name})",
                        'aktien_transaktion_id' => $transaktion->id,
                    ]);
                }

                // Verknüpfte Kontoeinträge: Betrieb +summe, Börse −summe
                // Das Bargeld liegt physisch bei der Börse, gehört buchhalterisch dem Betrieb.
                $payBetrieb = Payment::create([
                    'customer_id' => $betrieb->id,
                    'amount'      => $summe,
                    'comment'     => "Börse: {$stueck} Anteile verkauft à {$kurs} Radi ({$kind->name})",
                    'user_id'     => auth()->id() ?? 1,
                ]);
                $payBoerse = Payment::create([
                    'customer_id' => $boerse->id,
                    'amount'      => -$summe,
                    'comment'     => "Börse: Anteilskauf {$stueck}× {$betrieb->name} à {$kurs} Radi ({$kind->name})",
                    'payment_id'  => $payBetrieb->id,
                    'user_id'     => auth()->id() ?? 1,
                ]);
                $payBetrieb->update(['payment_id' => $payBoerse->id]);

                $bestand = $bestandVorher ?? AktienBestand::firstOrCreate(
                    ['customer_id' => $kind->id, 'buisness_id' => $betrieb->id],
                    ['stueck' => 0]
                );
                $bestand->increment('stueck', $stueck);

                $ergebnis = ['kurs' => $kurs, 'summe' => $summe, 'gesamt' => $summe + $gebuehr];
            });
        } catch (BoerseException $e) {
            return back()->with(['type' => 'error', 'Meldung' => $e->getMessage()]);
        }

        $summe  = $ergebnis['summe'];
        $gesamt = $ergebnis['gesamt'];

        $hinweis = $gebuehr > 0
            ? "{$kind->name} hat {$stueck} Anteile am {$customer->name} gekauft. Bezahlt: {$gesamt} Radi bar ({$summe} + {$gebuehr} Gebühr). ✅"
            : "{$kind->name} hat {$stueck} Anteile am {$customer->name} gekauft ({$summe} Radi bar). ✅";

        return redirect('/boerse/handel')
            ->with(['type' => 'success', 'Meldung' => $hinweis]);
    }

    public function verkaufenForm(Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);
        $kassenstand    = BoerseKasse::kassenstand();
        $aufgabenStatus = $this->aufgabenStatus();
        return view('boerse.handel.verkaufen', compact('customer', 'kassenstand', 'aufgabenStatus'));
    }

    public function verkaufen(AktienVerkaufRequest $request, Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);

        $stueck = (int) $request->stueck;
        $kind   = Customer::findOrFail($request->customer_id);

        // Börsen-Konto muss eingerichtet sein
        $boerse = Customer::boerseBetrieb();
        if (!$boerse) {
            return back()->with(['type' => 'error',
                'Meldung' => '⚠️ Kein Börsen-Konto eingerichtet! Bitte den Admin kontaktieren.']);
        }

        // Vorab-Prüfung Bestand (schnelle UX; harte Prüfung folgt unter Lock)
        $vorab = AktienBestand::where('customer_id', $kind->id)
            ->where('buisness_id', $customer->id)->first();
        if (!$vorab || $vorab->stueck < $stueck) {
            return back()->with(['type' => 'error',
                'Meldung' => "{$kind->name} hat nicht genug Anteile! Bestand: " . ($vorab?->stueck ?? 0) . "."]);
        }

        // Handelssperre prüfen
        if ($kind->handelGesperrt()) {
            return back()->with(['type' => 'error',
                'Meldung' => "⛔ {$kind->name} ist vom Börsenhandel ausgeschlossen und darf keine Anteile verkaufen."]);
        }

        $minKurs = (int) config('bank.aktien.min_kurs', 4);
        $spread  = (int) config('bank.aktien.verkauf_spread', 1);
        $ergebnis = [];

        try {
            DB::transaction(function () use ($customer, $kind, $stueck, $boerse, $minKurs, $spread, &$ergebnis) {
                // Row-Lock serialisiert alle Trades dieses Betriebs.
                $betrieb = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
                $bestand = AktienBestand::where('customer_id', $kind->id)
                    ->where('buisness_id', $betrieb->id)->lockForUpdate()->first();

                if (!$bestand || $bestand->stueck < $stueck) {
                    throw new BoerseException(
                        "{$kind->name} hat nicht genug Anteile! Bestand: " . ($bestand?->stueck ?? 0) . ".");
                }

                // (A) Verkaufs-Spread: Börse zahlt pro Anteil `spread` Radi unter Kurs
                //     aus (nie unter Mindestkurs). Killt das Sofort-Arbitrage.
                $kurs        = (int) $betrieb->aktien_kurs;
                $normalKurs  = max($minKurs, $kurs - $spread);

                // (B) Einkaufspreis-Cap: Wer Anteile zu einem günstigeren Kurs als
                //     den aktuellen Mindestkurs gekauft hat, bekommt beim Verkauf
                //     höchstens seinen Einkaufspreis zurück — kein Windfall-Gewinn
                //     durch künstlich angehobene Kursuntergrenze.
                $avgKauf     = $kind->avgKaufKurs($betrieb->id);
                $verkaufKurs = ($avgKauf > 0 && $avgKauf < $normalKurs)
                    ? $avgKauf
                    : $normalKurs;
                $summe       = $stueck * $verkaufKurs;

                $kassenstand = BoerseKasse::kassenstand();
                if ($kassenstand < $summe) {
                    throw new BoerseException(
                        "Die Börse hat nicht genug Bargeld ({$kassenstand} Radi)! Bitte den Kassenwart fragen.");
                }

                if ($betrieb->balance < $summe) {
                    throw new BoerseException(
                        "Das Konto von {$betrieb->name} hat nicht genug Radi ({$betrieb->balance} Radi), um die Anteile zurückzukaufen!");
                }

                $transaktion = AktienTransaktion::create([
                    'customer_id' => $kind->id,
                    'buisness_id' => $betrieb->id,
                    'typ'         => 'verkauf',
                    'stueck'      => $stueck,
                    'kurs'        => $verkaufKurs,
                    'summe'       => $summe,
                    'boerse_rolle'=> 'haendler',
                    'notiz'       => $spread > 0 ? "Verkaufskurs {$verkaufKurs} (Kurs {$kurs} − {$spread} Spread)" : null,
                ]);

                // Bargeld-Tracking: Bargeld verlässt physisch die Kasse
                BoerseKasse::buchen([
                    'typ'                   => 'verkauf_auszahlung',
                    'betrag'                => $summe,
                    'notiz'                 => "{$stueck} Anteile {$betrieb->name}, Kind: {$kind->name}",
                    'aktien_transaktion_id' => $transaktion->id,
                ]);

                // Verknüpfte Kontoeinträge: Betrieb −summe, Börse +summe
                $payBoerse = Payment::create([
                    'customer_id' => $boerse->id,
                    'amount'      => $summe,
                    'comment'     => "Börse: Anteilsrückkauf {$stueck}× {$betrieb->name} à {$verkaufKurs} Radi ({$kind->name})",
                    'user_id'     => auth()->id() ?? 1,
                ]);
                $payBetrieb = Payment::create([
                    'customer_id' => $betrieb->id,
                    'amount'      => -$summe,
                    'comment'     => "Börse: {$stueck} Anteile zurückgekauft à {$verkaufKurs} Radi ({$kind->name})",
                    'payment_id'  => $payBoerse->id,
                    'user_id'     => auth()->id() ?? 1,
                ]);
                $payBoerse->update(['payment_id' => $payBetrieb->id]);

                $bestand->decrement('stueck', $stueck);

                $ergebnis = ['summe' => $summe, 'kurs' => $kurs, 'verkaufKurs' => $verkaufKurs,
                            'durch_einkauf_gedeckelt' => ($avgKauf > 0 && $avgKauf < $normalKurs)];
            });
        } catch (BoerseException $e) {
            return back()->with(['type' => 'error', 'Meldung' => $e->getMessage()]);
        }

        $summe    = $ergebnis['summe'];
        $vk       = $ergebnis['verkaufKurs'];
        $kursAkt  = $ergebnis['kurs'];
        if ($ergebnis['durch_einkauf_gedeckelt'] ?? false) {
            $spreadInfo = " (Verkaufskurs {$vk} Radi — gedeckelt auf Einkaufspreis)";
        } elseif ($spread > 0) {
            $spreadInfo = " (Verkaufskurs {$vk} Radi · {$spread} Radi Spread je Anteil)";
        } else {
            $spreadInfo = '';
        }

        return redirect('/boerse/handel')
            ->with(['type' => 'success',
                'Meldung' => "{$kind->name} hat {$stueck} Anteile verkauft und bekommt {$summe} Radi bar.{$spreadInfo} 💵"]);
    }

    public function rueckkaufForm(Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);
        $aufgabenStatus = $this->aufgabenStatus();
        return view('boerse.handel.rueckkauf', compact('customer', 'aufgabenStatus'));
    }

    public function rueckkauf(AktienRueckkaufRequest $request, Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);

        $stueck  = (int) $request->stueck;
        $kind    = Customer::findOrFail($request->customer_id);

        // Vorab-Prüfung (schnelle UX; harte Prüfung folgt unter Lock)
        $vorab = AktienBestand::where('customer_id', $kind->id)
            ->where('buisness_id', $customer->id)->first();
        if (!$vorab || $vorab->stueck < $stueck) {
            return back()->with(['type' => 'error',
                'Meldung' => "{$kind->name} hat nicht genug Anteile!"]);
        }

        $ergebnis = [];

        try {
            DB::transaction(function () use ($customer, $kind, $stueck, &$ergebnis) {
                $betrieb = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
                $bestand = AktienBestand::where('customer_id', $kind->id)
                    ->where('buisness_id', $betrieb->id)->lockForUpdate()->first();

                if (!$bestand || $bestand->stueck < $stueck) {
                    throw new BoerseException("{$kind->name} hat nicht genug Anteile!");
                }

                // Eigenbestand darf aktien_gesamt nicht überschreiten
                $verkauft = (int) AktienBestand::where('buisness_id', $betrieb->id)
                    ->lockForUpdate()->sum('stueck');
                $eigenNachher = (int) ($betrieb->aktien_gesamt ?? 0) - ($verkauft - $stueck);
                if ($eigenNachher > (int) $betrieb->aktien_gesamt) {
                    throw new BoerseException('Rückkauf würde den Eigenbestand über die Gesamtstückzahl treiben.');
                }

                $kurs  = (int) $betrieb->aktien_kurs;
                $summe = $stueck * $kurs;

                $transaktion = AktienTransaktion::create([
                    'customer_id' => $kind->id,
                    'buisness_id' => $betrieb->id,
                    'typ'         => 'rueckkauf',
                    'stueck'      => $stueck,
                    'kurs'        => $kurs,
                    'summe'       => $summe,
                    'boerse_rolle'=> 'haendler',
                    'notiz'       => "Rückkauf durch {$betrieb->name}",
                ]);

                // Einnahme vom Betrieb …
                BoerseKasse::buchen([
                    'typ'                   => 'rueckkauf_einnahme',
                    'betrag'                => $summe,
                    'notiz'                 => "{$stueck} Anteile, Betrieb: {$betrieb->name}, Kind: {$kind->name}",
                    'aktien_transaktion_id' => $transaktion->id,
                ]);
                // … sofortige Auszahlung an Kind (Netto 0)
                BoerseKasse::buchen([
                    'typ'                   => 'rueckkauf_auszahlung',
                    'betrag'                => $summe,
                    'notiz'                 => "{$stueck} Anteile, Betrieb: {$betrieb->name}, Kind: {$kind->name}",
                    'aktien_transaktion_id' => $transaktion->id,
                ]);

                $bestand->decrement('stueck', $stueck);

                $ergebnis = ['summe' => $summe];
            });
        } catch (BoerseException $e) {
            return back()->with(['type' => 'error', 'Meldung' => $e->getMessage()]);
        }

        $summe = $ergebnis['summe'];

        return redirect('/boerse/handel')
            ->with(['type' => 'success',
                'Meldung' => "{$customer->name} hat {$stueck} Anteile von {$kind->name} zurückgekauft. {$kind->name} bekommt {$summe} Radi bar. ✅"]);
    }
}

