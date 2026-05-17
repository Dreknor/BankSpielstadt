<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoerseBeobachtung extends Model
{
    protected $table = 'boerse_beobachtungen';

    public $timestamps = false;
    protected $dates   = ['created_at'];

    protected $fillable = ['buisness_id', 'angestellte', 'notiz', 'created_at'];

    public function betrieb()
    {
        return $this->belongsTo(Customer::class, 'buisness_id');
    }
}

