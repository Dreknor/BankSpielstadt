<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return  Customer::firstOrCreate([
            'name'    => $row['name'] ?? $row[0],
        ],
        [
            'buisness' => $row['buisness'] ?? NULL,
            'startkapital' => $row['startkapital'] ?? config('bank.startkapital'),
            'key'   => $row['key'] ?? NULL,
            'export' => $row['export'] ?? 0,
            'kredit' => $row['kredit'] ?? 0,
            'is_boerse' => $row['is_boerse'] ?? 0,
            'is_fotostudio' => $row['is_fotostudio'] ?? 0,
            'betrieb_pin'   => $row['betrieb_pin']   ?? null,
            'aktien_gesamt' => $row['aktien_gesamt'] ?? config('bank.aktien.standard_gesamt'),
            'aktien_kurs' => $row['start_kurs'] ?? config('bank.aktien.start_kurs'),

        ]);
    }
}
