<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'buisness', 'startkapital', 'kredit','key','export'];
    protected $visible = ['id','name', 'buisness', 'startkapital', 'kredit','key','export'];



    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute(){
        return $this->payments()->sum('amount');
    }

    public function working_times(){
        return $this->hasMany(WorkingTime::class);
    }

    public function worker(){
        return $this->hasMany(WorkingTime::class, 'buisness_id');
    }

    public function is_buisness()
    {
        return ($this->buisness == 1)? true : false;
    }

    public function daily_balance($day = null)
    {
        if ($day == null){
            $day = Carbon::today();
        }
        return $this->payments()
                ->whereDate('created_at', '=',$day)
                ->whereNot('comment', 'LIKE', 'Kredit')
                ->whereNot('comment', 'LIKE', 'Startkapital')
                ->sum('amount');

    }

    /**
     * Scope a query to only include buissnes customers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeBuisness($query)
    {
        $query->where('buisness', 1);
    }

    protected static function booted()
    {
        static::addGlobalScope('sorted', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }
}
