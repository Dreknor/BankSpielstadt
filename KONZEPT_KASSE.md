# Konzept: Betriebs-Kasse & Abrechnung

**Projekt:** Kinderspielstadt-Bank  
**Status:** Konzept – Entscheidungen getroffen, bereit zur Implementierung  
**Datum:** 2026-05-17

---

## Getroffene Entscheidungen

| # | Frage | Entscheidung |
|---|-------|--------------|
| 1 | Bezahlwege | **Nur Barzahlung** – keine Konto-Abbuchung in der Kasse |
| 2 | Einlagen | **Bareinzahlung möglich** (z. B. Tagesstartkapital) |
| 3 | Kassierer-Erfassung | **Keine** – kein Namensfeld nötig |
| 4 | Zugang | **Ein PIN pro Betrieb**, keine Mehrfach-Sessions |
| 5 | Export | **Druckansicht / PDF** für die Abrechnung |
| 6 | Admin-Zugriff | **Bank-Admin sieht alle Betriebs-Daten** (Produkte, Kasse, Abrechnung) |

---

## 1. Ziel

Betriebe (Kunden mit `buisness = 1`) erhalten einen eigenen, separaten Zugangsbereich.
Dort können sie:

- Produkte mit Preisen hinterlegen
- Kassenvorgänge **ausschließlich bar** erfassen (Verkauf, Einlage, Entnahme)
- Ihren Kassenbestand jederzeit einsehen
- Geldentnahmen und Bareinlagen dokumentieren
- Eine Tages- und Gesamtabrechnung als **Druckansicht / PDF** ausgeben

Die normalen Bank-Routen (`/einzahlen`, `/auszahlen`, `/log` usw.) bleiben für den
Betriebs-Zugang **vollständig gesperrt**.

Der **Bank-Admin** kann alle Betriebsdaten (Produkte, Kassenvorgänge, Abrechnung)
über den normalen Admin-Bereich einsehen, ohne sich als Betrieb einloggen zu müssen.

---

## 2. Zugangssystem

### 2.1 Prinzip

- Betriebe loggen sich über einen eigenen **Betriebs-PIN** ein – kein normaler User-Account.
- Der PIN wird vom Admin vergeben (neue Spalte `betrieb_pin` in der `customers`-Tabelle).
- Die Session-Variable `betrieb` identifiziert den eingeloggten Betrieb.
- Eine neue Middleware `hasBetrieb` schützt alle Betriebs-Routen.
- **Nur ein gleichzeitiger Zugang pro Betrieb** – beim erneuten Login wird die alte Session überschrieben.

### 2.2 Login-Flow

```
GET  /betrieb/login   → PIN-Eingabe-Formular (Betriebsname + PIN)
POST /betrieb/login   → PIN prüfen → session('betrieb') setzen → redirect /betrieb
GET  /betrieb/logout  → session('betrieb') löschen → redirect /betrieb/login
```

### 2.3 PIN-Vergabe durch Admin

Neue Admin-Route (hinter `isAdmin`-Middleware):

```
GET  /admin/betriebe/pin   → Übersicht aller Betriebe mit PIN-Status
POST /admin/betriebe/pin   → PIN für einen Betrieb setzen / ändern
```

---

## 3. Datenmodell

### 3.1 Neue Spalte: `customers.betrieb_pin`

Migration: `add_betrieb_pin_to_customers_table`

```php
$table->string('betrieb_pin')->nullable();
```

### 3.2 Neue Tabelle: `products`

Migration: `create_products_table`

| Spalte        | Typ         | Beschreibung                       |
|---------------|-------------|------------------------------------|
| `id`          | PK          |                                    |
| `customer_id` | FK → customers | Zugehöriger Betrieb             |
| `name`        | string(100) | Produktname                        |
| `price`       | integer     | Preis in Radi (ganzzahlig, min. 1) |
| `active`      | boolean     | Sichtbar im Kassensystem           |
| `created_at`  | timestamp   |                                    |
| `updated_at`  | timestamp   |                                    |
| `deleted_at`  | timestamp   | SoftDeletes                        |

### 3.3 Neue Tabelle: `kasse_transaktionen`

Migration: `create_kasse_transaktionen_table`

| Spalte        | Typ                                    | Beschreibung                                          |
|---------------|----------------------------------------|-------------------------------------------------------|
| `id`          | PK                                     |                                                       |
| `customer_id` | FK → customers                         | Betrieb                                               |
| `type`        | enum: `verkauf`, `entnahme`, `einlage` | Art des Vorgangs                                      |
| `amount`      | integer                                | Betrag in Radi (immer positiv; Vorzeichen via `type`) |
| `comment`     | string (nullable)                      | Freitext (z. B. „Tagesstartkapital")                  |
| `created_at`  | timestamp                              |                                                       |
| `updated_at`  | timestamp                              |                                                       |

> Kein `kassierer`-Feld – Entscheidung: keine Kassierer-Erfassung.  
> Kein `payment_id`-Feld – Entscheidung: nur Barzahlung, kein Konto-Bezug.

### 3.4 Neue Tabelle: `kasse_positionen`

Migration: `create_kasse_positionen_table`

| Spalte           | Typ                      | Beschreibung                           |
|------------------|--------------------------|----------------------------------------|
| `id`             | PK                       |                                        |
| `transaktion_id` | FK → kasse_transaktionen |                                        |
| `product_id`     | FK → products            |                                        |
| `menge`          | integer                  | Stückzahl (min. 1)                     |
| `einzelpreis`    | integer                  | Snapshot des Preises zum Kaufzeitpunkt |
| `created_at`     | timestamp                |                                        |
| `updated_at`     | timestamp                |                                        |

> `kasse_positionen` gilt nur für `type = 'verkauf'`. Einlagen und Entnahmen
> haben keine Positionen.

---

## 4. Kassenbestand-Logik

```
Kassenbestand = SUMME(einlage.amount)
              + SUMME(verkauf.amount)
              − SUMME(entnahme.amount)
```

- **Startsaldo = 0.** Das Tagesstartkapital wird als erste `einlage`-Transaktion eingetragen.
- Alle Vorgänge sind reine Bargeldbewegungen – kein Bezug zu `Payment`-Einträgen.
- Die Kasse wird **nie negativ gemacht**: vor jeder Entnahme wird geprüft,
  ob `Kassenbestand ≥ Entnahmebetrag`.

---

## 5. Routen-Struktur

### 5.1 Betriebs-Routen (Prefix `/betrieb`)

```
# Öffentlich
GET  /betrieb/login                        BetriebController@loginForm
POST /betrieb/login                        BetriebController@login
GET  /betrieb/logout                       BetriebController@logout

# Geschützt (Middleware: hasBetrieb)
GET  /betrieb                              BetriebController@index          Startseite / Kasse
GET  /betrieb/produkte                     ProduktController@index          Produktliste
GET  /betrieb/produkte/erstellen           ProduktController@create         Neues Produkt
POST /betrieb/produkte                     ProduktController@store          Speichern
GET  /betrieb/produkte/{id}/bearbeiten     ProduktController@edit           Bearbeiten
PUT  /betrieb/produkte/{id}               ProduktController@update         Aktualisieren
DELETE /betrieb/produkte/{id}             ProduktController@destroy        Löschen (SoftDelete)

GET  /betrieb/kasse                        KasseController@index            Kassensystem
POST /betrieb/kasse/verkauf                KasseController@storeVerkauf     Verkauf abschließen
POST /betrieb/kasse/entnahme               KasseController@storeEntnahme    Entnahme dokumentieren
POST /betrieb/kasse/einlage                KasseController@storeEinlage     Bareinlage dokumentieren

GET  /betrieb/abrechnung                   AbrechnungController@index       Tagesjournal
GET  /betrieb/abrechnung/drucken           AbrechnungController@print       Druckansicht / PDF
```

### 5.2 Admin-Routen (Prefix `/admin`, Middleware: `isAdmin`)

```
GET  /admin/betriebe/pin                   AdminBetriebController@pinIndex  PIN-Übersicht
POST /admin/betriebe/pin                   AdminBetriebController@pinStore  PIN setzen

GET  /admin/betriebe/{id}/kasse            AdminBetriebController@kasse     Kassenjournal eines Betriebs
GET  /admin/betriebe/{id}/abrechnung       AdminBetriebController@abrechnung Abrechnung eines Betriebs
GET  /admin/betriebe/{id}/produkte         AdminBetriebController@produkte  Produkte eines Betriebs
```

---

## 6. Controller-Übersicht

| Controller               | Pfad                                              | Verantwortung                            |
|--------------------------|---------------------------------------------------|------------------------------------------|
| `BetriebController`      | `Http/Controllers/Betrieb/BetriebController`      | Login, Logout, Startseite                |
| `ProduktController`      | `Http/Controllers/Betrieb/ProduktController`      | CRUD Produkte                            |
| `KasseController`        | `Http/Controllers/Betrieb/KasseController`        | Verkauf, Entnahme, Einlage               |
| `AbrechnungController`   | `Http/Controllers/Betrieb/AbrechnungController`   | Journal, Druckansicht                    |
| `AdminBetriebController` | `Http/Controllers/AdminBetriebController`         | Admin-Zugriff auf alle Betriebsdaten     |

---

## 7. Kassen-UI (Wireframe)

```
┌──────────────────────────────────────────────────────────────────────┐
│  🏪 Bäckerei Müller                  Kasse: 42 Radi   [Abmelden]    │
├───────────────────────────────┬──────────────────────────────────────┤
│  PRODUKTE                     │  WARENKORB                           │
│                               │                                      │
│  [Brot       3 Radi   ➕]    │  Brot       × 2  =  6 Radi    [−]   │
│  [Brezel     2 Radi   ➕]    │  Brezel     × 1  =  2 Radi    [−]   │
│  [Kuchen     5 Radi   ➕]    │  ──────────────────────────────────  │
│  [Limonade   1 Radi   ➕]    │  Gesamt:  8 Radi                     │
│                               │                                      │
│                               │  [💵 Bezahlt – Kasse buchen]        │
│                               │  [🗑 Warenkorb leeren]              │
└───────────────────────────────┴──────────────────────────────────────┘

[📥 Bareinlage]   [📤 Geldentnahme]   [📋 Abrechnung]   [📦 Produkte]
```

- Kein Bezahlweg-Auswahl – ausschließlich Barzahlung.
- Kein Kassierername-Feld.
- Kassenbestand immer sichtbar in der Kopfzeile.

---

## 8. Abrechnung / Druckansicht

### 8.1 Bildschirmansicht (`/betrieb/abrechnung`)

- **Kassenbestand** aktuell (groß, prominent)
- **Filter:** Datum (Standard: heute)
- **Tabelle** aller Transaktionen: Uhrzeit | Typ | Betrag | Kommentar | Positionen (aufklappbar)
- **Summenzeile:** Einlagen | Verkäufe | Entnahmen | Kassenbestand
- **Einlage-Button** und **Entnahme-Button** direkt auf der Seite

### 8.2 Druckansicht / PDF (`/betrieb/abrechnung/drucken`)

- Sauberes, druckoptimiertes Layout (kein Navbar, keine Buttons)
- Kopfzeile: Betriebsname, Datum, Uhrzeit des Ausdrucks
- Tabelle aller Transaktionen des gewählten Tages
- Summentabelle am Ende
- `window.print()` via Button, alternativ serverseitiges PDF (z. B. mit `barryvdh/laravel-dompdf`)

---

## 9. Admin-Zugriff auf Betriebsdaten

Der **Bank-Admin** kann ohne Betriebs-Login:

- Alle Betriebe und ihre PINs einsehen und verwalten
- Das Kassenjournal jedes Betriebs einsehen (inkl. Druckansicht)
- Die Produktliste jedes Betriebs einsehen
- Einzelne Kassenbuchungen **löschen** (Korrekturmöglichkeit)

Der Admin-Zugriff erfolgt über separate Routen unter `/admin/betriebe/*`,
geschützt durch die bestehende `isAdmin`-Middleware.

Im **bestehenden Admin-Dashboard** wird die Betriebe-Tabelle um eine Spalte
„Kassenbestand" ergänzt.

---

## 10. Migrations-Reihenfolge

```
1. add_betrieb_pin_to_customers_table
2. create_products_table
3. create_kasse_transaktionen_table
4. create_kasse_positionen_table
```

---

## 11. Modell-Übersicht

```
Customer (bestehend)
  hasMany → Product
  hasMany → KasseTransaktion

Product
  belongsTo → Customer
  hasMany   → KassePosition
  SoftDeletes

KasseTransaktion
  belongsTo → Customer
  hasMany   → KassePosition

KassePosition
  belongsTo → KasseTransaktion
  belongsTo → Product
```

Hilfsmethode am `Customer`-Modell:

```php
public function kassenbestand(): int
{
    $ein = $this->kasseTransaktionen()->whereIn('type', ['einlage', 'verkauf'])->sum('amount');
    $aus = $this->kasseTransaktionen()->where('type', 'entnahme')->sum('amount');
    return $ein - $aus;
}
```

---

## 12. Views-Struktur

```
resources/views/betrieb/
    layouts/
        app.blade.php          Eigenes Layout (kein Bank-Navbar)
    login.blade.php
    index.blade.php            Startseite = Kasse
    kasse/
        index.blade.php        Kassensystem (Produktgitter + Warenkorb)
    produkte/
        index.blade.php        Produktliste
        create.blade.php       Neues Produkt
        edit.blade.php         Produkt bearbeiten
    abrechnung/
        index.blade.php        Tagesjournal
        print.blade.php        Druckansicht (druckoptimiert, kein Layout)

resources/views/admin/
    betrieb_pin.blade.php      PIN-Verwaltung
    betrieb_kasse.blade.php    Admin: Kassenjournal eines Betriebs
    betrieb_produkte.blade.php Admin: Produktliste eines Betriebs
```

---

## 13. Implementierungsreihenfolge

```
Schritt 1  Migrations + Modelle
           → add_betrieb_pin, products, kasse_transaktionen, kasse_positionen
           → Customer erweitern (kassenbestand()-Methode)

Schritt 2  Middleware hasBetrieb
           → Kernel registrieren

Schritt 3  BetriebController (Login / Logout / Index)
           → Session-Handling, Login-View

Schritt 4  ProduktController + Views
           → CRUD inkl. SoftDelete, Aktivieren/Deaktivieren

Schritt 5  KasseController + Kassen-View
           → Verkauf (Warenkorb → Transaktion + Positionen)
           → Einlage, Entnahme (mit Kassenbestand-Prüfung)

Schritt 6  AbrechnungController + Views
           → Tagesjournal, Filter, Summen
           → Druckansicht

Schritt 7  AdminBetriebController + Views
           → PIN-Vergabe
           → Lesender Zugriff auf Kasse / Produkte aller Betriebe
           → Dashboard-Spalte Kassenbestand

Schritt 8  Routen in web.php eintragen
           → /betrieb/* mit hasBetrieb
           → /admin/betriebe/* mit isAdmin

Schritt 9  npm run build (Vite)
```

---

## 14. Nicht im Scope (bewusst ausgelassen)

- Kontobezahlungen in der Kasse (nur Bar)
- Kassierer-Erfassung
- Mehrere gleichzeitige Betriebs-Sessions
- Lagerbestand / Mengenverwaltung
- Automatische Tagesabschlüsse

