<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoerseKasse extends Model
{
    protected $table = 'boerse_kasse';

    public $timestamps = false;
    protected $dates   = ['created_at'];

    protected $fillable = ['typ', 'betrag', 'notiz', 'aktien_transaktion_id', 'created_at'];

    public function aktienTransaktion()
    {
        return $this->belongsTo(AktienTransaktion::class, 'aktien_transaktion_id');
    }

    /** Aktueller Bargeld-Kassenstand der Börse */
    public static function kassenstand(): int
    {
        $ein = self::whereIn('typ', ['kauf_einnahme', 'rueckkauf_einnahme', 'einlage', 'gebuehr_einnahme'])->sum('betrag');
        $aus = self::whereIn('typ', ['verkauf_auszahlung', 'rueckkauf_auszahlung', 'entnahme', 'abschluss_auszahlung'])->sum('betrag');
        return $ein - $aus;
    }
}


