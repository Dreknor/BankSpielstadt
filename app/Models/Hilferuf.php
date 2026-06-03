<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hilferuf extends Model
{

    protected $table = 'hilferufe';
    protected $fillable = ['customer_id', 'nachricht', 'status', 'bearbeiter', 'notiz'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function betrieb(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /** Liefert das Label für den Status */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'offen'          => '🔴 Neu',
            'in_bearbeitung' => '🟡 Wird bearbeitet',
            'erledigt'       => '🟢 Erledigt',
            default          => $this->status,
        };
    }

    /** CSS-Klassen für die Statusanzeige */
    public function statusKlasse(): string
    {
        return match ($this->status) {
            'offen'          => 'bg-rose-100 border-rose-400 text-rose-900',
            'in_bearbeitung' => 'bg-amber-100 border-amber-400 text-amber-900',
            'erledigt'       => 'bg-emerald-100 border-emerald-400 text-emerald-900',
            default          => 'bg-slate-100 border-slate-400 text-slate-900',
        };
    }

    /** Scope: nur offene und in Bearbeitung */
    public function scopeAktiv($query)
    {
        return $query->whereIn('status', ['offen', 'in_bearbeitung']);
    }
}

