<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AktienVerkaufRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'stueck'      => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Bitte ein Kind auswählen.',
            'stueck.min'           => 'Mindestens 1 Anteil verkaufen.',
        ];
    }
}

