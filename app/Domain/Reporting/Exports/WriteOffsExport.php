<?php

namespace App\Domain\Reporting\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Daftar barang dihapuskan (rusak / pensiun / hilang). */
class WriteOffsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    private int $index = 1;

    public function __construct(private array $rows, private string $unitLabel = 'Unit') {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return ['No', 'Nama Barang', 'Kode', 'Merk', 'Kategori', $this->unitLabel, 'Gudang', 'Jumlah', 'Satuan', 'Tanggal Dihapuskan', 'Alasan', 'Dicatat Oleh'];
    }

    public function map($row): array
    {
        return [
            $this->index++,
            $row['product_name'],
            $row['code'],
            $row['brand_name'],
            $row['category_name'],
            $row['unit_name'],
            $row['warehouse_name'],
            $row['quantity'],
            $row['uom_name'],
            $row['occurred_at'] ? Carbon::parse($row['occurred_at'])->translatedFormat('j F Y') : null,
            $row['reason'],
            $row['created_by_name'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Barang Dihapuskan';
    }
}
