<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminAktienAktivierenRequest;
use App\Http\Requests\AdminDividendeRequest;
use App\Models\AktienBestand;
use App\Models\AktienKurs;
use App\Models\AktienTransaktion;
use App\Models\BoerseAufgabenLog;
use App\Models\BoerseBeobachtung;
use App\Models\BoerseKasse;
use App\Models\Customer;
use App\Models\Payment;
use App\Services\BoerseAufgabenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBoerseController extends Controller
{
    public function index(BoerseAufgabenService $service)
    {
        $betriebe    = Customer::where('buisness', 1)->get();
        $alarmCount  = $service->alarmCount();
        $aufgabenStatus = $service->status();
        return view('admin.boerse.index', compact('betriebe', 'alarmCount', 'aufgabenStatus'));
    }

    public function aufgaben(BoerseAufgabenService $service)
    {
        $aufgabenStatus = $service->status();
        return view('admin.boerse.aufgaben', compact('aufgabenStatus'));
    }

    public function aktivierenForm()
    {
        $betriebe = Customer::where('buisness', 1)
            ->where('is_boerse', false)
            ->whereNull('aktien_gesamt')
            ->get();
        return view('admin.boerse.aktivieren', compact('betriebe'));
    }

    public function aktivieren(AdminAktienAktivierenRequest $request, BoerseAufgabenService $service)
    {
        $betrieb = Customer::findOrFail($request->customer_id);

        $betrieb->update([
            'aktien_gesamt'   => $request->aktien_gesamt,
            'aktien_kurs'     => $request->aktien_kurs,
            'aktien_startkurs'=> $request->aktien_kurs,
            'aktien_letzte_berechnung' => now(),
        ]);

        AktienKurs::create([
            'buisness_id' => $betrieb->id,
            'kurs'        => $request->aktien_kurs,
            'vorher'      => 0,
            'grund'       => 'Startkurs gesetzt',
            'created_at'  => now(),
        ]);

        $service->clearCache();

        return redirect('/admin/boerse')
            ->with(['type' => 'success',
                'Meldung' => "{$betrieb->name} ist jetzt an der Börse! Startkurs: {$request->aktien_kurs} Radi, Stückzahl: {$request->aktien_gesamt}."]);
    }

    public function deaktivieren(Customer $customer, BoerseAufgabenService $service)
    {
        $customer->update(['aktien_gesamt' => null, 'aktien_kurs' => null]);
        $service->clearCache();
        return redirect('/admin/boerse')
            ->with(['type' => 'warning', 'Meldung' => "{$customer->name} wurde aus der Börse entfernt."]);
    }

    public function kursForm(Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);
        $kursverlauf = AktienKurs::where('buisness_id', $customer->id)->latest('created_at')->take(10)->get();
        return view('admin.boerse.kurs', compact('customer', 'kursverlauf'));
    }

    public function kursUpdate(Request $request, Customer $customer, BoerseAufgabenService $service)
    {
        $request->validate([
            'kurs'  => 'required|integer|min:1',
            'grund' => 'required|string|max:120',
        ]);
        abort_unless($customer->hatAktien(), 404);

        $alterKurs = $customer->aktien_kurs ?? 0;
        $customer->update(['aktien_kurs' => $request->kurs]);

        AktienKurs::create([
            'buisness_id' => $customer->id,
            'kurs'        => $request->kurs,
            'vorher'      => $alterKurs,
            'grund'       => 'Admin-Korrektur: ' . $request->grund,
            'created_at'  => now(),
        ]);

        $service->clearCache();

        return redirect('/admin/boerse')
            ->with(['type' => 'success',
                'Meldung' => "Kurs für {$customer->name} auf {$request->kurs} Radi gesetzt."]);
    }

    public function dividendeForm()
    {
        $betriebe = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        return view('admin.boerse.dividende', compact('betriebe'));
    }

    public function dividende(AdminDividendeRequest $request)
    {
        $betrieb      = Customer::findOrFail($request->buisness_id);
        abort_unless($betrieb->hatAktien() && $betrieb->is_buisness(), 422);

        $radiProAnteil = $request->radi_pro_anteil;
        $bestaende     = AktienBestand::where('buisness_id', $betrieb->id)
            ->where('stueck', '>', 0)->with('kind')->get();

        if ($bestaende->isEmpty()) {
            return back()->with(['type' => 'warning',
                'Meldung' => 'Keine Anteilsinhaber — nichts zu überweisen.']);
        }

        $gesamtSumme = $bestaende->sum(fn($b) => $b->stueck * $radiProAnteil);

        // Betriebskontostand prüfen
        if ($betrieb->balance < $gesamtSumme) {
            // Anteilig kürzen
            $faktor = $gesamtSumme > 0 ? $betrieb->balance / $gesamtSumme : 0;
            $radiProAnteil = max(1, (int) floor($radiProAnteil * $faktor));
        }

        DB::transaction(function () use ($betrieb, $bestaende, $radiProAnteil) {
            foreach ($bestaende as $bestand) {
                $betrag = $bestand->stueck * $radiProAnteil;
                if ($betrag <= 0) continue;

                $kind = $bestand->kind;

                // Zeile B (Kind bekommt)
                $paymentB = Payment::create([
                    'customer_id' => $kind->id,
                    'amount'      => $betrag,
                    'comment'     => "Dividende {$betrieb->name}: {$bestand->stueck} Anteile × {$radiProAnteil} Radi",
                    'user_id'     => auth()->id() ?? 1,
                ]);

                // Zeile A (Betrieb zahlt)
                $paymentA = Payment::create([
                    'customer_id' => $betrieb->id,
                    'amount'      => -$betrag,
                    'comment'     => "Dividende {$kind->name}: {$bestand->stueck} Anteile × {$radiProAnteil} Radi",
                    'payment_id'  => $paymentB->id,
                    'user_id'     => auth()->id() ?? 1,
                ]);

                $paymentB->update(['payment_id' => $paymentA->id]);

                AktienTransaktion::create([
                    'customer_id'  => $kind->id,
                    'buisness_id'  => $betrieb->id,
                    'typ'          => 'dividende',
                    'stueck'       => 0,
                    'kurs'         => $betrieb->aktien_kurs,
                    'summe'        => $betrag,
                    'boerse_rolle' => 'admin',
                    'payment_id'   => $paymentB->id,
                    'notiz'        => "{$bestand->stueck} Anteile × {$radiProAnteil} Radi",
                ]);
            }
        });

        return redirect('/admin/boerse')
            ->with(['type' => 'success',
                'Meldung' => "Dividende für {$betrieb->name} ausgezahlt: {$radiProAnteil} Radi je Anteil. 🎉"]);
    }

    public function abschlussForm()
    {
        $betriebe        = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        $kassenstand     = BoerseKasse::kassenstand();
        $gesamtAuszahlung = AktienBestand::where('stueck', '>', 0)
            ->get()
            ->sum(function ($b) {
                return $b->stueck * ($b->betrieb->aktien_kurs ?? 0);
            });
        return view('admin.boerse.abschluss',
            compact('betriebe', 'kassenstand', 'gesamtAuszahlung'));
    }

    public function abschluss(Request $request, BoerseAufgabenService $service)
    {
        $kassenstand      = BoerseKasse::kassenstand();
        $bestaende        = AktienBestand::where('stueck', '>', 0)->with(['kind', 'betrieb'])->get();
        $gesamtAuszahlung = $bestaende->sum(fn($b) => $b->stueck * ($b->betrieb->aktien_kurs ?? 0));

        // Börsen-Konto muss eingerichtet sein
        $boerse = Customer::boerseBetrieb();
        if (!$boerse) {
            return back()->with(['type' => 'error',
                'Meldung' => '⚠️ Kein Börsen-Konto eingerichtet! Bitte erst unter Admin → Börse → Einstellungen ein Konto als Börse markieren.']);
        }

        if ($kassenstand < $gesamtAuszahlung) {
            return back()->with(['type' => 'error',
                'Meldung' => "Nicht genug Geld in der Börsen-Kasse! Benötigt: {$gesamtAuszahlung} Radi, vorhanden: {$kassenstand} Radi. Bitte Kassenwart Geld einlegen lassen."]);
        }

        // Prüfen ob alle Betriebe genug Kontostand haben
        $betriebeZuArmut = $bestaende
            ->groupBy('buisness_id')
            ->filter(function ($gruppe) {
                $betrieb   = $gruppe->first()->betrieb;
                $benoetigt = $gruppe->sum(fn($b) => $b->stueck * ($betrieb->aktien_kurs ?? 0));
                return $betrieb->balance < $benoetigt;
            });

        if ($betriebeZuArmut->isNotEmpty()) {
            $namen = $betriebeZuArmut->map(fn($g) => $g->first()->betrieb->name)->join(', ');
            return back()->with(['type' => 'error',
                'Meldung' => "Folgende Betriebe haben nicht genug Geld auf ihrem Konto für die Schlussabrechnung: {$namen}. Bitte erst Dividenden auszahlen oder Konten auffüllen."]);
        }

        DB::transaction(function () use ($boerse) {
            $bestaende = AktienBestand::where('stueck', '>', 0)->with(['kind', 'betrieb'])->get();
            foreach ($bestaende as $bestand) {
                $betrag = $bestand->stueck * ($bestand->betrieb->aktien_kurs ?? 0);
                if ($betrag <= 0) {
                    $bestand->update(['stueck' => 0]);
                    continue;
                }

                AktienTransaktion::create([
                    'customer_id'  => $bestand->customer_id,
                    'buisness_id'  => $bestand->buisness_id,
                    'typ'          => 'abschluss',
                    'stueck'       => $bestand->stueck,
                    'kurs'         => $bestand->betrieb->aktien_kurs ?? 0,
                    'summe'        => $betrag,
                    'boerse_rolle' => 'admin',
                    'notiz'        => 'Schlussabrechnung',
                ]);

                // Börsen-Kasse zahlt Bargeld an das Kind aus
                BoerseKasse::create([
                    'typ'        => 'abschluss_auszahlung',
                    'betrag'     => $betrag,
                    'notiz'      => "{$bestand->kind->name}: {$bestand->stueck} Anteile {$bestand->betrieb->name}",
                    'created_at' => now(),
                ]);

                // Verknüpfte Kontoeinträge: Betrieb −betrag, Börse +betrag
                // Der Betrieb zahlt die Anteile ab; die Börse empfängt sie buchhalterisch (zahlt bar aus).
                $payBoerse = Payment::create([
                    'customer_id' => $boerse->id,
                    'amount'      => $betrag,
                    'comment'     => "Schlussabrechnung: {$bestand->stueck} Anteile {$bestand->betrieb->name} von {$bestand->kind->name}",
                    'user_id'     => auth()->id() ?? 1,
                ]);
                $payBetrieb = Payment::create([
                    'customer_id' => $bestand->buisness_id,
                    'amount'      => -$betrag,
                    'comment'     => "Schlussabrechnung Börse: {$bestand->stueck} Anteile à {$bestand->betrieb->aktien_kurs} Radi an {$bestand->kind->name}",
                    'payment_id'  => $payBoerse->id,
                    'user_id'     => auth()->id() ?? 1,
                ]);
                $payBoerse->update(['payment_id' => $payBetrieb->id]);

                $bestand->update(['stueck' => 0]);
            }
        });

        $service->clearCache();

        return redirect('/admin/boerse')
            ->with(['type' => 'success',
                'Meldung' => "✅ Schlussabrechnung abgeschlossen! Bitte Druckliste ausdrucken und Bargeld auszahlen."]);
    }

    public function pinForm()
    {
        $pin            = config('bank.aktien.boerse_pin');
        $boerseBetrieb  = Customer::boerseBetrieb();
        $betriebe       = Customer::where('buisness', 1)->whereNotNull('betrieb_pin')->get();
        return view('admin.boerse.pin', compact('pin', 'boerseBetrieb', 'betriebe'));
    }

    public function pinUpdate(Request $request)
    {
        $request->validate(['pin' => 'required|string|min:4|max:20']);
        // PIN in .env schreiben
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);
        if (str_contains($content, 'BOERSE_PIN=')) {
            $content = preg_replace('/BOERSE_PIN=.*/', 'BOERSE_PIN=' . $request->pin, $content);
        } else {
            $content .= "\nBOERSE_PIN={$request->pin}";
        }
        file_put_contents($envPath, $content);

        return redirect('/admin/boerse/pin')
            ->with(['type' => 'success', 'Meldung' => 'Börsen-PIN wurde auf "' . $request->pin . '" gesetzt.']);
    }

    /** Markiert einen bestehenden Betrieb als "die Börse" (genau einer). */
    public function setBoerseBetrieb(Request $request)
    {
        $request->validate(['customer_id' => 'required|integer|exists:customers,id']);

        // Bestehende Markierung zurücksetzen
        Customer::where('is_boerse', true)->update(['is_boerse' => false]);
        // Neuen markieren
        $c = Customer::findOrFail($request->customer_id);
        $c->update(['is_boerse' => true]);

        return redirect('/admin/boerse/pin')
            ->with(['type' => 'success',
                'Meldung' => "{$c->name} ist jetzt als Börse markiert. Mitarbeiter kommen mit dem Betriebs-PIN ins Börsen-Frontend."]);
    }

    public function clearBoerseBetrieb()
    {
        Customer::where('is_boerse', true)->update(['is_boerse' => false]);
        return redirect('/admin/boerse/pin')
            ->with(['type' => 'warning', 'Meldung' => 'Börse-Markierung entfernt.']);
    }

    public function bericht()
    {
        $betriebe       = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        $transaktionen  = AktienTransaktion::with(['kind', 'betrieb'])
            ->whereDate('created_at', today())->latest()->get();
        $kassenstand    = BoerseKasse::kassenstand();
        $kassenbewegungen = BoerseKasse::whereDate('created_at', today())->latest()->get();
        $gebuehrSumme   = BoerseKasse::where('typ', 'gebuehr_einnahme')
            ->whereDate('created_at', today())->sum('betrag');
        return view('admin.boerse.bericht',
            compact('betriebe', 'transaktionen', 'kassenstand', 'kassenbewegungen', 'gebuehrSumme'));
    }
}



