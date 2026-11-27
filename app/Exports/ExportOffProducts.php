<?php

namespace App\Exports;

use App\Services\ProductLogsService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportOffProducts implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithEvents
{
    use Exportable;

    private int $index = 1;

    public function __construct(
        public int $major_id,
        public ?string $sub_type = null,
        public ?string $product_name = null,
        public ?string $sheetTitle = null, // untuk judul sheet
    ) {
        setlocale(LC_TIME, 'id_ID.UTF-8');
    }

    public function collection()
    {
        return app(ProductLogsService::class)
            ->getOffByMajor($this->major_id, $this->sub_type, $this->product_name);
    }

    public function map($row): array
    {
        return [
            $this->index++,
            $row->name,
            $row->code,
            $row->brand_name,
            date('j F Y', strtotime($row->off_date)),
            $row->sub_type_name,
            $row->warehouse_name,
            $row->off_qty,
            $row->unit_name,
        ];
    }

    public function headings(): array
    {
        // menyesuaikan dengan selectRaw di repo
        return [
            'Nomor',             // index
            'Nama Produk',       // p.name
            'Kode Produk',       // p.code
            'Brand',             // p.brand_name
            'Tanggal OFF',       // off_date
            'Sub Tipe',          // sub_type_name
            'Nama Gudang',       // warehouse_name
            'Qty OFF',           // off_qty
            'Satuan',            // unit_name
        ];
    }

    public function title(): string
    {
        // nama sheet di Excel
        if ($this->sheetTitle) {
            return $this->sheetTitle;
        }

        if ($this->sub_type) {
            return $this->sub_type;
        }

        return 'OFF Products';
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
