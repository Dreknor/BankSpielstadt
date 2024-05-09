<?php

namespace App\Imports;

use App\Models\Customer;
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
        ]);
    }
}
