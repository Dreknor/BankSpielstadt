<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ImportVorlageExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function headings(): array
    {
        return [
            'name',
            'buisness',
            'startkapital',
            'kredit',
            'betrieb_pin',
            'key',
            'export',
            'is_boerse',
            'is_fotostudio',
            'aktien_gesamt',
            'start_kurs',
        ];
    }

    public function array(): array
    {
        //            name               bus  start  kred  pin    key  exp  börs  foto  akt   kurs
        return [
            ['Max Mustermann',            0,  '',    0,    '',    '',  0,   0,    0,    '',   ''],
            ['Bäckerei Müller',           1,  200,   0,    '1234','',  1,   0,    0,    '',   ''],
            ['Radi-Börse GmbH (Beisp.)', 1,  500,   0,    '9999','',  0,   1,    0,    '',   ''],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // name
            'B' => 12, // buisness
            'C' => 14, // startkapital
            'D' => 10, // kredit
            'E' => 14, // betrieb_pin
            'F' => 20, // key
            'G' => 10, // export
            'H' => 12, // is_boerse
            'I' => 15, // is_fotostudio
            'J' => 16, // aktien_gesamt
            'K' => 12, // start_kurs
        ];
    }
}
