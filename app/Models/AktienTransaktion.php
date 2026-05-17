<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AktienTransaktion extends Model
{
    use SoftDeletes;

    protected $table = 'aktien_transaktionen';

    protected $fillable = [
        'customer_id', 'buisness_id', 'typ', 'stueck',
        'kurs', 'summe', 'boerse_rolle', 'payment_id', 'notiz',
    ];

    public function kind()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function betrieb()
    {
        return $this->belongsTo(Customer::class, 'buisness_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}

