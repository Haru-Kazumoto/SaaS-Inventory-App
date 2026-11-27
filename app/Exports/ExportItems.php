<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportItems implements WithMultipleSheets
{
    public function __construct(
        private string $major
    ) {}

    public function sheets(): array
    {
        return [
            new ExportConsumableItem($this->major),
            new ExportDurableItem($this->major),
            new ExportSummaryDurableItem($this->major)
        ];
    }
}
