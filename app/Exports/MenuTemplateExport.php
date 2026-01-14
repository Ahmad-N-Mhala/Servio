<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MenuTemplateExport implements WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'Category Name (EN) *',
            'Category Name (AR)',
            'Item Name (EN) *',
            'Item Name (AR)',
            'Description (EN)',
            'Description (AR)',
            'Price *',
            'Type (item/meal)',
            'Is Available (1/0)',
            'Sort Order',
        ];
    }

    public function title(): string
    {
        return 'Menu Items Template';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
