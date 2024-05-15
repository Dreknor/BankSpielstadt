<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentBonus extends Model
{
    use SoftDeletes;

    protected $table = 'payment_bonus';

    protected $fillable = ['buissnes_id', 'start', 'end', 'bonus', 'bonus_type'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'buissnes_id');
    }


}
