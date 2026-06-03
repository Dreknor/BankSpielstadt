<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FotostudioBild extends Model
{
    protected $table = 'fotostudio_bilder';

    protected $fillable = [
        'customer_id', 'titel', 'dateiname', 'pfad',
        'sichtbar', 'anzeige_von', 'anzeige_bis', 'anzeige_dauer', 'reihenfolge',
    ];

    protected $casts = [
        'sichtbar'      => 'boolean',
        'anzeige_von'   => 'datetime',
        'anzeige_bis'   => 'datetime',
        'anzeige_dauer' => 'integer',
        'reihenfolge'   => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Öffentliche URL des Bildes (storage/public disk). */
    public function url(): string
    {
        return Storage::disk('public')->url($this->pfad);
    }

    /** Ist das Bild aktuell in der Slideshow sichtbar? */
    public function istAktiv(): bool
    {
        if (! $this->sichtbar) {
            return false;
        }
        $jetzt = now();
        if ($this->anzeige_von && $this->anzeige_von->gt($jetzt)) {
            return false;
        }
        if ($this->anzeige_bis && $this->anzeige_bis->lt($jetzt)) {
            return false;
        }
        return true;
    }

    /** Label für die Zeitbegrenzung. */
    public function zeitLabel(): string
    {
        if ($this->anzeige_von === null && $this->anzeige_bis === null) {
            return 'Unbegrenzt';
        }
        $von = $this->anzeige_von?->format('d.m.Y H:i') ?? '–';
        $bis = $this->anzeige_bis?->format('d.m.Y H:i') ?? '–';
        return "Von {$von} bis {$bis}";
    }
}

