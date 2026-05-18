<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BoerseKasseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'betrag'          => 'required|integer|min:1',
            'notiz'           => 'nullable|string|max:120',
            'ausgefuehrt_von' => 'required|integer|exists:customers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'betrag.required'          => 'Bitte einen Betrag eingeben.',
            'betrag.min'               => 'Mindestens 1 Radi.',
            'ausgefuehrt_von.required' => 'Bitte den Namen der ausführenden Person auswählen.',
            'ausgefuehrt_von.exists'   => 'Diese Person wurde nicht gefunden.',
        ];
    }
}

