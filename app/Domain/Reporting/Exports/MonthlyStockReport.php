<?php

namespace App\Domain\Reporting\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Workbook laporan bulanan barang habis pakai: sheet "Semua Barang" + satu sheet per kategori. */
class MonthlyStockReport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        public int $organizationId,
        public ?int $unitId,
        public int $year,
        public int $month,
    ) {}

    public function sheets(): array
    {
        $categories = DB::table('products as p')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.organization_id', $this->organizationId)
            ->where('p.type', 'CONSUMABLE')
            ->whereNull('p.deleted_at')
            ->when($this->unitId, fn ($q, $v) => $q->where('p.unit_id', $v))
            ->select('c.id', 'c.name')
            ->distinct()
            ->orderBy('c.name')
            ->get();

        $sheets = [new MonthlyStockSheet($this->organizationId, $this->unitId, $this->year, $this->month, null, 'Semua Barang')];

        foreach ($categories as $c) {
            $sheets[] = new MonthlyStockSheet($this->organizationId, $this->unitId, $this->year, $this->month, $c->id, $c->name);
        }

        return $sheets;
    }
}
