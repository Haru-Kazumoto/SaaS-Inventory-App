<?php

namespace App\Http\Controllers\Reporting;

use App\Domain\Inventory\Queries\StockMovementQuery;
use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Domain\Reporting\Exports\MonthlyStockReport;
use App\Domain\Reporting\Exports\WriteOffsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    /** Laporan bulanan barang habis pakai (per kategori). */
    public function monthlyStock(Request $request): BinaryFileResponse
    {
        $unitId = $this->unitFilter($request);
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $org = CurrentOrganization::get();

        return Excel::download(
            new MonthlyStockReport($org->id, $unitId, $year, $month),
            "Laporan Barang {$org->name} {$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx',
        );
    }

    public function writeOffs(Request $request, StockMovementQuery $query): BinaryFileResponse
    {
        $unitId = $this->unitFilter($request);
        $org = CurrentOrganization::get();

        return Excel::download(
            new WriteOffsExport($query->writeOffsForExport($unitId), $org->terms()['unit']),
            'Barang Dihapuskan '.now()->format('Y-m-d').'.xlsx',
        );
    }

    /** Anggota unit hanya bisa export unitnya; admin organisasi boleh semua (null). */
    private function unitFilter(Request $request): ?int
    {
        $unitId = $request->filled('unit_id') ? $request->integer('unit_id') : null;

        if ($unitId !== null) {
            Access::authorizeView($unitId);

            return $unitId;
        }

        if (Access::managesOrganization()) {
            return null;
        }

        $units = CurrentOrganization::unitIds();
        abort_if(count($units) !== 1, 422, 'Pilih unit yang akan diexport.');

        return $units[0];
    }
}
