<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'name', 'price', 'active'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function positionen(): HasMany
    {
        return $this->hasMany(KassePosition::class, 'product_id');
    }
}

