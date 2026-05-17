<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KassePosition extends Model
{
    protected $table = 'kasse_positionen';

    protected $fillable = ['transaktion_id', 'product_id', 'menge', 'einzelpreis'];

    public function transaktion(): BelongsTo
    {
        return $this->belongsTo(KasseTransaktion::class, 'transaktion_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

