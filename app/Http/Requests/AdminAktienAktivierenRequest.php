<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAktienAktivierenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'    => 'required|integer|exists:customers,id',
            'aktien_gesamt'  => 'required|integer|min:1|max:1000',
            'aktien_kurs'    => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'aktien_gesamt.required' => 'Bitte eine Stückzahl eingeben.',
            'aktien_kurs.required'   => 'Bitte einen Startkurs eingeben.',
        ];
    }
}

