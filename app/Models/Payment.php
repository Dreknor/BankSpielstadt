<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'source_id', 'amount', 'comment', 'user_id'];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function banker(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', '>', Carbon::yesterday())
            ->whereDate('created_at', '<', Carbon::tomorrow());
    }

}
