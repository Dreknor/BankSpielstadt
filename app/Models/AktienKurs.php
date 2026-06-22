<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktienKurs extends Model
{
    protected $table = 'aktien_kurse';

    public $timestamps = false;
    protected $dates   = ['created_at'];

    protected $fillable = ['buisness_id', 'kurs', 'vorher', 'grund', 'created_at'];

    protected $casts = [
        'kurs'   => 'integer',
        'vorher' => 'integer',
    ];

    public function betrieb()
    {
        return $this->belongsTo(Customer::class, 'buisness_id');
    }
}


