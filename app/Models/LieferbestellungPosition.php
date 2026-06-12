<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LieferbestellungPosition extends Model
{
    protected $table = 'lieferbestellung_positionen';

    protected $fillable = ['bestellung_id', 'product_id', 'menge', 'einzelpreis'];

    public function bestellung(): BelongsTo
    {
        return $this->belongsTo(Lieferbestellung::class, 'bestellung_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

