<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KasseTransaktion extends Model
{
    protected $table = 'kasse_transaktionen';

    protected $fillable = ['customer_id', 'type', 'amount', 'comment'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function positionen(): HasMany
    {
        return $this->hasMany(KassePosition::class, 'transaktion_id');
    }
}

