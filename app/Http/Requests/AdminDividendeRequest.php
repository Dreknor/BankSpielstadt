<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminDividendeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'prozent' => 'required|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'prozent.required' => 'Bitte einen Prozentsatz eingeben.',
            'prozent.min'      => 'Mindestens 1 %.',
            'prozent.max'      => 'Maximal 100 %.',
        ];
    }
}
