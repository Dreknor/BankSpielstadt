<?php

namespace App\Console\Commands;

use App\Models\AktienTransaktion;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Phase 4 – Arbitrage-Auswertung
 *
 * Analysiert alle Kauf-Verkauf-Paare je Kind via FIFO-Matching und identifiziert:
 * - Schnellverkäufe (Haltedauer < arbitrage_schnellverkauf_min Minuten)
 * - Kursdifferenz-Gewinne (Vk-Kurs − Kauf-Kurs ≥ arbitrage_min_gewinn Radi/Anteil)
 *
 * Aufruf:
 *   php artisan aktien:arbitrage-report
 *   php artisan aktien:arbitrage-report --datum=2026-06-22
 *   php artisan aktien:arbitrage-report --datum=alle
 */
class AktienArbitrageReport extends Command
{
    protected $signature = 'aktien:arbitrage-report
                            {--datum=heute : Datum (YYYY-MM-DD), "heute" oder "alle"}';

    protected $description = 'Analysiert Kauf-Verkauf-Paare auf Arbitrage-Muster via FIFO-Matching (Phase 4)';

    public function handle(): int
    {
        $datumOpt = $this->option('datum');
        $datum    = match (true) {
            $datumOpt === 'heute' => today()->toDateString(),
            $datumOpt === 'alle'  => null,
            default               => $datumOpt,
        };

        $schnellMin = (int) config('bank.aktien.arbitrage_schnellverkauf_min', 30);
        $minGewinn  = (int) config('bank.aktien.arbitrage_min_gewinn', 1);

        $label = $datum ?? 'alle Daten';
        $this->info("=== Arbitrage-Report: {$label} ===");
        $this->line("Schnellverkauf < {$schnellMin} Min · Verdächtig wenn Δ Kurs ≥ {$minGewinn} Radi/Anteil");

        $paare = static::berechne($datum);

        if ($paare->isEmpty()) {
            $this->info('✅ Keine auswertbaren Kauf-Verkauf-Paare gefunden.');
            return 0;
        }

        $verdaechtig = $paare->where('verdaechtig', true);
        $schnell     = $paare->where('schnellverkauf', true);

        $this->line('');
        $this->info("Paare: {$paare->count()} | Verdächtig: {$verdaechtig->count()} | Schnellverkäufe: {$schnell->count()}");

        $rows = $paare->sortByDesc('gewinn_gesamt')->map(fn ($p) => [
            $p['kind'],
            $p['betrieb'],
            $p['stueck'],
            $p['kauf_kurs']      . ' R',
            $p['verkauf_kurs']   . ' R',
            $p['haltedauer_min'] . ' Min',
            ($p['gewinn_gesamt'] >= 0 ? '+' : '') . $p['gewinn_gesamt'] . ' Radi',
            $p['schnellverkauf'] ? '⚡' : '',
            $p['verdaechtig']    ? '🚨' : '',
        ])->values()->toArray();

        $this->table(
            ['Kind', 'Betrieb', 'Stück', 'Kauf', 'Vk', 'Haltezeit', 'Gewinn', 'Schnell', 'Verdächtig'],
            $rows
        );

        $gesamtGewinn = $verdaechtig->sum('gewinn_gesamt');
        if ($gesamtGewinn > 0) {
            $this->warn("⚠️  Verdächtige Kursgewinne gesamt: {$gesamtGewinn} Radi");
        }

        return 0;
    }

    /**
     * FIFO-Matching aller Kauf-Verkauf-Paare.
     *
     * Käufe werden geladen (ohne Datumsfilter, für korrekte FIFO-Basis über
     * Tagesgrenzen hinweg). Verkäufe werden optional auf ein Datum gefiltert.
     *
     * @param  string|null  $datum  'YYYY-MM-DD' filtert Verkäufe auf dieses Datum; null = alle Verkäufe
     */
    public static function berechne(?string $datum = null): Collection
    {
        $schnellMin = (int) config('bank.aktien.arbitrage_schnellverkauf_min', 30);
        $minGewinn  = (int) config('bank.aktien.arbitrage_min_gewinn', 1);

        // Alle Käufe laden (vollständige FIFO-Basis)
        $alleKaeufe = AktienTransaktion::with(['kind', 'betrieb'])
            ->whereNull('deleted_at')
            ->where('typ', 'kauf')
            ->orderBy('customer_id')
            ->orderBy('buisness_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($t) => $t->customer_id . '_' . $t->buisness_id);

        // Verkäufe (optional datumsegefiltert)
        $vkQuery = AktienTransaktion::with(['kind', 'betrieb'])
            ->whereNull('deleted_at')
            ->whereIn('typ', ['verkauf', 'rueckkauf'])
            ->orderBy('created_at');

        if ($datum) {
            $vkQuery->whereDate('created_at', $datum);
        }

        $alleVerkäufe = $vkQuery->get();

        // FIFO-Queues je (customer_id, buisness_id) aufbauen
        $queues = [];
        foreach ($alleKaeufe as $key => $kaeufe) {
            $queues[$key] = $kaeufe->map(fn ($k) => [
                'stueck' => $k->stueck,
                'kurs'   => $k->kurs,
                'time'   => $k->created_at,
            ])->toArray();
        }

        $paare = collect();

        foreach ($alleVerkäufe as $vk) {
            $key = $vk->customer_id . '_' . $vk->buisness_id;

            if (empty($queues[$key])) {
                continue; // kein passender Kauf (z. B. Rückkauf durch den Betrieb selbst)
            }

            $zuMatchende = $vk->stueck;

            while ($zuMatchende > 0 && !empty($queues[$key])) {
                $kauf = array_shift($queues[$key]);

                // Käufe die nach dem Verkauf liegen können nicht matched werden
                if ($kauf['time'] >= $vk->created_at) {
                    array_unshift($queues[$key], $kauf);
                    break;
                }

                $match        = min($kauf['stueck'], $zuMatchende);
                $zuMatchende -= $match;

                // Rest zurück in Queue
                if ($kauf['stueck'] > $match) {
                    array_unshift($queues[$key], [
                        'stueck' => $kauf['stueck'] - $match,
                        'kurs'   => $kauf['kurs'],
                        'time'   => $kauf['time'],
                    ]);
                }

                $kursGewinn    = $vk->kurs - $kauf['kurs'];
                $gewinnGesamt  = $match * $kursGewinn;
                $haltedauerMin = (int) $kauf['time']->diffInMinutes($vk->created_at);

                $paare->push([
                    'kind'           => $vk->kind?->name  ?? "ID {$vk->customer_id}",
                    'betrieb'        => $vk->betrieb?->name ?? "ID {$vk->buisness_id}",
                    'customer_id'    => $vk->customer_id,
                    'stueck'         => $match,
                    'kauf_kurs'      => $kauf['kurs'],
                    'verkauf_kurs'   => $vk->kurs,
                    'kurs_delta'     => $kursGewinn,
                    'gewinn_gesamt'  => $gewinnGesamt,
                    'haltedauer_min' => $haltedauerMin,
                    'schnellverkauf' => $haltedauerMin < $schnellMin,
                    'verdaechtig'    => $kursGewinn >= $minGewinn && $gewinnGesamt > 0,
                ]);
            }
        }

        return $paare;
    }
}
