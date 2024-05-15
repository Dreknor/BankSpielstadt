<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkingTime extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'buisness_id', 'user_id','is_manager','start','end', 'payment_customer', 'payment_buisness'];
    protected $visible = ['id','customer_id', 'buisness_id', 'user_id','is_manager','start','end', 'payment_customer', 'payment_buisness'];

    protected $casts = [
        'start' => 'datetime',
        'end'   => 'datetime',
    ];

    public function is_manager()
    {
        return ($this->is_manager == 1)? true : false;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function buisness()
    {
        return $this->belongsTo(Customer::class, 'buisness_id');
    }
    public function payment_customer()
    {
        return $this->belongsTo(Payment::class, 'payment_customer');
    }
    public function payment_buisness()
    {
        return $this->belongsTo(Payment::class, 'payment_buisness');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute()
    {
        return $this->start->diffInMinutes($this->end);
    }
}
