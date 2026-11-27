<?php

namespace App\Exports;

use App\Http\Services\AuthService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportCurrentItemData implements FromCollection, WithHeadings, WithColumnWidths
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
        
        return DB::table('partial_item as pi')
            ->join('items as i', 'pi.item_id', '=', 'i.id')
            ->where('i.major_id', $major->id)
            ->orderByDesc('pi.created_at')
            ->select([
                'i.name',
                'i.brand_name',
                'i.unit',
                'i.category',
                'pi.code_item',
                'pi.labeling_date',
                'pi.base_quantity',
                DB::raw("CASE 
                            WHEN pi.is_available = 1 THEN 'TERSEDIA' 
                            ELSE 'TIDAK TERSEDIA' 
                        END AS availability_status"),
                DB::raw("CASE 
                            WHEN pi.condition_item = 'GOOD' THEN 'BAIK' 
                            WHEN pi.condition_item = 'DAMAGED' THEN 'RUSAK' 
                            WHEN pi.condition_item = 'LOST' THEN 'HILANG' 
                            ELSE 'TIDAK DIKETAHUI' 
                        END AS condition_status")
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Barang',
            'Merk Barang',
            'Satuan Barang',
            'Kategori Barang',
            'Kode Barang',
            'Tanggal Pelabelan',
            'Jumlah Satuan',
            'Status Tersedia',
            'Kondisi Barang',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 24,  
            'B' => 20,  
            'C' => 17,
            'D' => 17,
            'E' => 20,
            'F' => 21,
            'G' => 20,
            'H' => 20,
            'I' => 20
        ];
    }
}
