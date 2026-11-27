<?php

namespace App\Http\Controllers;

use App\Exports\ExportRequestItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportRequestItemData extends Controller
{
    public function export(Request $request) 
    {
        $year = $request->get('year', Carbon::now()->year);
        $major = $request->get('major', null);
        $export_type = $request->get('export_type');

        return Excel::download(
            new ExportRequestItem(
                $export_type, 
                $major, 
                $year
            ), "pengajuan_barang_{$year}.xlsx");
    }
}
