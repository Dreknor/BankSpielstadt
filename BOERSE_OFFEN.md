# Börse – Umsetzungsstatus & offene Aufgaben
Stand: 2026-06-22 · Erstellt automatisch nach Code-Review

---

## ✅ Bereits umgesetzt

| # | Datei | Was |
|---|-------|-----|
| 1 | `database/migrations/2026_06_22_150001_change_payments_amount_to_integer.php` | `payments.amount` FLOAT → INT (Phase 1) |
| 2 | `database/migrations/2026_06_22_150002_add_boerse_indexes_and_saldo.php` | Indizes auf `aktien_kurse`, `aktien_transaktionen`, `boerse_kasse`; `saldo_nach`-Spalte (Phase 2) |
| 3 | `app/Models/Payment.php` | `$casts['amount'] = 'integer'` |
| 4 | `app/Models/BoerseKasse.php` | Typ-Konstanten `EINNAHME_TYPEN`/`AUSGABE_TYPEN`, zentrale `buchen()`-Methode mit Laufsaldo, `saldo_nach` in fillable + casts |
| 5 | `app/Models/AktienTransaktion.php` | Integer-Casts für `stueck`, `kurs`, `summe` |
| 6 | `app/Models/AktienBestand.php` | Integer-Cast für `stueck` |
| 7 | `app/Models/AktienKurs.php` | Integer-Casts für `kurs`, `vorher` |
| 8 | `config/bank.php` | Neue Parameter: `verkauf_spread`, `kurs_nur_fixing`, `fixing_von`, `fixing_bis`, `arbitrage_schnellverkauf_min`, `arbitrage_min_gewinn` |
| 9 | `app/Exceptions/BoerseException.php` | Neue Börsen-Fachausnahme (Domain Exception) |
| 10 | `app/Models/Customer.php` | Neue Methode `operativerTagesgewinn()` (Dividendenbasis ohne Börsen-Kapitalflüsse) |
| 11 | `app/Http/Controllers/Boerse/BoerseHandelController.php` | `kaufen()`, `verkaufen()`, `rueckkauf()` mit Row-Lock, Kurslesung in DB-Transaktion, Verkaufs-Spread, `BoerseKasse::buchen()`, `BoerseException` |

---

## ❌ Noch offen (in Prioritätsreihenfolge)

### PRIORITÄT 1 – Kursbildung entkoppeln (Phase 3a)
**Datei:** `app/Http/Controllers/Boerse/BoerseErfassungController.php`

**Problem:** `store()` ändert `customers.aktien_kurs` und schreibt `aktien_kurse` **sofort** bei jeder
Beobachtung – unabhängig von `config('bank.aktien.kurs_nur_fixing')`. Das widerspricht
Entscheidung C (Kurs nur per Fixing).

**Lösung:** Wenn `kurs_nur_fixing === true`, nur `boerse_beobachtungen` speichern und
**keinen** Kurs schreiben. Die Feedback-Meldung muss entsprechend geändert werden.

```php
// store() – neuer Block wenn kurs_nur_fixing aktiv:
if (config('bank.aktien.kurs_nur_fixing', true)) {
    // Nur sammeln – kein Kurs-Update hier
    $service->clearCache();
    return redirect('/boerse/erfassung')
        ->with(['type' => 'success',
            'Meldung' => "✅ Beobachtung für {$customer->name} gespeichert: {$request->angestellte} Angestellte. "
                       . "Kurs wird beim nächsten Fixing angepasst."]);
}
// ... bestehender Kursberechnungs-Block bleibt für kurs_nur_fixing=false
```

---

### PRIORITÄT 2 – Dividende auf echten Gewinn umstellen (Phase 3b)
**Datei:** `app/Http/Controllers/AdminBoerseController.php`

**Problem:** `dividendeForm()` und `dividende()` verwenden noch `$betrieb->daily_balance()`,
das Börsen-Kapitalflüsse enthält. Dadurch wird die Dividende auf eingesammeltes
Anlegergeld statt auf echten Umsatz gezahlt.

**Lösung:** Beide Stellen auf `$betrieb->operativerTagesgewinn()` umstellen.

```php
// dividendeForm() und dividende() – beide Stellen:
$tagesgewinn = max(0, $betrieb->operativerTagesgewinn());
// statt:
// $tagesgewinn = max(0, $betrieb->daily_balance());
```

---

### PRIORITÄT 3 – `BoerseKasse::buchen()` überall verwenden (Phase 2 – Rest)
**Dateien:**
- `app/Http/Controllers/Boerse/BoerseKasseController.php` – `einlage()` und `entnahme()`
- `app/Http/Controllers/AdminBoerseController.php` – `abschluss()`

**Problem:** Diese drei Stellen nutzen noch direkt `BoerseKasse::create()`, ohne `saldo_nach`
zu befüllen. Das unterbricht die Laufsaldo-Kette.

**Lösung:**
```php
// BoerseKasseController::einlage() – ersetzen:
BoerseKasse::buchen([
    'typ'             => 'einlage',
    'betrag'          => (int) $request->betrag,
    'notiz'           => ($request->notiz ?: 'Bareinlage') . ' – ' . $person->name,
    'ausgefuehrt_von' => $person->id,
]);

// BoerseKasseController::entnahme() – ersetzen:
BoerseKasse::buchen([
    'typ'             => 'entnahme',
    'betrag'          => (int) $request->betrag,
    'notiz'           => ($request->notiz ?: 'Entnahme') . ' – ' . $person->name,
    'ausgefuehrt_von' => $person->id,
]);

// AdminBoerseController::abschluss() – ersetzen:
BoerseKasse::buchen([
    'typ'    => 'abschluss_auszahlung',
    'betrag' => $betrag,
    'notiz'  => "{$bestand->kind->name}: {$bestand->stueck} Anteile {$bestand->betrieb->name}",
]);
```

---

### PRIORITÄT 4 – Scheduler-Fenster aus Config lesen (Phase 3c)
**Datei:** `app/Console/Kernel.php`

**Problem:** `between('08:00', '12:00')` ist hardcodiert; der neue `fixing_von`/`fixing_bis`-
Parameter in `bank.php` wird nicht verwendet. Außerdem zeigen die SQL-Daten Handel ab 06:00,
das Fenster ist also zu eng.

**Lösung:**
```php
protected function schedule(Schedule $schedule)
{
    $von = config('bank.aktien.fixing_von', '06:00');
    $bis = config('bank.aktien.fixing_bis', '16:00');
    $schedule->command('aktien:kurs-berechnen')
        ->weekdays()
        ->everyThirtyMinutes()
        ->between($von, $bis);
}
```

---

### PRIORITÄT 5 – Console Command: Bestands-Abgleich (Phase 4)
**Neue Datei:** `app/Console/Commands/AktienBestaendeAbgleichen.php`
**Signature:** `aktien:reconcile-bestaende`

Rechnet den Soll-Bestand aus `kauf − verkauf − rueckkauf − abschluss` je
`(customer_id, buisness_id)` neu und vergleicht mit `aktien_bestaende.stueck`.
Meldet Abweichungen (Report-Modus) und korrigiert auf Wunsch (Flag `--korrigieren`).

**Kernlogik:**
```php
// Soll-Bestand aus Transaktionen berechnen
$soll = AktienTransaktion::where('customer_id', $bestand->customer_id)
    ->where('buisness_id', $bestand->buisness_id)
    ->whereNull('deleted_at')
    ->selectRaw("
        SUM(CASE WHEN typ IN ('kauf') THEN stueck ELSE 0 END) -
        SUM(CASE WHEN typ IN ('verkauf','rueckkauf','abschluss') THEN stueck ELSE 0 END)
        AS soll
    ")->value('soll') ?? 0;

$ist = $bestand->stueck;
if ($soll != $ist) {
    // Abweichung melden / korrigieren
}
```

---

### PRIORITÄT 6 – Console Command: Kassen-Abgleich (Phase 4)
**Neue Datei:** `app/Console/Commands/BoerseKasseAbgleichen.php`
**Signature:** `aktien:reconcile-kasse`

Berechnet den Soll-Kassenstand aus allen `boerse_kasse`-Einträgen und vergleicht mit
dem letzten `saldo_nach`. Rückberechnet `saldo_nach` für alle historischen Zeilen
(einmalige Datenmigration). Erstellt auf Wunsch eine dokumentierte `einlage`/`entnahme`-
Ausgleichsbuchung für verbleibende Differenzen.

---

### PRIORITÄT 7 – Console Command: Arbitrage-Auswertung (Phase 4)
**Neue Datei:** `app/Console/Commands/AktienArbitrageReport.php`
**Signature:** `aktien:arbitrage-report {--datum=heute}`

Analysiert alle Kauf-Verkauf-Paare je Kind aus `aktien_transaktionen` und meldet:
- Kauf- und Verkaufskurs je Paar
- Haltedauer in Minuten
- Erzielter Kursgewinn pro Anteil (verkauf_kurs − kauf_kurs)
- Gesamtgewinn aus der Kursdifferenz
- Klassifizierung: `schnellverkauf` wenn Haltedauer < `arbitrage_schnellverkauf_min` Minuten
- Qualifizierung: `verdaechtig` wenn Kursgewinn ≥ `arbitrage_min_gewinn` Radi/Anteil

**Bekannte Fälle aus dem Dump (bereits identifiziert):**
| Kind (ID) | Betrieb | Kauf | Verkauf | Haltezeit | Δ Kurs | Gewinn |
|-----------|---------|------|---------|-----------|--------|--------|
| Jurian Bolks (135) | Kaufhaus (265) | 1 Radi | 2 Radi | ~92 min | +1 | +9 Radi |
| Noah Bretschneider (216) | Kaufhaus (265) | 1 Radi | 2 Radi | ~10 min | +1 | +7 Radi |
| Noah Bretschneider (216) | Sweet Society (298) | 1 Radi | 2 Radi | ~40 sec | +1 | +9 Radi |
| Leopold Hanschmann (155) | Go Asia (292) | 1 Radi | 2 Radi | ~54 sec | +1 | +26 Radi |
| Noah Bretschneider (216) | Kino Klein Radebeul (291) | 1 Radi | 1 Radi | ~51 sec | 0 | 0 (kein Gewinn, aber auffällig) |

---

### PRIORITÄT 8 – Admin-Seite: Arbitrage-Auswertung (Phase 4)
**Neue Dateien:**
- `app/Http/Controllers/AdminBoerseController.php` → neue Methode `arbitrageAuswertung()`
- `resources/views/admin/boerse/arbitrage.blade.php`
- `routes/web.php` → Route `GET /admin/boerse/arbitrage`

Die Seite zeigt die Ergebnisse des `aktien:arbitrage-report` als Admin-Tabelle:
- Sortierbar nach Gewinn (absteigend)
- Farbige Markierung: rot = verdächtig, orange = Schnellverkauf
- Summe der unsauberen Gewinne je Kind
- Exportierbar als Druckansicht

---

### PRIORITÄT 9 – Rückberechnung saldo_nach (Phase 4 – Datenmigration)
**Neue Datei:** `database/migrations/2026_06_22_150003_backfill_boerse_kasse_saldo_nach.php`

Befüllt `saldo_nach` für alle bestehenden `boerse_kasse`-Zeilen chronologisch
(einmalige Ausführung, idempotent über `WHERE saldo_nach IS NULL`).

```php
// Pseudocode für die Migration:
$saldo = 0;
BoerseKasse::orderBy('id')->each(function ($zeile) use (&$saldo) {
    if (in_array($zeile->typ, BoerseKasse::AUSGABE_TYPEN)) {
        $saldo -= $zeile->betrag;
    } else {
        $saldo += $zeile->betrag;
    }
    $zeile->update(['saldo_nach' => $saldo]);
});
```

---

### PRIORITÄT 10 – customers.aktien_kurs mit aktien_kurse synchronisieren (Phase 4)
**Betrifft:** `aktien:reconcile-bestaende` oder separates einmaliges Artisan-Kommando

Nach Aktivierung von `kurs_nur_fixing=true` ist `customers.aktien_kurs` die einzige
vom Handel gelesene Wahrheitsquelle. Eventuelle Divergenzen zum letzten
`aktien_kurse`-Eintrag müssen einmalig korrigiert werden:

```sql
-- SQL zur Überprüfung:
SELECT c.id, c.name, c.aktien_kurs AS kurs_customers,
       k.kurs AS kurs_letzte_berechnung
FROM customers c
JOIN aktien_kurse k ON k.id = (
    SELECT id FROM aktien_kurse WHERE buisness_id = c.id ORDER BY created_at DESC LIMIT 1
)
WHERE c.aktien_gesamt IS NOT NULL
  AND c.aktien_kurs != k.kurs;
```

---

## Zusammenfassung offener Punkte

| Priorität | Datei(en) | Art | Aufwand |
|-----------|-----------|-----|---------|
| 1 | `BoerseErfassungController.php` | Code-Änderung | 10 min |
| 2 | `AdminBoerseController.php` | Code-Änderung (2 Stellen) | 5 min |
| 3 | `BoerseKasseController.php`, `AdminBoerseController.php` | Code-Änderung (3 Stellen) | 10 min |
| 4 | `Console/Kernel.php` | Code-Änderung | 5 min |
| 5 | `Commands/AktienBestaendeAbgleichen.php` | Neue Datei | 30 min |
| 6 | `Commands/BoerseKasseAbgleichen.php` | Neue Datei | 30 min |
| 7 | `Commands/AktienArbitrageReport.php` | Neue Datei | 45 min |
| 8 | Controller-Methode + Route + View | Neue Dateien | 60 min |
| 9 | Migration `...backfill_saldo_nach.php` | Neue Datei | 20 min |
| 10 | Einmaliges SQL / Command | Datenmigration | 15 min |

**Prio 1–4 sind reine Code-Änderungen in bestehenden Dateien und sollten zuerst erledigt werden.**
**Prio 5–7 sind neue Console-Commands für die Datenkompensation (Phase 4).**
**Prio 8 ist die Admin-Auswertungsseite (Phase 4 – Ansicht für Administratoren).**
**Prio 9–10 sind einmalige Datenmigrations-Schritte.**
