<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'buisness', 'startkapital', 'kredit','key','export'];
    protected $visible = ['id','name', 'buisness', 'startkapital', 'kredit','key','export'];


    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function working_times(): HasMany
    {
        return $this->hasMany(WorkingTime::class);
    }

    public function worker(): HasMany
    {
        return $this->hasMany(WorkingTime::class, 'buisness_id');
    }

    public function is_buisness(): bool
    {
        return $this->buisness == 1;
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

    public function getBalanceAttribute()
    {
        return $this->payments()
                ->sum('amount');
    }



    /**
     * Scope a query to only include buissnes customers.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeBuisness(Builder $query): void
    {
        $query->where('buisness', 1);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('sorted', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }
}
