<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class WorkingTimeRequest extends FormRequest
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
            'day' => ['required', 'integer', 'min:0', 'max:6'],
            'buisness' => ['required', 'integer', 'min:1'],
            'manager' => ['required', 'integer', 'min:0', 'max:1'],
            'start_hour' => ['required', 'integer', 'min:8', 'max:13'],
            'start_minute' => ['required', 'integer', 'min:0', 'max:59'],
            'end_hour' => ['required', 'integer', 'min:8', 'max:13'],
            'end_minute' => ['required', 'integer', 'min:0', 'max:59']
        ];
    }
        public function messages()
    {
        return [
            'day.min' => 'Du musst den richtigen Wochentag auswählen',
            'day.max' => 'Du kannst keinen Wochentag wählen, der in der Zukunft liegt',
            'start_hour.min'  => 'Die Zeit muss zwischen 8 und 13 Uhr liegen',
            'start_minute.integer'  => 'Gib die Minuten korrekt an. Es darf keine null davor stehen',
            'end_hour.min'  => 'Die Zeit muss zwischen 8 und 13 Uhr liegen',
            'end_minute.integer'  => 'Gib die Minuten korrekt an. Es darf keine null davor stehen',
        ];
    }


}
