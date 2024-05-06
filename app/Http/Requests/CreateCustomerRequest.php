<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() and auth()->user()->is_manager;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string'],
            'buisness' => ['integer','min:0', 'max:1'],
            'startkapital' => ['nullable','integer'],
            'key' => ['string','nullable'],
        ];
    }
}
