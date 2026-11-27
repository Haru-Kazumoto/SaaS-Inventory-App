<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportRequestItem implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{

    public function __construct(
        protected array $export_type,
        protected ?string $major,
        protected ?string $year,
    ){}

    public function headings(): array
    {
        return [
            'NAMA BARANG',
            'QUANTITY',
            'DESKRIPSI',
            'TANGGAL PENGAJUAN',
            'NAMA PENGAJU',
            'JURUSAN',
            'PERIODE',
            'STATUS PENGAJUAN',
        ];
    }

    public function collection()
    {
        $query = DB::table('request_item as ri')
            ->select(
                'ri.id',
                'ri.brand_name',
                'ri.quantity',
                'ri.reason_request',
                'ri.created_at',
                'requestor.fullname',
                'm.name as major_requestor',
                DB::raw('YEAR(ri.created_at) as period'),
                'ri.request_status'
            )
            ->join('users as requestor', 'requestor.id', '=', 'ri.requestor_id')
            ->join('major as m', 'm.id', '=', 'ri.major_id')
            ->whereYear('ri.created_at', $this->year ?? now()->year);

        // ✅ Filter status hanya jika tidak "all"
        if (!in_array('all', $this->export_type)) {
            $mappedStatuses = collect($this->export_type)->map(function ($status) {
                return strtoupper($status);
            })->toArray();

            $query->whereIn('ri.request_status', $mappedStatuses);
        }

        // ✅ Optional filter major (jika ada)
        if ($this->major) {
            $query->where('m.id', (int)$this->major);
        }

        return $query->get();
    }

    public function map($row): array
    {
        return [
            $row->brand_name,
            $row->quantity,
            $row->reason_request,
            $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : null,
            $row->fullname,
            $row->major_requestor,
            $row->period,
            $row->request_status,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 40,
            'D' => 20,
            'E' => 25,
            'F' => 15,
            'G' => 10,
            'H' => 15
        ];
    }
}
