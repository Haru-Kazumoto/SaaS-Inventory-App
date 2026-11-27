<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportConsumableItem implements FromCollection, WithTitle, WithHeadings, WithMapping, WithColumnWidths, WithEvents
{
    public function __construct(protected string $major)
    {}
    
    public function collection()
    {
        return DB::table('items as i')
            ->leftJoin('partial_item as pt', 'pt.item_id', '=', 'i.id')
            ->join('warehouse as w', 'w.id', '=', 'pt.warehouse_id')
            ->join('major as m', 'm.id', '=', 'i.major_id')
            ->select(
                'i.id',
                'i.name',
                'i.brand_name',
                'i.unit',
                DB::raw("IF(i.price = 0, '0', i.price) as price"),
                'pt.labeling_date',
                DB::raw("IF(pt.base_quantity = 0, '0', pt.base_quantity) as base_quantity"),
                DB::raw("
                    CASE
                        WHEN pt.base_quantity > 10 THEN 'TERSEDIA'
                        WHEN pt.base_quantity BETWEEN 1 AND 10 THEN 'BUTUH TAMBAH'
                        WHEN pt.base_quantity = 0 THEN 'HABIS'
                        ELSE 'TIDAK DIKETAHUI'
                    END AS status_ketersediaan
                "),
                'w.name as warehouse_name'
            )
            ->where('i.category', 'CONSUMABLE')
            ->where('m.name', $this->major)
            ->groupBy(
                'i.id',
                'i.name',
                'i.brand_name',
                'i.unit',
                'i.price',
                'pt.labeling_date',
                'pt.base_quantity',
                'w.name'
            )
            ->get();
    }

    public function title() : string
    {
        return 'HABIS PAKAI';
    }

    public function headings(): array
    {
        return [
            'NAMA BARANG',
            'MERK BARANG',
            'SATUAN BARANG',
            'HARGA',
            'WAKTU PELABELAN',
            'QUANTITY',
            'STATUS KETERSEDIAAN',
            'TEMPAT PENYIMPANAN'
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->brand_name,
            $row->unit,
            'Rp ' . number_format($row->price, 0, ',', '.'),
            Carbon::parse($row->labeling_date)->format('Y-m-d'),
            $row->base_quantity,
            $row->status_ketersediaan,
            $row->warehouse_name
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 25,
            'H' => 25
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Atur tinggi baris pertama (header)
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Styling header (opsional)
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F81BD'],
                    ],
                ]);
            },
        ];
    }
}
