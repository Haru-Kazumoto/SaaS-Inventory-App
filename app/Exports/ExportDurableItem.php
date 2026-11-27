<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportDurableItem implements FromCollection, WithTitle, WithHeadings, WithMapping, WithColumnWidths, WithEvents
{
    public function __construct(private string $major){}
    
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
                'pt.code_item',
                DB::raw("
                    CASE 
                        WHEN pt.condition_item = 'GOOD' THEN 'BAIK'
                        WHEN pt.condition_item = 'LOST' THEN 'HILANG'
                        WHEN pt.condition_item = 'DAMAGED' THEN 'RUSAK'
                        ELSE '-'
                    END AS condition_item
                "),
                DB::raw("
                    CASE
                        WHEN pt.is_available THEN 'TERSEDIA'
                        ELSE 'TIDAK TERSEDIA'
                    END AS status_ketersediaan
                "),
                'w.name as warehouse_name'
            )
            ->where('i.category', 'DURABLE')
            ->where('m.name', $this->major)
            ->groupBy(
                'i.id',
                'i.name',
                'i.brand_name',
                'i.unit',
                'i.price',
                'pt.labeling_date',
                'pt.code_item',
                'pt.condition_item',
                'pt.is_available',
                'w.name'
            )
            ->get();
    }

    public function title(): string
    {
        return 'TIDAK HABIS PAKAI';
    }

    public function headings(): array
    {
        return [
            'NAMA BARANG',
            'MERK BARANG',
            'SATUAN BARANG',
            'HARGA SATUAN',
            'WAKTU PELABELAN',
            'KODE BARANG',
            'KONDISI BARANG',
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
            $row->code_item,
            $row->condition_item,
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
            'H' => 25,
            'I' => 25
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
                $sheet->getStyle('A1:I1')->applyFromArray([
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
