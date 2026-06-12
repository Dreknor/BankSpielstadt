<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lieferbestellung extends Model
{
    protected $table = 'lieferbestellungen';

    protected $fillable = [
        'lieferdienst_id',
        'besteller_name',
        'lieferort',
        'status',
        'mitarbeiter_id',
        'gesamtbetrag',
    ];

    public function lieferdienst(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'lieferdienst_id');
    }

    public function mitarbeiter(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'mitarbeiter_id');
    }

    public function positionen(): HasMany
    {
        return $this->hasMany(LieferbestellungPosition::class, 'bestellung_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'neu'           => '🔴 Neu',
            'in_bearbeitung'=> '🟡 In Bearbeitung',
            'erledigt'      => '🟢 Erledigt',
            default         => $this->status,
        };
    }
}

