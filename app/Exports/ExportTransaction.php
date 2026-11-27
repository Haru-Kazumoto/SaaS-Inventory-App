<?php

namespace App\Exports;

use App\Http\Services\AuthService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportTransaction implements FromCollection, WithHeadings
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $major = $this->authService->getMajorFromCurrentUser();

        return DB::table('transaction as tx')
            ->select([
                'tx.transaction_number',
                'tx.description',
                'tx.out_date',
                'tx.in_date',
                'br.fullname',
                'br.phone',
                'br.room_name',
                'br.teacher_name',
                'i.name as item_name',
                'i.brand_name',
                'pi.code_item',
                DB::raw("
                    CASE 
                        WHEN io.status_in = 'APPROVED' THEN 'DITERIMA'
                        WHEN io.status_in = 'BORROWED' THEN 'MASIH DIPINJAM'
                        WHEN io.status_in = 'DAMAGED' THEN 'DITERIMA RUSAK'
                        WHEN io.status_in = 'LOST' THEN 'HILANG'
                    END AS status_in
                ")
            ])
            ->join('borrower as br', 'br.transaction_id', '=', 'tx.id')
            ->leftJoin('items_out as io', 'io.transaction_id', '=', 'tx.id')
            ->leftJoin('partial_item as pi', 'pi.id', '=', 'io.partial_item_id')
            ->leftJoin('items as i', 'i.id', '=', 'pi.item_id')
            ->where('br.major_id', $major->id)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Transaksi',
            'Deskripsi Transaksi',
            'Waktu Keluar',
            'Waktu Masuk',
            'Nama Peminjam',
            'Nomor Peminjam',
            'Ruang Peminjam',
            'Nama Pengawas',
            'Nama Barang',
            'Kode Barang',
            'Status Masuk Barang'
        ];
    }
}
