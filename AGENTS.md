# AGENTS.md — Bank (Laravel 9) Play-Money Banking App

A German-language Laravel 9 / PHP 8 app for running an in-house "bank" (currency = **Radi**). UI strings, route paths and config keys are German — keep that convention when adding code.

## Kontext: Kinderspielstadt-Bank

Die Software ist die **Bank einer Kinderspielstadt**. Jedes Kind hat ein eigenes Konto; Betriebe (`buisness = 1`) zahlen aus ihrem Konto Löhne an die Konten der mitarbeitenden Kinder. Arbeitszeiten werden erfasst, der Lohn berechnet, dem Kinderkonto gutgeschrieben und dem Betriebskonto belastet (zwei verknüpfte `Payment`-Zeilen, siehe Überweisungsmuster).

### Leitprinzipien (für jede Weiterentwicklung verbindlich)

1. **Bedienung ausschließlich durch Schüler.** Alle UI-Flows, Texte, Fehlermeldungen und Workflows müssen so gestaltet sein, dass ein Grundschulkind sie ohne Erwachsenenhilfe bedienen kann.
   - Kurze, einfache deutsche Sätze; keine Fach-/Bankjargon-Wörter ohne Erklärung; keine englischen Begriffe in der UI.
   - Große, klar beschriftete Buttons, eindeutige Bestätigungs-/Fehlermeldungen (`type` + `Meldung`).
   - Pflichtfelder, Validierung und sinnvolle Defaults so wählen, dass Fehleingaben aufgefangen werden (z. B. `min:1`, `exists:customers,id`, Kontostand-Check vor Auszahlung/Überweisung).
   - Keine destruktiven Aktionen ohne sichtbares Feedback; gelöschte Zahlungen geben `type => 'warning'` zurück.
2. **Schüler-Banker statt Admin.** Der eingeloggte `User` ist i. d. R. ein Schüler-Banker (kein `is_admin`). Neue Features für den Alltagsbetrieb gehören in die `auth`+`hasCustomer`-Gruppe, **nicht** hinter `isAdmin`. `isAdmin` bleibt Lehrkräften/Setup vorbehalten (Import, Export, Gebühren, Strafen, Startkapital).
3. **Kundenkontext immer explizit wählen.** Bevor irgendeine Geldbewegung möglich ist, muss über `choose/customer` ein Kunde in der Session liegen (`SessionHasCustomer`). Neue Money-Routes **müssen** in der `hasCustomer`-Gruppe stehen.
4. **Nachvollziehbarkeit vor Bequemlichkeit.** Jede Geldbewegung erzeugt eine neue `Payment`-Zeile mit `user_id` (Banker) und deutschem `comment`. Niemals Beträge in bestehenden Zeilen ändern — das Ledger muss für Lehrer/Schüler im Log (`/log`) lesbar bleiben.
5. **Betrieb ↔ Kind = zwei verknüpfte Zeilen.** Lohn-, Bonus- und Überweisungsvorgänge belasten das Betriebskonto **und** schreiben dem Kinderkonto gut, verknüpft über `payment_id` (siehe `storeUeberweisung`). Niemals nur eine Seite buchen.
6. **Keine negativen Konten ohne Kredit.** Vor Auszahlung/Überweisung `$customer->balance` prüfen und mit `type => 'error'`-Meldung abbrechen, wie in `PaymentController::storeAuszahlen` / `storeUeberweisung`.
7. **Geldsummen sind ganzzahlige Radi.** Beträge als `numeric|min:1` validieren; Anzeige immer mit Einheit „Radi".
8. **Datenschutz light:** Im öffentlichen `/kontostand`-Flow nur das nötige Minimum zeigen; Zugang per kindgewähltem `key` (`min:8`).

## Architecture at a glance

- **Stack**: Laravel 9, PHP ^8.0.2, Blade + Bootstrap 5 (Bank-UI) / Tailwind 3 (Betriebs-Kasse), **Vite 5** via `laravel-vite-plugin` (Mix wurde entfernt), MySQL (Eloquent + SoftDeletes), `maatwebsite/excel` for import/export, `barryvdh/laravel-debugbar` in dev.
- **Domain models** (`app/Models/`):
  - `Customer` — has many `Payment`, `WorkingTime`, `Product`, `KasseTransaktion`. `buisness` flag (note misspelling, kept everywhere) marks employer customers. `getBalanceAttribute()` sums all payments → `$customer->balance`. Global scope orders by name. Uses `SoftDeletes`. `betrieb_pin` (nullable) ist der Login-PIN für die Betriebs-Kasse.
  - `Payment` — append-only ledger. Negative `amount` = withdrawal. Transfers create **two** linked rows via self-referencing `payment_id` (see `partnerPayment()` and `PaymentController::storeUeberweisung` — second row is saved, then first row updated with the partner id). Deleting one auto-deletes its partner (`PaymentController::delete`).
  - `WorkingTime`, `PaymentBonus` — wage/bonus tracking tied to a `buisness` customer.
  - **Betriebs-Kasse** (separater Ledger, **kein** `Payment`-Bezug, ausschließlich Bargeld): `Product` (SoftDeletes, `customer_id`, `name`, `price`, `active`), `KasseTransaktion` (`type` ∈ `verkauf`/`einlage`/`entnahme`, `amount` immer positiv, Vorzeichen über `type`), `KassePosition` (Snapshot `einzelpreis` + `menge`, nur für `type='verkauf'`). Kassenbestand = Σ(`einlage`+`verkauf`) − Σ(`entnahme`); siehe `Customer::kassenbestand()`. Konzept-Dokument: `KONZEPT_KASSE.md`.
- **Auth & roles**: `User` has `is_manager` / `is_admin` booleans. Vier custom route middlewares registered in `app/Http/Kernel.php`:
  - `isAdmin` → admin dashboard, import/export, fees (`gebuehr`), penalties (`strafe`), Betriebs-PIN-Verwaltung, lesender Zugriff auf alle Kassen.
  - `isManager` → may create customers.
  - `hasCustomer` (`SessionHasCustomer`) → most user actions require a selected customer in session; otherwise redirects to `choose/customer`.
  - `hasBetrieb` (`HasBetrieb`) → schützt `/betrieb/*`-Routen; verlangt `session('betrieb')` (per PIN gesetzt), sonst Redirect auf `/betrieb/login`. **Komplett getrennt** vom `auth`-Stack — Betriebe haben keinen `User`-Account.
- **Session-scoped "current customer"**: nearly every controller reads `session('customer')` (set by `CustomerController::setCustomer`, cleared by `new`). Treat it as an implicit request param. After mutating the customer, the session copy can be stale — re-fetch if you rely on fresh fields (`storeEinzahlen` updates DB then re-reads via `session('customer')->kredit`). Die Betriebs-Kasse nutzt analog `session('betrieb')` (nur **ein** aktiver Login pro Betrieb).
- **Routes**: all wired in `routes/web.php` only (api.php unused). German URL slugs: `einzahlen`, `auszahlen`, `kredit`, `ueberweisung`, `arbeitszeit`, `kontostand`, `gebuehr`, `strafe`. The public `/kontostand` flow is **outside** auth — customers enter a `key` (see `KontostandController`) to view balance. Zwei zusätzliche Route-Bäume:
  - `/betrieb/*` (Prefix-Group, Middleware `hasBetrieb` für alles außer Login/Logout): Controller unter `app/Http/Controllers/Betrieb/` (`BetriebController`, `ProduktController`, `KasseController`, `AbrechnungController`). Bank-Routen (`/einzahlen`, `/log`, …) sind für Betriebs-Sessions **gesperrt** (kein `auth`).
  - `/admin/betriebe/*` (Middleware `auth`+`isAdmin`): `AdminBetriebController` für PIN-Vergabe, Kassenjournal-Einsicht, Transaktions-Korrektur (`DELETE /admin/betriebe/transaktion/{transaktion}`).
- **Config knobs** in `config/bank.php` (read via `config('bank.*')`): `startkapital`, `konto_gebuehr`, `gewinn_steuer`, `zinsen`, `lohn.chef|mitarbeiter`, etc. Prefer adding new tunables here over hardcoding.
- **Helpers**: `app/helpers.php` is autoloaded (composer `files`). Add global helpers there, wrapped in `if (! function_exists(...))`.

## Conventions & patterns

- **Form requests** live in `app/Http/Requests/` (`PaymentRequest`, `KreditRequest`, `CreateCustomerRequest`, `WorkingTimeRequest`). Use them for validation; inline `$request->validate()` is acceptable for one-offs (see `storeUeberweisung`).
- **Flash messages**: always `->with(['type' => 'success|error|warning|danger', 'Meldung' => '...'])`. The layout (`resources/views/layouts/app.blade.php`) renders these — keep the keys exactly (`type`, `Meldung`, German text).
- **Money mutations = new `Payment` row**, never edit an existing one. Use signed `amount` and a German `comment` string (e.g. `'Einzahlung'`, `'Auszahlung'`, `'Kredit'`, `'Rückzahlung Kredit: X Radi'`, `'Überweisung an/von <Name>: <reason>'`). `daily_balance()` excludes `Kredit` and `Startkapital` by `comment LIKE`, so reuse those exact strings.
- **Kasse-Mutationen = neue `KasseTransaktion`-Zeile** (+ ggf. `KassePosition`-Zeilen für Verkäufe). Niemals Bank-`Payment` und `KasseTransaktion` mischen — die Kasse ist ein eigener Bar-Ledger ohne Konto-Bezug. Vor `entnahme` Kassenbestand prüfen (kein Negativwerden).
- **Views**: Bank-UI nutzt Bootstrap 5 + `layouts.app`. Betriebs-Kasse hat ein **eigenes** Layout `resources/views/betrieb/layouts/app.blade.php` (Tailwind, keine Bank-Navbar) — neue Betriebs-Views immer dieses Layout extenden.
- **Assets via Vite**: in Blade `@vite(['resources/css/app.css', 'resources/js/app.js'])` benutzen (Custom-Direktive aus `App\Providers\ViteServiceProvider` + `App\Helpers\Vite::tags()`, liest `public/build/manifest.json`). Kein `mix()`-Helper mehr.
- **Typos are load-bearing**: `buisness` (everywhere), `costumer` route prefix + migration filename `create_costumers_table`, `buissnes_id` FK on `PaymentBonus`. Don't "fix" them silently — DB columns depend on them.
- **Views** under `resources/views/{admin,auth,betrieb,customer,kontostand,workingtimes,layouts}/`. Bank-Views extenden `layouts.app`, Betriebs-Views `betrieb.layouts.app`. Customer-facing forms live under `customer/`, Betriebs-Kasse unter `betrieb/{kasse,produkte,abrechnung}/`.
- **Numeric ids vs models**: route-model binding is used for `{customer}`, `{payment}`, `{working_time}`. `Customer::find($request->buisness)` is used when the id comes from a form select.

## Workflows

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve            # http://127.0.0.1:8000

npm install
npm run dev                  # Vite dev server (HMR)
npm run build                # production build → public/build (+ manifest.json)
npm run prod                 # alias for build

php artisan test             # PHPUnit (tests/Feature, tests/Unit) — currently stubs only
vendor\bin\phpunit
```

- Registration is gated by `REGISTER` env var (`Auth::routes(['register' => env('REGISTER', false)])`). Set `REGISTER=true` in `.env` to enable signup.
- Wage envs: `LOHN_CHEF`, `LOHN_MITARBEITER`.
- Debugbar is auto-enabled in non-production (`config/debugbar.php`).
- Excel import flow: `AdminController::storeImport` → `App\Imports\CustomerImport` (Maatwebsite). Export route: `GET /export`.

## When adding a feature

1. Add migration in `database/migrations/` (one column change per file — see `add_key_to_consumers_table`, `add_betrieb_pin_to_customers_table`).
2. Update `$fillable` **and** `$visible` on the model if you want the field serialized (see `Customer`).
3. Add route inside the correct middleware group in `routes/web.php` (`auth` + one of `hasCustomer` / `isManager` / `isAdmin`, **oder** `hasBetrieb` für Kassen-Funktionen). Alltagsfunktionen → `hasCustomer`, nicht `isAdmin`. Betriebs-Funktionen → `hasBetrieb` (kein `auth`!), Admin-Einsicht auf Betriebsdaten → `auth`+`isAdmin` unter `/admin/betriebe/*`.
4. Validate via a FormRequest in `app/Http/Requests/`.
5. For balance-affecting actions, insert a `Payment` row instead of mutating state; if it's a 2-sided transfer (Betrieb ↔ Kind), link both rows via `payment_id`. Für Kassenvorgänge stattdessen eine `KasseTransaktion` (+ ggf. `KassePosition`) anlegen — Kasse und Bank-Ledger niemals verknüpfen.
6. Redirect with `['type' => ..., 'Meldung' => ...]` in German.
7. **Kind-Test:** Vor dem Mergen prüfen, ob ein Grundschulkind den Flow ohne Erklärung durchklicken kann — Buttontexte, Reihenfolge der Felder, Fehlermeldungen entsprechend anpassen.



