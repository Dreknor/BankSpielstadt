<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AktienRueckkaufRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'stueck'      => 'required|integer|min:1',
        ];
    }
}

