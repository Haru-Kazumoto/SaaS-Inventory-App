<?php

namespace App\Domain\Procurement\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Rekap pengajuan barang per tahun. */
class RequestItemsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private array $rows, private string $unitLabel = 'Unit') {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return ['NAMA BARANG', 'JUMLAH', 'SATUAN', 'ALASAN', 'TANGGAL PENGAJUAN', 'NAMA PENGAJU', strtoupper($this->unitLabel), 'PERIODE', 'STATUS'];
    }

    public function map($row): array
    {
        return [
            $row->item_name,
            (int) $row->quantity,
            $row->uom,
            $row->reason,
            $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : null,
            $row->requested_by_name,
            $row->unit_name,
            $row->created_at ? Carbon::parse($row->created_at)->year : null,
            match ($row->status) {
                'APPROVED' => 'DISETUJUI',
                'REJECTED' => 'DITOLAK',
                default => 'MENUNGGU',
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
