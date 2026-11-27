<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportSummaryDurableItem implements FromCollection, WithTitle, WithHeadings, WithMapping, WithColumnWidths, WithEvents
{
    public function __construct(private string $major){}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DB::table('items as i')
            ->leftJoin('partial_item as pt', 'pt.item_id', '=', 'i.id')
            ->leftJoin('major as m', 'm.id', '=', 'i.major_id')
            ->select(
                'i.name',
                DB::raw('SUM(CASE WHEN pt.is_available = 1 THEN 1 ELSE 0 END) as total_available'),
                DB::raw('SUM(CASE WHEN pt.is_available = 0 THEN 1 ELSE 0 END) as total_unavailable')
            )
            ->where('i.category', '=', value: 'DURABLE')
            ->where('m.name', '=', $this->major)
            ->groupBy('i.name')
            ->get();
    }

    public function title(): string 
    {
        return 'SUMMARY';
    }

    public function headings(): array
    {
        return [
            'NAMA BARANG',
            'JUMLAH BARANG TERSEDIA',
            'JUMLAH BARANG TIDAK TERSEDIA'
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->total_available,
            $row->total_unavailable
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 35,
            'C' => 35,
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
                $sheet->getStyle('A1:C1')->applyFromArray([
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
