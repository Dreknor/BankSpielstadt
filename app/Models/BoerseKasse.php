<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoerseKasse extends Model
{
    protected $table = 'boerse_kasse';

    public $timestamps = false;
    protected $dates   = ['created_at'];

    protected $fillable = ['typ', 'betrag', 'saldo_nach', 'notiz', 'ausgefuehrt_von', 'aktien_transaktion_id', 'created_at'];

    protected $casts = [
        'betrag'     => 'integer',
        'saldo_nach' => 'integer',
    ];

    public function aktienTransaktion()
    {
        return $this->belongsTo(AktienTransaktion::class, 'aktien_transaktion_id');
    }

    public function ausfuehrer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'ausgefuehrt_von');
    }

    /** Buchungstypen, die der Kasse Bargeld zuführen. */
    public const EINNAHME_TYPEN = ['kauf_einnahme', 'rueckkauf_einnahme', 'einlage', 'gebuehr_einnahme'];

    /** Buchungstypen, die der Kasse Bargeld entnehmen. */
    public const AUSGABE_TYPEN = ['verkauf_auszahlung', 'rueckkauf_auszahlung', 'entnahme', 'abschluss_auszahlung'];

    /** Aktueller Bargeld-Kassenstand der Börse */
    public static function kassenstand(): int
    {
        $ein = self::whereIn('typ', self::EINNAHME_TYPEN)->sum('betrag');
        $aus = self::whereIn('typ', self::AUSGABE_TYPEN)->sum('betrag');
        return (int) ($ein - $aus);
    }

    /**
     * Bucht eine Kassenbewegung und schreibt den Laufsaldo (saldo_nach) mit.
     * Innerhalb einer DB-Transaktion mit Row-Lock aufrufen, damit der Saldo
     * konsistent bleibt. Ersetzt das direkte BoerseKasse::create(), damit jede
     * Bewegung lückenlos nachvollziehbar ist.
     */
    public static function buchen(array $attrs): self
    {
        $betrag = (int) ($attrs['betrag'] ?? 0);
        $delta  = in_array($attrs['typ'], self::AUSGABE_TYPEN, true) ? -$betrag : $betrag;

        $attrs['betrag']     = $betrag;
        $attrs['saldo_nach'] = self::kassenstand() + $delta;
        $attrs['created_at'] = $attrs['created_at'] ?? now();

        return self::create($attrs);
    }
}


