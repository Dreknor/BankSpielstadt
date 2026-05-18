<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoerseAufgabenLog extends Model
{
    protected $table = 'boerse_aufgaben_log';

    public $timestamps = false;
    protected $dates   = ['created_at'];

    protected $fillable = ['aufgabe', 'mitarbeiter', 'created_at'];
}

