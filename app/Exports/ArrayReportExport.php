<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArrayReportExport implements FromArray, ShouldAutoSize, WithHeadings
{
    /**
     * @param  array<string, string>  $columns
     * @param  list<array<string, string>>  $rows
     */
    public function __construct(
        private readonly array $columns,
        private readonly array $rows,
    ) {
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return array_values($this->columns);
    }

    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        $keys = array_keys($this->columns);

        return array_map(
            fn (array $row) => array_map(
                fn (string $key) => $row[$key] ?? '',
                $keys,
            ),
            $this->rows,
        );
    }
}
