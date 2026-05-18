<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warenkorb extends Model
{
    protected $table = 'warenkoerbe';

    protected $fillable = ['customer_id', 'inhalt'];

    protected $casts = [
        'inhalt' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

