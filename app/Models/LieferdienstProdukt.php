<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LieferdienstProdukt extends Model
{
    protected $table = 'lieferdienst_produkte';

    protected $fillable = ['lieferdienst_id', 'product_id'];

    public function lieferdienst(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'lieferdienst_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

