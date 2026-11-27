<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportProductsPerSubType implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        public int $major_id,
        public bool $withAllSheet = false,
        public ?int $year = null,
        public ?int $month = null,
    ) {}

    public function sheets(): array
    {
        $year  = $this->year  ?? now()->year;
        $month = $this->month ?? now()->month;

        // Ambil semua sub type yg dimiliki major ini untuk CONSUMABLE
        $subTypes = DB::table('products as p')
            ->join('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->where('p.major_id', $this->major_id)
            ->where('p.type', 'CONSUMABLE')
            ->select('st.code', 'st.name')
            ->distinct()
            ->orderBy('st.name')
            ->get();

        $sheets = [];

        if ($this->withAllSheet) {
            $sheets[] = new ExportProducts(
                major_id: $this->major_id,
                year: $year,
                month: $month,
                sub_type_code: null,
                sub_type_name: 'Semua Produk'
            );
        }

        foreach ($subTypes as $st) {
            $sheets[] = new ExportProducts(
                major_id: $this->major_id,
                year: $year,
                month: $month,
                sub_type_code: $st->code,   // <— filter dipakai di build()
                sub_type_name: $st->name
            );
        }

        return $sheets;
    }
}
