<?php

namespace App\Http\Controllers;

use App\Exports\ExportTransaction;
use App\Http\Services\AuthService;
use Maatwebsite\Excel\Facades\Excel;

class ExportTransactionData extends Controller
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke()
    {
        $major = $this->authService->getMajorFromCurrentUser();

        return Excel::download(new ExportTransaction($this->authService), 'data_transaksi_'.$major->name.'.xlsx');
    }
}
