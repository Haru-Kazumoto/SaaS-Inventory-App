<?php

namespace App\Http\Controllers;

use App\Exports\ExportItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportItemsData extends Controller
{
    public function __construct(private ?string $major){}

    public function export(Request $request) 
    {
        $selected_major = $request->get('major', Auth::user()->major ? Auth::user()->major->name : null);

        return Excel::download(new ExportItems($selected_major), "Daftar_Barang_$selected_major.xlsx");
    }
}
