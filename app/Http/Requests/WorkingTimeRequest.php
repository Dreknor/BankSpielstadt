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
     * Führende Nullen in Minuten-Feldern entfernen (z. B. "00" → 0, "05" → 5),
     * damit die `integer`-Validierung nicht fälschlicherweise fehlschlägt.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'start_minute' => is_numeric($this->start_minute) ? (int) $this->start_minute : $this->start_minute,
            'end_minute'   => is_numeric($this->end_minute)   ? (int) $this->end_minute   : $this->end_minute,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $maxDay = Carbon::today()->dayOfWeek; // 1 = Mo … 5 = Fr, 0 = So
        // Am Montag (1) darf nur Tag 1 gewählt werden; nie 0 (Sonntag) oder Tage > heute
        return [
            'day'          => ['required', 'integer', 'min:1', 'max:' . max(1, $maxDay)],
            'buisness'     => ['required', 'integer', 'min:1'],
            'manager'      => ['required', 'integer', 'min:0', 'max:1'],
            'start_hour'   => ['required', 'integer', 'min:8', 'max:13'],
            'start_minute' => ['required', 'integer', 'min:0', 'max:59'],
            'end_hour'     => ['required', 'integer', 'min:8', 'max:13'],
            'end_minute'   => ['required', 'integer', 'min:0', 'max:59'],
        ];
    }

    public function messages()
    {
        return [
            // Wochentag
            'day.required' => 'Bitte wähle einen Wochentag aus.',
            'day.integer'  => 'Der Wochentag ist ungültig.',
            'day.min'      => 'Bitte wähle einen gültigen Wochentag aus.',
            'day.max'      => 'Du kannst keinen Tag wählen, der noch nicht war.',

            // Betrieb
            'buisness.required' => 'Bitte wähle einen Betrieb aus.',
            'buisness.integer'  => 'Der Betrieb ist ungültig.',
            'buisness.min'      => 'Bitte wähle einen Betrieb aus der Liste aus.',

            // Chef-Frage
            'manager.required' => 'Bitte gib an, ob der Kunde Chef in diesem Betrieb ist.',
            'manager.integer'  => 'Ungültige Eingabe bei der Chef-Frage.',
            'manager.min'      => 'Ungültige Eingabe bei der Chef-Frage.',
            'manager.max'      => 'Ungültige Eingabe bei der Chef-Frage.',

            // Anfangsstunde
            'start_hour.required' => 'Bitte gib die Anfangsstunde ein.',
            'start_hour.integer'  => 'Die Stunde muss eine ganze Zahl sein (z. B. 8 oder 10).',
            'start_hour.min'      => 'Die Anfangsstunde muss zwischen 8 und 13 Uhr liegen.',
            'start_hour.max'      => 'Die Anfangsstunde muss zwischen 8 und 13 Uhr liegen.',

            // Anfangsminute
            'start_minute.required' => 'Bitte gib die Anfangsminute ein.',
            'start_minute.integer'  => 'Gib die Minuten als Zahl ein (z. B. 0, 15, 30 oder 45).',
            'start_minute.min'      => 'Die Minuten müssen zwischen 0 und 59 liegen.',
            'start_minute.max'      => 'Die Minuten müssen zwischen 0 und 59 liegen.',

            // Endstunde
            'end_hour.required' => 'Bitte gib die Endstunde ein.',
            'end_hour.integer'  => 'Die Stunde muss eine ganze Zahl sein (z. B. 8 oder 10).',
            'end_hour.min'      => 'Die Endstunde muss zwischen 8 und 13 Uhr liegen.',
            'end_hour.max'      => 'Die Endstunde muss zwischen 8 und 13 Uhr liegen.',

            // Endminute
            'end_minute.required' => 'Bitte gib die Endminute ein.',
            'end_minute.integer'  => 'Gib die Minuten als Zahl ein (z. B. 0, 15, 30 oder 45).',
            'end_minute.min'      => 'Die Minuten müssen zwischen 0 und 59 liegen.',
            'end_minute.max'      => 'Die Minuten müssen zwischen 0 und 59 liegen.',
        ];
    }


}
