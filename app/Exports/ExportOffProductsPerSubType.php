<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportOffProductsPerSubType implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        public int $major_id,
        public ?string $product_name = null,
    ) {}

    public function sheets(): array
    {
        // Ambil semua sub type yang punya produk OFF untuk major ini
        $subTypes = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->join('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->where('p.major_id', $this->major_id)
            ->where('pl.condition', 'OFF')
            ->when(
                $this->product_name,
                fn($q, $pn) =>
                $q->where('p.name', 'LIKE', "%{$pn}%")
            )
            ->select('st.code', 'st.name')
            ->distinct()
            ->orderBy('st.name')
            ->get();

        $sheets = [];

        // Kalau mau sheet "Semua" di awal, bisa buka komentar ini:
        // $sheets[] = new ExportOffProducts(
        //     major_id: $this->major_id,
        //     sub_type: null,
        //     product_name: $this->product_name,
        //     sheetTitle: 'Semua Sub Tipe'
        // );

        foreach ($subTypes as $st) {
            $sheets[] = new ExportOffProducts(
                major_id: $this->major_id,
                sub_type: $st->code,        // filter per sub type
                product_name: $this->product_name,
                sheetTitle: $st->name         // nama sheet = nama sub type
            );
        }

        return $sheets;
    }
}
