<?php

namespace App\Http\Controllers\Procurement;

use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Domain\Procurement\Actions\ProcessRequests;
use App\Domain\Procurement\Actions\SubmitRequest;
use App\Domain\Procurement\Exports\RequestItemsExport;
use App\Domain\Procurement\Queries\RequestListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\ProcessRequestsRequest;
use App\Http\Requests\Procurement\SubmitRequestRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProcurementController extends Controller
{
    public function index(Request $request, RequestListQuery $query): Response
    {
        $filters = [...$request->only(['item_name', 'status', 'unit_id']), 'period' => $request->input('period', now()->year)];

        return Inertia::render('Procurement/Index', [
            'requests' => $query->paginate($filters),
            'count' => $query->summary($filters),
            'period' => (int) $filters['period'],
            'canReview' => Access::managesOrganization(),
            'canSubmit' => Access::manageableUnitIds() !== [],
        ]);
    }

    public function create(): Response
    {
        $unitIds = Access::manageableUnitIds();
        abort_if($unitIds === [], 403, 'Anda tidak punya akses mengajukan barang.');

        return Inertia::render('Procurement/Create', [
            'units' => CurrentOrganization::units()->whereIn('id', $unitIds)->map->only(['id', 'name'])->values(),
        ]);
    }

    public function store(SubmitRequestRequest $request, SubmitRequest $submit): RedirectResponse
    {
        $submit->handle($request->payload(), $request->file('image'));

        return redirect()->route('procurement.index')->with('success', 'Pengajuan berhasil dikirim');
    }

    public function process(ProcessRequestsRequest $request, ProcessRequests $process): RedirectResponse
    {
        $count = $process->handle(array_map('intval', $request->input('ids')), $request->decision());

        return back()->with('success', "{$count} pengajuan diproses");
    }

    public function export(Request $request, RequestListQuery $query): BinaryFileResponse
    {
        $types = (array) $request->input('export_type', ['all']);
        $statuses = in_array('all', $types, true) ? [] : array_map('strtoupper', $types);
        $year = (int) $request->input('year', now()->year);

        $rows = $query->forExport(['period' => $year, 'unit_id' => $request->input('unit_id')], $statuses);
        $unitLabel = CurrentOrganization::get()->terms()['unit'];

        return Excel::download(new RequestItemsExport($rows, $unitLabel), "pengajuan_barang_{$year}.xlsx");
    }
}
