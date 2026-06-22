<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'buisness', 'startkapital', 'kredit', 'key', 'export', 'betrieb_pin',
                            'aktien_gesamt', 'aktien_kurs', 'aktien_startkurs', 'aktien_letzte_berechnung',
                            'is_boerse', 'boerse_handel_gesperrt', 'is_fotostudio', 'fotostudio_token', 'is_support',
                            'is_lieferdienst', 'lieferkosten'];
    protected $visible = ['id','name', 'buisness', 'startkapital', 'kredit','key','export',
                          'aktien_gesamt', 'aktien_kurs', 'is_boerse', 'is_fotostudio', 'is_support',
                          'is_lieferdienst', 'lieferkosten'];

    protected $casts = [
        'is_boerse'               => 'boolean',
        'boerse_handel_gesperrt'  => 'boolean',
        'is_fotostudio'           => 'boolean',
        'is_support'              => 'boolean',
        'is_lieferdienst'         => 'boolean',
        'buisness'                => 'integer',
    ];


    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function working_times(): HasMany
    {
        return $this->hasMany(WorkingTime::class);
    }

    public function worker(): HasMany
    {
        return $this->hasMany(WorkingTime::class, 'buisness_id');
    }

    public function is_buisness(): bool
    {
        return $this->buisness == 1;
    }

    public function daily_balance($day = null)
    {
        if ($day == null){
            $day = Carbon::today();
        }
        return $this->payments()
                ->whereDate('created_at', '=',$day)
                ->whereNot('comment', 'LIKE', 'Kredit')
                ->whereNot('comment', 'LIKE', 'Startkapital')
                ->sum('amount');

    }

    /**
     * Operativer Tagesgewinn als Basis für die Dividende.
     *
     * Klammert reine Börsen-Kapitalflüsse aus (Anteilskauf/-verkauf/-rückkauf,
     * Dividende, Schlussabrechnung). Sonst würde eingesammeltes Anlegergeld als
     * "Gewinn" gewertet und die Dividende auf Kapital statt auf echten Umsatz
     * gezahlt werden.
     */
    public function operativerTagesgewinn($day = null)
    {
        if ($day == null) {
            $day = Carbon::today();
        }
        return (int) $this->payments()
                ->whereDate('created_at', '=', $day)
                ->whereNot('comment', 'LIKE', 'Kredit')
                ->whereNot('comment', 'LIKE', 'Startkapital')
                ->whereNot('comment', 'LIKE', 'Börse:%')
                ->whereNot('comment', 'LIKE', 'Dividende%')
                ->whereNot('comment', 'LIKE', 'Schlussabrechnung%')
                ->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->payments()
                ->sum('amount');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function kasseTransaktionen(): HasMany
    {
        return $this->hasMany(KasseTransaktion::class);
    }

    public function kassenbestand(): int
    {
        $ein = $this->kasseTransaktionen()->whereIn('type', ['einlage', 'verkauf'])->sum('amount');
        $aus = $this->kasseTransaktionen()->where('type', 'entnahme')->sum('amount');
        return $ein - $aus;
    }

    public function bonus()
    {
        return $this->hasMany( related: PaymentBonus::class, foreignKey: 'buissnes_id');
    }

    // ── Radi-Börse ────────────────────────────────────────────────────────────

    public function hatAktien(): bool
    {
        return $this->aktien_gesamt !== null;
    }

    public function anteileVerkauft(): int
    {
        return AktienBestand::where('buisness_id', $this->id)->sum('stueck');
    }

    public function anteileEigen(): int
    {
        return ($this->aktien_gesamt ?? 0) - $this->anteileVerkauft();
    }

    public function kursVerlauf()
    {
        return $this->hasMany(AktienKurs::class, 'buisness_id');
    }

    public function beobachtungen()
    {
        return $this->hasMany(BoerseBeobachtung::class, 'buisness_id');
    }

    public function aktienBestaende()
    {
        return $this->hasMany(AktienBestand::class, 'buisness_id');
    }

    public function aktienPortfolio()
    {
        return $this->hasMany(AktienBestand::class, 'customer_id');
    }

    public function letzteBeobachtung(): ?int
    {
        return $this->beobachtungen()->latest('created_at')->value('angestellte');
    }

    public function isBoerse(): bool
    {
        return (bool) $this->is_boerse;
    }

    /** Gibt true zurück, wenn dieses Kind/dieser Betrieb vom Börsenhandel ausgeschlossen ist. */
    public function handelGesperrt(): bool
    {
        return (bool) ($this->boerse_handel_gesperrt ?? false);
    }

    /**
     * Gewichteter Durchschnittskaufkurs dieses Kunden für einen bestimmten Betrieb.
     * Basis: alle nicht-gelöschten Kauf-Transaktionen.
     * Wird beim Verkauf als Preisdeckel verwendet (kein Gewinn über Einkaufspreis).
     */
    public function avgKaufKurs(int $buisnessId): int
    {
        $row = AktienTransaktion::where('customer_id', $this->id)
            ->where('buisness_id', $buisnessId)
            ->whereNull('deleted_at')
            ->where('typ', 'kauf')
            ->selectRaw('SUM(summe) AS total_summe, SUM(stueck) AS total_stueck')
            ->first();

        if (!$row || (int) $row->total_stueck === 0) {
            return 0;
        }
        return (int) ceil($row->total_summe / $row->total_stueck);
    }

    public function isFotostudio(): bool
    {
        return (bool) $this->is_fotostudio;
    }

    public function isSupport(): bool
    {
        return (bool) $this->is_support;
    }

    public static function supportBetrieb(): ?self
    {
        return static::where('is_support', true)->first();
    }

    public function hilferufe()
    {
        return $this->hasMany(\App\Models\Hilferuf::class, 'customer_id');
    }

    // ── Lieferdienst ──────────────────────────────────────────────────────────

    public function isLieferdienst(): bool
    {
        return (bool) $this->is_lieferdienst;
    }

    public function lieferprodukte()
    {
        return $this->hasMany(LieferdienstProdukt::class, 'lieferdienst_id');
    }

    public function lieferbestellungen()
    {
        return $this->hasMany(Lieferbestellung::class, 'lieferdienst_id');
    }

    public function fotostudioBilder()
    {
        return $this->hasMany(FotostudioBild::class);
    }

    /** Liefert den als Börse markierten Customer (falls vorhanden). */
    public static function boerseBetrieb(): ?self
    {
        return static::where('is_boerse', true)->first();
    }



    /**
     * Scope a query to only include buissnes customers.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeBuisness(Builder $query): void
    {
        $query->where('buisness', 1);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('sorted', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }
}
