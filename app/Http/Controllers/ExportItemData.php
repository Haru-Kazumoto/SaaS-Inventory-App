<?php

namespace App\Http\Controllers;

use App\Exports\ExportCurrentItemData;
use App\Http\Services\AuthService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportItemData extends Controller
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke()
    {
        $major = $this->authService->getMajorFromCurrentUser();

        return Excel::download(new ExportCurrentItemData($this->authService), 'list_data_barang_'.$major->name.'.xlsx');
    }
}
