<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KreditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() and session()->has('customer');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'kredit' => ['required', 'min:0.5', 'numeric']
        ];
    }
}
