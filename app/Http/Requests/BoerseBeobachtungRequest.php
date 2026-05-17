<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BoerseBeobachtungRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'angestellte' => 'required|integer|min:0|max:50',
            'notiz'       => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'angestellte.required' => 'Bitte die Anzahl der Angestellten eingeben.',
            'angestellte.min'      => 'Die Zahl darf nicht kleiner als 0 sein.',
            'angestellte.max'      => 'Maximal 50 Angestellte können eingetragen werden.',
        ];
    }
}

