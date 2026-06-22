<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktienBestand extends Model
{
    protected $table = 'aktien_bestaende';

    protected $fillable = ['customer_id', 'buisness_id', 'stueck'];

    protected $casts = [
        'stueck' => 'integer',
    ];

    public function kind()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function betrieb()
    {
        return $this->belongsTo(Customer::class, 'buisness_id');
    }
}

