<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminDividendeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'buisness_id'     => 'required|integer|exists:customers,id',
            'radi_pro_anteil' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'buisness_id.required'     => 'Bitte einen Betrieb auswählen.',
            'radi_pro_anteil.required' => 'Bitte den Betrag pro Anteil eingeben.',
            'radi_pro_anteil.min'      => 'Mindestens 1 Radi pro Anteil.',
        ];
    }
}

