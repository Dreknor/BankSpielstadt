# Konzept: Radi-Börse – Aktienhandel der Betriebe

**Projekt:** Kinderspielstadt-Bank
**Status:** Konzept — Entscheidungen getroffen, bereit zur Implementierung
**Datum:** 2026-05-17
**Zielgruppe der Bedienung:** 4 Schüler-Angestellte der Börse + Admin (Lehrkraft)

---

## Getroffene Entscheidungen

| # | Frage | Entscheidung |
|---|-------|--------------|
| 1 | Stückelung | Per `config/bank.php`, **Default 100** Anteile je Betrieb |
| 2 | Startkurs | Vom **Admin je Betrieb** individuell bei Freischaltung festgelegt |
| 3 | Dividende | Per **Admin-Klick** (Lehrkraft behält Kontrolle) |
| 4 | Aktien am Ende | Anteile müssen **nicht** vor Spielende verkauft werden — Admin-Klick „Schlussabrechnung" löst alle Anteile zum letzten Kurs bar aus |
| 5 | Kursaktualisierung | **Stündlich** via Laravel-Scheduler + manuelle Admin-Korrektur; **Angestelltenanzahl** des Betriebs fließt in Berechnung ein |
| 6 | Betrieb-Rückkauf | Betriebe **dürfen** eigene Anteile zurückkaufen (Händler erfasst es) |
| 7 | Portfolio-Sichtbarkeit | **Nicht** sichtbar — kein Einblick in fremde Bestände, keine Rangliste |
| 8 | Zahlungsweg Kauf/Verkauf | **Bargeld** — kein Bank-Payment; Käufer zahlt bar an Börsen-Schalter |
| 9 | Zahlungsweg Dividende | **Auf das Kinderkonto** — zwei verknüpfte `Payment`-Zeilen (Betriebskonto → Kinderkonto) |
| 10 | Getrennter Bereich | Eigene Middleware `hasBoerse`, eigenes Layout, eigene Session `boerse` — **komplett getrennt** von Bank und Betriebs-Kasse |
| 11 | Zugangsprinzip Börse | **Gemeinsamer PIN** für alle 4 Angestellten — alle Funktionen für alle zugänglich, kein Rollen-Login |
| 12 | Hilfe-Seite | Eigene `/boerse/hilfe`-Seite listet alle 4 Jobs mit Aufgaben-Checkliste; als DIN-A5-Jobkarten druckbar |
| 13 | Aufgaben-Überwachung | System prüft automatisch, ob Pflichtaufgaben im Zeitfenster erledigt wurden; warnt Börsen-Dashboard + Admin-Badge |

---

## 1. Idee in einem Satz

Die Radi-Börse ist ein eigenständiger **Betrieb** in der Kinderspielstadt, der von
4 Schüler-Angestellten betrieben wird. Kinder können dort mit **Bargeld** Anteile an
anderen Betrieben kaufen, der Kurs steigt oder fällt stündlich je nach Kassenumsatz und
Besetzung des Betriebs, und die Lehrkraft schüttet nach einem erfolgreichen Tag
**Dividenden** auf die Bankkonten der Anteilsinhaber aus.

### Was Kinder dabei lernen

- **Sparen vs. Investieren:** Geld auf dem Konto bleibt sicher, Anteile können mehr wert werden — aber auch weniger.
- **Angebot & Nachfrage:** Wenn ein Betrieb voll besetzt und gut besucht ist, steigt sein Wert.
- **Beobachten lohnt sich:** Der Kurs hängt von echten Ereignissen ab, die man vor Ort sehen kann.
- **Geduld & Risiko:** Wer früh verkauft, bekommt vielleicht weniger. Wer wartet, kann die Dividende kassieren.
- **Teamarbeit:** 4 Angestellte mit je einer Aufgabe müssen zusammenarbeiten, damit die Börse läuft.

---

## 2. Leitprinzipien

1. **Kindgerecht zuerst.** Keine englischen Begriffe: „Anteil" statt „Aktie/Stock", „Wert" statt „Kurs/Preis", „Gewinnausschüttung" oder einfach „Geschenk" statt „Dividende". Smileys statt Charts. Große Buttons, Erklär-Tooltips überall.
2. **Getrennte Bereiche bleiben getrennt.** Die Börse mischt sich **nicht** in Bank (`/`) oder Betriebs-Kasse (`/betrieb/*`) ein.
3. **Bargeld-Ledger für Käufe/Verkäufe.** Aktienkäufe und -verkäufe erzeugen **keine** `Payment`-Zeilen, sondern `boerse_kasse`-Zeilen. Die Dividende ist die einzige Geldlinie durch das Bank-Ledger.
4. **Append-only Ledger.** `aktien_transaktionen` und `boerse_kasse` werden nie geändert — nur neue Zeilen.
5. **Nachvollziehbarkeit.** Jede Kursänderung landet mit Zeitstempel + Grund in `aktien_kurse`.
6. **Keine negativen Kassenstände.** Vor jeder Auszahlung Börsen-Kassenstand prüfen.
7. **Ganzzahlige Radi.** Kurs, Stückzahl, Dividende — alles ganze Zahlen.

---

## 3. Was ist ein „Anteil"?

- Ein Anteil ist ein **Stück des Betriebs** (`Customer` mit `buisness = 1`).
- Jeder Betrieb, der mitmacht, bekommt eine **Gesamtstückzahl** (Default: `config('bank.aktien.standard_gesamt', 100)`), die der Admin beim Freischalten festlegt.
- Anteile, die noch niemandem gehören, sind der **Eigenbestand** des Betriebs.
- Ein Kind besitzt immer eine ganze Zahl an Anteilen.

### Kindgerechte Erklärung in der UI

> „Das Eiscafé hat **100 Anteilsscheine**. Davon gehören schon 15 Kindern.
> Du kannst dir einen oder mehrere kaufen — direkt hier an der Börse, mit deinem Bargeld.
> Wenn das Eiscafé heute viel verkauft, werden deine Anteilsscheine **mehr wert** sein!"

---

## 4. Die Börse als Betrieb — 4 Angestellte mit Rollen

Die Radi-Börse hat einen festen Schalter in der Spielstadt und **4 Angestellte**,
von denen jeder eine klar definierte Aufgabe hat. Alle 4 loggen sich mit dem
**gleichen Börsen-PIN** ein und sehen alle Funktionen — es gibt keine Rollen-Trennung.
Die **Hilfe-Seite** (Abschnitt 5.4) erklärt jedem Kind, was sein Job ist.
Das System **überwacht automatisch**, ob die Pflichtaufgaben erledigt wurden (Abschnitt 5.5).

### Rolle 1: Der Händler 🤝

**Aufgabe:** Käufe und Verkäufe am Schalter erfassen.

| Tätigkeit | Wann | Details |
|-----------|------|---------|
| Kind kauft Anteile | Auf Anfrage | Kind bringt Bargeld, Händler erfasst Betrieb + Stückzahl, nimmt Geld entgegen |
| Kind verkauft Anteile | Auf Anfrage | Händler prüft Bestand + Börsenkasse, zahlt Bargeld aus |
| Betrieb kauft Anteile zurück | Auf Anfrage | Betrieb bringt Bargeld, Händler zahlt Kind aus, Anteil geht zurück an Betrieb |
| Kassenstand abgleichen | Stündlich | Übersicht „Haben wir genug Bargeld?" |

### Rolle 2: Der Kursbeobachter 🔭

**Aufgabe:** Stündlich die Betriebe beobachten und Daten erfassen — die wichtigste analytische Arbeit!

| Tätigkeit | Wann | Details |
|-----------|------|---------|
| Angestelltenzahl erfassen | Jede Stunde | Geht zu jedem Betrieb, zählt arbeitende Kinder, trägt Zahl ein |
| Qualitätseindruck erfassen | Jede Stunde | Kurze Einschätzung: „sehr voll", „normal", „leer" (optional) |
| Kursvorschau anzeigen | Nach Erfassung | „Wenn jetzt berechnet würde, wäre der Kurs X Radi" |

> „Du gehst jetzt zur Bäckerei und schaust: Wie viele Kinder arbeiten dort gerade?
> Das trägst du hier ein. Danach berechnet unser System, ob der Kurs steigt oder fällt."

### Rolle 3: Der Kassenwart 💰

**Aufgabe:** Bargeldbestand der Börse im Blick behalten — die Börse darf nie pleite gehen.

| Tätigkeit | Wann | Details |
|-----------|------|---------|
| Kassenstand prüfen + bestätigen | Stündlich | Klick auf „Kassenstand bestätigen" → grüne Kachel im Dashboard |
| Einlage erfassen | Nach Admin-Einzahlung | Wenn Lehrkraft Startkapital einlegt |
| Entnahme erfassen | Auf Anweisung | Tagesende, Geld zur Bank |
| Kassenabschluss drucken | Tagesende | Übersicht aller Einnahmen / Auszahlungen / Restbestand |

### Rolle 4: Der Börsenwart 📰

**Aufgabe:** Kursübersicht aktuell halten und als Auskunftsperson bereitstehen.

| Tätigkeit | Wann | Details |
|-----------|------|---------|
| Kurstafel ausdrucken + aufhängen | Nach jeder Kursberechnung | Druckansicht öffnen → Klick „Kurstafel ist ausgehängt" |
| Auskunft geben | Auf Anfrage | „Was kostet ein Anteil vom Eiscafé?" |
| Kursverlauf lesen | Jederzeit | Alle historischen Kurse aller Betriebe (nur lesen) |
| Tagesbericht ausgeben | Tagesende | Zusammenfassung Kursbewegungen, Dividenden, Käufe |

---

## 5. Zugangssystem (Login)

### 5.1 Prinzip

- Alle 4 Angestellten teilen sich **einen gemeinsamen Börsen-PIN** — kein User-Account, keine Rollen-Trennung.
- Nach dem Login sieht jeder **alle** Funktionen (Handel, Erfassung, Kasse, Kurse).
- Die Session-Variable `boerse` (Timestamp des Logins) markiert den eingeloggten Zustand.
- Middleware `HasBoerse` schützt alle `/boerse/*`-Routen (außer Login/Logout und Hilfe-Seite).
- **Komplett getrennt** von Bank-Auth und Betriebs-Kasse.

### 5.2 Login-Flow

```
GET  /boerse/login      → PIN-Eingabefeld (ein Feld, großer Button)
POST /boerse/login      → PIN prüfen → session('boerse') = now() → redirect /boerse
GET  /boerse/logout     → session vergessen → redirect /boerse/login
```

### 5.3 Börsen-PIN

Gespeichert in `config/bank.php` (via `.env` rotierbar, z. B. täglich neu):

```php
'aktien' => [
    'boerse_pin' => env('BOERSE_PIN', '1234'),
    // ...
],
```

Admin kann den PIN unter `/admin/boerse/pin` ändern. Der bisherige PIN wird sofort ungültig.

---

### 5.4 Hilfe-Seite (`/boerse/hilfe`)

Die Hilfe-Seite ist der **erste Anlaufpunkt** für alle 4 Angestellten. Vom Dashboard aus
jederzeit mit einem großen „❓ Hilfe"-Button erreichbar und auch **ohne Login** zugänglich.

#### Inhalt der Hilfe-Seite

```
┌──────────────────────────────────────────────────────────────────┐
│  📋 Was mache ich heute an der Börse?                             │
├──────────────────────────────────────────────────────────────────┤
│  🤝 Job 1: Der Händler                                            │
│  ✅ Wenn jemand Anteile kaufen will → „Kauf erfassen"             │
│  ✅ Wenn jemand Anteile verkaufen will → „Verkauf erfassen"       │
│  ✅ Wenn ein Betrieb Anteile zurückkauft → „Rückkauf erfassen"    │
│  ⏰ Stündlich: Schau, ob die Kasse stimmt                         │
├──────────────────────────────────────────────────────────────────┤
│  🔭 Job 2: Der Kursbeobachter                                     │
│  ✅ Jede Stunde: Geh zu jedem Betrieb, zähle Mitarbeiter          │
│  ✅ Trag die Zahl ein: „Beobachtung erfassen"                     │
│  ✅ Füge einen kurzen Eindruck ein: „voll", „ruhig", „leer"       │
├──────────────────────────────────────────────────────────────────┤
│  💰 Job 3: Der Kassenwart                                         │
│  ✅ Jede Stunde: Kassenstand prüfen → „Kassenstand bestätigen"    │
│  ✅ Wenn Geld eingelegt wird: „Einlage erfassen"                   │
│  ✅ Wenn zu wenig Geld → Lehrkraft informieren!                    │
│  ✅ Am Ende: Kassenabschluss drucken                               │
├──────────────────────────────────────────────────────────────────┤
│  📰 Job 4: Der Börsenwart                                         │
│  ✅ Nach jeder Kursberechnung: Kurstafel drucken + aufhängen       │
│  ✅ Dann klicken: „Kurstafel ist ausgehängt" ← wichtig!           │
│  ✅ Fragen von Kindern beantworten                                 │
│  ✅ Am Ende: Tagesbericht drucken                                  │
└──────────────────────────────────────────────────────────────────┘
         [ 🖨️ Jobkarten drucken (DIN A5) ]   [ 🔙 Zurück ]
```

#### Druckansicht Jobkarten (`/boerse/hilfe/drucken`)

Vier DIN-A5-Karten auf einer DIN-A4-Seite — jede: Job-Titel + Icon, Aufgaben als nummerierte Liste.
Admin druckt sie vor Spielbeginn aus und verteilt sie an die 4 Schüler.

---

### 5.5 Aufgaben-Überwachung

#### Was wird überwacht?

| Aufgabe | Auslöser (gilt als erledigt wenn…) | Warnzeit | Alarmzeit |
|---------|--------------------------------------|----------|-----------|
| **Beobachtung** | Neuer `boerse_beobachtungen`-Eintrag für **alle** aktiven Betriebe | 75 Min | 90 Min |
| **Kassenkontrolle** | Klick „Kassenstand bestätigen" → `boerse_aufgaben_log` | 90 Min | 120 Min |
| **Kurstafel** | Klick „Kurstafel ist ausgehängt" oder Aufruf der Druckseite → `boerse_aufgaben_log` | 15 Min nach Kursberechnung | 30 Min |

Alle Schwellwerte konfigurierbar in `config('bank.aktien.aufgaben.*')`.

#### Stufen

| Stufe | Farbe | Was passiert |
|-------|-------|--------------|
| ✅ OK | Grün | Aufgabe rechtzeitig erledigt |
| ⚠️ Warnung | Gelb | Banner auf `/boerse` Dashboard |
| 🚨 Alarm | Rot | Banner + **roter Badge auf `/admin/boerse`** im Admin-Menü |

#### Anzeige auf dem Börsen-Dashboard

Drei Status-Kacheln permanent unter dem Header (auf **jeder** Börsen-Seite sichtbar):

```
┌────────────────┐  ┌─────────────────────┐  ┌──────────────────┐
│ 🔭 Beobachtung │  │ 💰 Kassenkontrolle  │  │ 📰 Kurstafel     │
│ ✅ vor 12 Min  │  │ ⚠️ vor 95 Min!       │  │ ✅ vor 5 Min      │
│                │  │ Bitte jetzt prüfen! │  │                  │
└────────────────┘  └─────────────────────┘  └──────────────────┘
```

Kacheln sind anklickbar → führen direkt zur zugehörigen Funktion.

#### Admin-Badge

```
Börse  🔴 2
```

Erscheint im Admin-Navigationsmenü neben „Börse" wenn ≥ 1 Aufgabe im Alarm-Zustand.
Auf `/admin/boerse/aufgaben` sieht der Admin eine Tabelle aller überfälligen Aufgaben.

#### Technische Umsetzung

- **Kein eigener Scheduler** — Prüfung on-demand beim Laden jedes Börsen-Views via `BoerseAufgabenService::status()`.
- Ergebnis für 2 Minuten im Laravel-Cache gespeichert (kein DB-Spam).
- Admin-Badge: frisch berechnet pro Request (günstig: max. 3 Timestamps).

---

## 6. Kursbildung

### 6.1 Vorgaben

- Kursberechnung läuft **stündlich** automatisch (Laravel Scheduler →
  Artisan-Command `php artisan aktien:kurs-berechnen`).
- Grundlage: Kassenumsatz **seit letzter Berechnung** + **zuletzt erfasste Angestelltenzahl**.
- Mindestkurs: `config('bank.aktien.min_kurs', 1)` Radi.
- Maximaler Sprung pro Stunde: `config('bank.aktien.max_sprung_prozent', 15)` %.
- Admin kann jederzeit **manuell korrigieren** (Pflichtfeld `grund`, sichtbar im Kursverlauf).

### 6.2 Formel

```
// ── Umsatz-Delta ────────────────────────────────────────────────────────────
umsatz_neu    = Σ KasseTransaktion.amount WHERE typ='verkauf'
                AND created_at > aktien_letzte_berechnung (des Betriebs)
entnahmen_neu = Σ KasseTransaktion.amount WHERE typ='entnahme'
                AND created_at > aktien_letzte_berechnung
umsatz_delta  = ⌊ (umsatz_neu − entnahmen_neu) / kurs_teiler ⌋

// ── Angestellten-Faktor ─────────────────────────────────────────────────────
letzte_beobachtung = boerse_beobachtungen.angestellte  (neuester Eintrag)
normal             = config('bank.aktien.angestellte_normal', 4)
angestellten_delta = clamp(letzte_beobachtung − normal, −2, +2)
                     // jeder Angestellte über/unter Normal = ±1 Radi, max. ±2

// ── Neuer Kurs ──────────────────────────────────────────────────────────────
roh_kurs   = alter_kurs + umsatz_delta + angestellten_delta
max_sprung = ⌊ alter_kurs × max_sprung_prozent / 100 ⌋
neuer_kurs = clamp(roh_kurs, alter_kurs − max_sprung, alter_kurs + max_sprung)
neuer_kurs = max(neuer_kurs, min_kurs)
```

**Alle Stellschrauben in `config/bank.php`:**

```php
'aktien' => [
    // ── Zugangssystem ───────────────────────────────────────────────────────
    'boerse_pin'               => env('BOERSE_PIN', '1234'),

    // ── Stückelung & Kurs ───────────────────────────────────────────────────
    'standard_gesamt'          => 100,   // Default-Stückzahl je Betrieb
    'min_kurs'                 => 1,     // untere Kursgrenze in Radi
    'kurs_teiler'              => 20,    // Umsatz / Teiler = Kursdelta
    'angestellte_normal'       => 4,     // Normalbesetzung für Angestelltenbonus
    'max_sprung_prozent'       => 15,    // max. stündliche Kursänderung in %

    // ── Dividende & Kasse ───────────────────────────────────────────────────
    'dividende_prozent'        => 20,    // Vorschlagswert bei Dividenden-Dialog
    'kasse_warnschwelle'       => 50,    // Börsen-Kasse: Warnung ab X Radi
    'max_bargeld_invest'       => 50,    // Soft-Limit: Warn-Dialog ab X Radi Kaufsumme
    'kauf_gebuehr'             => env('BOERSE_KAUF_GEBUEHR', 1), // Bar-Gebühr je Kauf (Einnahme der Börse)

    // ── Aufgaben-Überwachung (Minuten) ──────────────────────────────────────
    'aufgaben' => [
        'beobachtung_warn_min'       => 75,
        'beobachtung_alarm_min'      => 90,
        'kassenkontrolle_warn_min'   => 90,
        'kassenkontrolle_alarm_min'  => 120,
        'kurstafel_warn_min'         => 15,
        'kurstafel_alarm_min'        => 30,
    ],
],
```

### 6.3 Kindgerechte Anzeige

| Anzeige | Wann |
|---------|------|
| 😀 ↑↑ großer grüner Pfeil | Kurs > 110 % von zuvor |
| 🙂 ↑ kleiner grüner Pfeil | Kurs > zuvor |
| 😐 → gleichbleibend | Kurs ≈ zuvor (±1 Radi) |
| 😕 ↓ kleiner roter Pfeil | Kurs < zuvor |
| 😟 ↓↓ großer roter Pfeil | Kurs < 90 % von zuvor |

Daneben Klartext: „Vor 1 Stunde: 12 Radi · Jetzt: 15 Radi · **+3 Radi** 🎉 Warum? Das Eiscafé hatte 6 Mitarbeiter und viel Umsatz!"

### 6.4 Kursverlauf (Börsenwart-Anzeige)

```
10 🟩 12 🟩 15 🟥 13 🟩 14 🟩 16
```
Grüne Kachel = gestiegen, rote Kachel = gefallen. Keine Y-Achse, keine Dezimalzahlen.

---

## 7. Dividende (Gewinnausschüttung)

Admin klickt **„Dividende ausschütten"** unter `/admin/boerse/dividende`:

1. Admin wählt Betrieb + gibt **Dividende pro Anteil** in Radi ein (System zeigt Vorschlag).
2. Vorschau: „Betrieb X schüttet Y Radi pro Anteil aus → Z Kinder erhalten zusammen W Radi."
3. Bestätigung → pro Anteilsinhaber **zwei verknüpfte `Payment`-Zeilen**:
   - Zeile A (Betrieb): `amount = -(stueck × radi_pro_anteil)`, comment = `"Dividende {Kind}: {stueck} Anteile × {radi} Radi"`
   - Zeile B (Kind): `amount = +(stueck × radi_pro_anteil)`, comment = `"Dividende {Betrieb}: {stueck} Anteile × {radi} Radi"`
   - Verknüpfung via `payment_id` (exakt wie `storeUeberweisung`).
4. Eigenbestand des Betriebs bekommt **keine** Dividende.
5. Wenn Betriebskonto nicht ausreicht: anteilig kürzen + Admin-Hinweis.

> „🎉 Das Eiscafé hat dir heute **10 Radi geschenkt**, weil du 5 Anteile hast! Das Geld ist jetzt auf deinem Konto."

---

## 8. Schlussabrechnung (Spielende)

- Admin klickt **„Spielende — Alle Anteile auflösen"** unter `/admin/boerse/abschluss`.
- Alle `aktien_bestaende` mit `stueck > 0` → zum **aktuellen Kurs** in Bargeld umgewandelt.
- Auszahlung aus der Börsen-Kasse (kein Payment-Eintrag).
- Vorher: Börsen-Kassenstand prüfen. Falls zu wenig → Admin-Warnung.
- **Druckansicht:** Namensliste mit Betrag → Kassenwart zahlt physisch aus.

---

## 9. Betrieb-Rückkauf

1. Betrieb + Kind kommen gemeinsam zur Börse.
2. Händler erfasst Betrieb, Kind, Stückzahl → System zeigt Gesamtbetrag.
3. `boerse_kasse` `typ='rueckkauf_einnahme'` (Betrieb zahlt) + `typ='rueckkauf_auszahlung'` (Kind bekommt). Netto = 0.
4. `aktien_transaktionen` + `aktien_bestaende` Update.
5. Flash: `['type' => 'success', 'Meldung' => 'Das Eiscafé hat 3 Anteile von Anna zurückgekauft. Anna bekommt 45 Radi bar. ✅']`

---

## 10. Datenmodell

### 10.1 Neue Spalten in `customers` (je eine eigene Migration)

| Spalte | Typ | Bemerkung |
|--------|-----|-----------|
| `aktien_gesamt` | `int nullable` | Gesamtstückzahl; `null` = kein Börsenhandel |
| `aktien_kurs` | `int nullable` | Aktueller Kurs in Radi |
| `aktien_startkurs` | `int nullable` | Vom Admin gesetzter Startkurs (historisch) |
| `aktien_letzte_berechnung` | `datetime nullable` | Zeitpunkt der letzten automatischen Kursberechnung |

### 10.2 Neue Tabellen

**`aktien_bestaende`** — aktueller Bestand pro Kind/Betrieb

| Spalte | Typ | Bemerkung |
|--------|-----|-----------|
| `id` | bigint PK | |
| `customer_id` | FK customers | Kind (Käufer) |
| `buisness_id` | FK customers | Betrieb |
| `stueck` | int ≥ 0 | aktueller Bestand |
| timestamps | | |

Unique-Index auf (`customer_id`, `buisness_id`).

---

**`aktien_transaktionen`** — append-only Ledger

| Spalte | Typ | Bemerkung |
|--------|-----|-----------|
| `id` | bigint PK | |
| `customer_id` | FK customers | Kind |
| `buisness_id` | FK customers | Betrieb |
| `typ` | enum | `'kauf'` / `'verkauf'` / `'rueckkauf'` / `'dividende'` / `'abschluss'` |
| `stueck` | int | Anzahl Anteile (bei `dividende` = 0) |
| `kurs` | int | Kurs-Snapshot |
| `summe` | int | `stueck × kurs` (bzw. Dividendenbetrag) |
| `boerse_rolle` | string | `'haendler'` / `'admin'` |
| `payment_id` | FK payments nullable | nur bei `typ='dividende'` |
| `notiz` | string nullable | |
| timestamps + SoftDeletes | | |

---

**`aktien_kurse`** — historische Kursverläufe

| Spalte | Typ | Bemerkung |
|--------|-----|-----------|
| `id` | bigint PK | |
| `buisness_id` | FK customers | |
| `kurs` | int | neuer Kurs |
| `vorher` | int | Kurs davor |
| `grund` | string | `'Stündliche Berechnung'`, `'Admin-Korrektur: …'` |
| `created_at` | datetime | |

---

**`boerse_beobachtungen`** — stündliche Angestellten-Erfassungen

| Spalte | Typ | Bemerkung |
|--------|-----|-----------|
| `id` | bigint PK | |
| `buisness_id` | FK customers | Betrieb |
| `angestellte` | int | Anzahl aktuell arbeitender Kinder |
| `notiz` | string nullable | z. B. „sehr voll" |
| `created_at` | datetime | |

---

**`boerse_kasse`** — Bargeld-Ledger der Börse

| Spalte | Typ | Bemerkung |
|--------|-----|-----------|
| `id` | bigint PK | |
| `typ` | enum | `'kauf_einnahme'` / `'verkauf_auszahlung'` / `'rueckkauf_einnahme'` / `'rueckkauf_auszahlung'` / `'einlage'` / `'entnahme'` / `'abschluss_auszahlung'` / `'gebuehr_einnahme'` |
| `betrag` | int | immer positiv |
| `notiz` | string | |
| `aktien_transaktion_id` | FK nullable | |
| `created_at` | datetime | |

Kassenstand = `Σ(kauf_einnahme + rueckkauf_einnahme + einlage + gebuehr_einnahme)` − `Σ(verkauf_auszahlung + rueckkauf_auszahlung + entnahme + abschluss_auszahlung)`

---

**`boerse_aufgaben_log`** — Protokoll erledigter Pflichtaufgaben

| Spalte | Typ | Bemerkung |
|--------|-----|-----------|
| `id` | bigint PK | |
| `aufgabe` | enum | `'kassenkontrolle'` / `'kurstafel_ausgehaengt'` |
| `created_at` | datetime | |

Beobachtungs-Status wird direkt aus `boerse_beobachtungen.created_at` abgeleitet (kein extra Log).

### 10.3 Eloquent-Modelle (neu in `app/Models/`)

- `AktienBestand` (fillable: customer_id, buisness_id, stueck)
- `AktienTransaktion` (fillable: alle Spalten) + SoftDeletes
- `AktienKurs` (fillable: buisness_id, kurs, vorher, grund)
- `BoerseBeobachtung` (fillable: buisness_id, angestellte, notiz)
- `BoerseKasse` (fillable: typ, betrag, notiz, aktien_transaktion_id)
- `BoerseAufgabenLog` (fillable: aufgabe)

**Neuer Service** `app/Services/BoerseAufgabenService.php`:

```php
public function status(): array
{
    return [
        'beobachtung'     => $this->pruefeBeobachtung(),
        'kassenkontrolle' => $this->pruefeLog('kassenkontrolle'),
        'kurstafel'       => $this->pruefeKurstafel(),
    ];
    // Ergebnis: ['status' => 'ok'|'warn'|'alarm', 'seit' => Carbon]
}
```

Ergänzungen auf `Customer`:

```php
public function hatAktien(): bool         { return $this->aktien_gesamt !== null; }
public function anteileVerkauft(): int    { return AktienBestand::where('buisness_id', $this->id)->sum('stueck'); }
public function anteileEigen(): int       { return $this->aktien_gesamt - $this->anteileVerkauft(); }
public function kursVerlauf()             { return $this->hasMany(AktienKurs::class, 'buisness_id'); }
public function beobachtungen()           { return $this->hasMany(BoerseBeobachtung::class, 'buisness_id'); }
public function aktienPortfolio()         { return $this->hasMany(AktienBestand::class, 'customer_id'); }
public function letzteBeobachtung(): ?int { return $this->beobachtungen()->latest()->value('angestellte'); }
```

---

## 11. Routen & Middleware

### 11.1 `/boerse/*` — Börsen-Mitarbeiter-Bereich (Middleware `HasBoerse`)

```
GET  /boerse/login
POST /boerse/login
GET  /boerse/logout

GET  /boerse                                  Dashboard (alle Aufgaben + Status-Kacheln)
GET  /boerse/hilfe                            Hilfe-Seite (auch ohne Login)
GET  /boerse/hilfe/drucken                    Druckansicht Jobkarten DIN A5

// ── Handel ─────────────────────────────────────────────────────────────────
GET  /boerse/handel
GET  /boerse/handel/{customer}/kaufen
POST /boerse/handel/{customer}/kaufen
GET  /boerse/handel/{customer}/verkaufen
POST /boerse/handel/{customer}/verkaufen
GET  /boerse/handel/{customer}/rueckkauf
POST /boerse/handel/{customer}/rueckkauf

// ── Erfassung ──────────────────────────────────────────────────────────────
GET  /boerse/erfassung
POST /boerse/erfassung/{customer}
GET  /boerse/erfassung/vorschau/{customer}

// ── Kasse ──────────────────────────────────────────────────────────────────
GET  /boerse/kasse
POST /boerse/kasse/einlage
POST /boerse/kasse/entnahme
POST /boerse/kasse/bestaetigen                → BoerseAufgabenLog 'kassenkontrolle'

// ── Kurse & Berichte ───────────────────────────────────────────────────────
GET  /boerse/kurse
GET  /boerse/kurse/{customer}
GET  /boerse/bericht/kurstafel                → auto-Log 'kurstafel_ausgehaengt' beim Aufruf
POST /boerse/bericht/kurstafel/bestaetigen    → manueller Log-Eintrag
GET  /boerse/bericht/tagesabschluss
```

### 11.2 `/admin/boerse/*` — Admin (Middleware `auth` + `isAdmin`)

```
GET  /admin/boerse                            Übersicht + Aufgaben-Badge
GET  /admin/boerse/aufgaben                   Detailliste überfälliger Aufgaben
GET  /admin/boerse/aktivieren
POST /admin/boerse/aktivieren
POST /admin/boerse/{customer}/deaktivieren
GET  /admin/boerse/{customer}/kurs
POST /admin/boerse/{customer}/kurs
GET  /admin/boerse/dividende
POST /admin/boerse/dividende
GET  /admin/boerse/abschluss
POST /admin/boerse/abschluss
GET  /admin/boerse/pin
POST /admin/boerse/pin
GET  /admin/boerse/bericht
```

### 11.3 Neue Middleware

`HasBoerse` in `app/Http/Middleware/HasBoerse.php`:
- Prüft `session('boerse')`. Leitet zu `/boerse/login` weiter falls nicht gesetzt.
- Keine Rollen-Einschränkung — alle eingeloggten Nutzer sehen alle Routen.
- Eintrag in `app/Http/Kernel.php` unter `$routeMiddleware`.
- `/boerse/hilfe` und `/boerse/hilfe/drucken` sind **öffentlich** (kein `HasBoerse`).

---

## 12. Buchungslogik

### 12.1 Kauf: Kind kauft `n` Anteile (Bargeld)

1. `AktienKaufRequest`: `stueck >= 1`, Eigenbestand `>= stueck`.
2. Gesamtbetrag = `n × kurs` + `kauf_gebuehr` (separate Bar-Gebühr, Einnahme der Börse).
3. Soft-Limit: wenn `gesamt > max_bargeld_invest` → Warn-Dialog (kein harter Stop).
4. Kein Kontostand-Check — physisches Bargeld.
5. `AktienBestand` → `firstOrCreate` + `increment`.
6. `AktienTransaktion` + **zwei** `BoerseKasse`-Zeilen anlegen:
   - `typ='kauf_einnahme'` für `n × kurs` (Anteile)
   - `typ='gebuehr_einnahme'` für `kauf_gebuehr` (eigene Einnahme der Börse)
7. Flash: z. B. „Anna hat 5 Anteile am Eiscafé gekauft. Bezahlt: 46 Radi bar (45 + 1 Gebühr). ✅"

### 12.2 Verkauf: Kind verkauft `n` Anteile (Bargeld)

1. Kind hat `>= n` Anteile.
2. Börsenkasse prüfen: `kassenstand >= n × kurs`, sonst Error-Flash.
3. `AktienBestand` → `decrement`. `AktienTransaktion` + `BoerseKasse`.

### 12.3 Betrieb-Rückkauf

Zwei `BoerseKasse`-Zeilen (Netto 0). `AktienTransaktion` + `AktienBestand`-Update.

### 12.4 Stündliche Kursberechnung

```
php artisan aktien:kurs-berechnen
```

`$schedule->command('aktien:kurs-berechnen')->hourly()` in `app/Console/Kernel.php`.

---

## 13. Views & Layout

### 13.1 Eigenes Börsen-Layout

`resources/views/boerse/layouts/app.blade.php` — Tailwind 3, Goldtöne im Header.
- Header: Logo „📈 Radi-Börse", Uhrzeit, Button „❓ Hilfe".
- Navigationsleiste: alle Funktionen (Handel, Erfassung, Kasse, Kurse).
- **Aufgaben-Status-Banner** direkt unter dem Header: 3 farbige Kacheln auf jeder Seite.

### 13.2 Views-Verzeichnis

```
resources/views/boerse/
├── layouts/app.blade.php
├── login.blade.php
├── dashboard.blade.php
├── hilfe.blade.php               ← öffentlich, 4 Job-Beschreibungen
├── hilfe_drucken.blade.php       ← Druckansicht 4× DIN A5 Jobkarten
├── handel/
│   ├── index.blade.php
│   ├── kaufen.blade.php
│   ├── verkaufen.blade.php
│   └── rueckkauf.blade.php
├── erfassung/
│   ├── index.blade.php
│   └── vorschau.blade.php
├── kasse/
│   └── index.blade.php           ← inkl. „Kassenstand bestätigen"-Button
├── kurse/
│   ├── index.blade.php
│   └── verlauf.blade.php
└── bericht/
    ├── kurstafel.blade.php       ← auto-Log beim Aufruf
    └── tagesabschluss.blade.php

resources/views/admin/boerse/
├── index.blade.php               ← Aufgaben-Badge
├── aufgaben.blade.php            ← Detailansicht überfälliger Aufgaben
├── aktivieren.blade.php
├── kurs.blade.php
├── dividende.blade.php
├── abschluss.blade.php
├── pin.blade.php
└── bericht.blade.php
```

### 13.3 Tooltips & Kindertexte

| Begriff | Erklärung |
|---------|-----------|
| Anteil | „Ein kleines Stück vom Betrieb. Wenn der Betrieb viel verdient, wird dein Stück mehr wert." |
| Wert / Kurs | „So viel kostet heute ein Anteil — in Radi." |
| Gewinnausschüttung | „Wenn ein Betrieb Geld verdient hat, darf er es teilen. Du bekommst dann Radi auf dein Konto." |
| Kursberechnung | „Unser Computer schaut einmal pro Stunde: Wie läuft der Betrieb? Dann ändert er den Preis." |

---

## 14. Form Requests (neu in `app/Http/Requests/`)

| Request | Wichtige Regeln |
|---------|-----------------|
| `AktienKaufRequest` | `stueck: required\|integer\|min:1`, `buisness_id: exists:customers,id`, `customer_id: exists:customers,id` |
| `AktienVerkaufRequest` | wie oben |
| `AktienRueckkaufRequest` | wie VerkaufRequest; Eigenbestand im Controller prüfen |
| `BoerseBeobachtungRequest` | `angestellte: required\|integer\|min:0\|max:50`, `notiz: nullable\|string\|max:100` |
| `BoerseKasseRequest` | `betrag: required\|integer\|min:1`, `notiz: nullable\|string\|max:120` |
| `AdminAktienAktivierenRequest` | `aktien_gesamt: required\|integer\|min:1`, `aktien_kurs: required\|integer\|min:1` |
| `AdminDividendeRequest` | `buisness_id: exists:customers,id`, `radi_pro_anteil: required\|integer\|min:1` |

---

## 15. Risiken & Gegenmaßnahmen

| Risiko | Gegenmaßnahme |
|--------|---------------|
| Börse läuft leer | Kassenwart-Warnung ab `kasse_warnschwelle`; Schlussabrechnung gesperrt |
| Kind investiert zu viel Bargeld | Soft-Limit `max_bargeld_invest`: Warn-Dialog, kein harter Stop |
| Kurs-Crash frustriert Kinder | Mindestkurs 1 Radi, Max-Stundenschwankung 15 %, Admin-Korrekturoption |
| Händler-Fehleingabe | Vorschau vor Bestätigung; Storno nur durch Admin (SoftDelete) |
| Kursbeobachter vergisst Erfassung | Aufgaben-Überwachung: Gelbe Kachel nach 75 Min + roter Admin-Badge nach 90 Min |
| Kassenwart vergisst Bestätigung | Aufgaben-Überwachung: Warnung 90 Min / Alarm 120 Min |
| Kurstafel nicht aktualisiert | Aufgaben-Überwachung: Alarm 30 Min nach Kursberechnung ohne Bestätigung |
| Betrieb stellt Angestellte ab zum Kursmanipulieren | Bewusster Effekt — Lernerfahrung; Admin kann korrigieren |
| Portfolio-Neid | Bestände unsichtbar; kein Ranglisten-Feature |
| Betrieb-Rückkauf → Eigenbestand > gesamt | Validierung im Controller |

---

## 16. Pädagogischer Begleitprozess

### Vor dem ersten Spieltag
- 15-Minuten-Erklärrunde: „Betrieb = Pizza, Anteile = Pizzastücke. Wenn die Pizzeria voll ist, will jeder ein Stück → Stück wird teurer."
- **Jobkarten drucken** (`/boerse/hilfe/drucken`) und an die 4 Börsen-Angestellten ausgeben.
- Börsen-PIN unter `/admin/boerse/pin` festlegen und allen 4 mitteilen.
- Startkurse für 2–3 Pilotbetriebe im Admin-Bereich setzen.

### Während des Spieltags
- Kursbeobachter verlässt die Börse stündlich → aktivste, lernreichste Rolle.
- Börsenwart druckt nach Kursberechnung neue Kurstafel aus und bestätigt mit Klick.
- Kassenwart drückt stündlich „Kassenstand bestätigen" → grüne Kachel.
- Aufgaben-Status-Kacheln zeigen sofort, wenn eine Aufgabe zu lang offen ist.
- Lehrkraft injiziert spontane Ereignisse als Admin-Kurskorrektur.
- Wenn Admin-Badge rot leuchtet → Lehrkraft geht zur Börse und hilft dem Team.

### Am Ende des Spieltags
1. Admin → Dividende je aktivem Betrieb per Klick.
2. Kinder rufen Kontostand an der Bank ab → sehen die Dividende.
3. Admin → Schlussabrechnung: Druckliste → Kassenwart zahlt bar aus.
4. **Reflexionsrunde:** „Welcher Betrieb war am stärksten? Hatte der täglich viele Angestellte? Was haben die Kursbeobachter herausgefunden?"

---

## 17. Abgrenzung zu anderen Bereichen

| Funktion | Bank (`/`) | Betriebs-Kasse (`/betrieb/*`) | Börse (`/boerse/*`) |
|----------|-----------|-------------------------------|---------------------|
| Geldart | Buchgeld (Radi auf Konto) | Bargeld | Bargeld |
| Ledger | `payments` | `kasse_transaktionen` | `boerse_kasse` + `aktien_transaktionen` |
| Auth | User (Schüler-Banker) | Betriebs-PIN | Gemeinsamer Börsen-PIN |
| Session | `customer` | `betrieb` | `boerse` |
| Layout | Bootstrap 5 | Tailwind (eigenes Layout) | Tailwind (eigenes Layout) |
| Admin | `/admin/*` | `/admin/betriebe/*` | `/admin/boerse/*` |

**Einzige Verbindung Bank ↔ Börse:** Dividende (Betriebskonto → Kinderkonto als `Payment`-Zeile).

---

## 18. Was außerhalb des Konzepts bleibt

- Keine Echtzeit-Kurse, keine WebSockets.
- Keine Hebelprodukte / Optionen / Derivate (nicht kindgerecht).
- Keine Verzinsung des Aktiendepots.
- Keine direkte Buchungsverknüpfung Betriebs-Kasse ↔ Börse.
- Kein automatischer Push-Alert (nur Dashboard-Kacheln + Admin-Badge).

---

## 19. Implementierungs-Reihenfolge

1. **`config/bank.php`** — `aktien`-Block (Abschnitt 6.2) + `.env` um `BOERSE_PIN` ergänzen.
2. **Migrationen** (je eine Datei):
   - `add_aktien_gesamt_to_customers_table`
   - `add_aktien_kurs_to_customers_table`
   - `add_aktien_startkurs_to_customers_table`
   - `add_aktien_letzte_berechnung_to_customers_table`
   - `create_aktien_bestaende_table`
   - `create_aktien_transaktionen_table`
   - `create_aktien_kurse_table`
   - `create_boerse_beobachtungen_table`
   - `create_boerse_kasse_table`
   - `create_boerse_aufgaben_log_table`
3. **Modelle** + Beziehungen + `Customer`-Ergänzungen (Abschnitt 10.3).
4. **`BoerseAufgabenService`** in `app/Services/`.
5. **Middleware** `HasBoerse` + Registration in `Kernel.php`.
6. **Artisan-Command** `aktien:kurs-berechnen` + Scheduler `Console/Kernel.php`.
7. **Form Requests** (Abschnitt 14).
8. **Controller** (`BoerseController`, `BoerseHandelController`, `BoerseErfassungController`, `BoerseKasseController`, `BoerseKurseController`, `AdminBoerseController`).
9. **Routen** in `routes/web.php` (`/boerse/hilfe` ohne Middleware!).
10. **Layouts + Views** (`resources/views/boerse/`, `resources/views/admin/boerse/`).
11. **Kind- und Schüler-Test** an einem Probetag — iterieren bis alle Texte klar sind.

---

**Nächster Schritt:** `config/bank.php` + `.env` (Schritt 1),
dann Migrationen (Schritt 2) — bereit zur Implementierung.
