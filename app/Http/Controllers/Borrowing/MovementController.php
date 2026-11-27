<?php

namespace App\Http\Controllers\Borrowing;

use App\Domain\Borrowing\Queries\OutgoingLinesQuery;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Pergerakan barang keluar. */
class MovementController extends Controller
{
    private const FILTERS = ['borrower_name', 'product_code', 'warehouse_id', 'date', 'unit_id'];

    public function loaned(Request $request, OutgoingLinesQuery $query): Response
    {
        return Inertia::render('Movements/Outgoing', [
            'kind' => 'durable',
            'lines' => $query->durableOutstanding($request->only(self::FILTERS)),
            'warehouses' => $this->warehouses(),
        ]);
    }

    public function consumed(Request $request, OutgoingLinesQuery $query): Response
    {
        return Inertia::render('Movements/Outgoing', [
            'kind' => 'consumable',
            'lines' => $query->consumables($request->only(self::FILTERS)),
            'warehouses' => $this->warehouses(),
        ]);
    }

    private function warehouses(): array
    {
        return Warehouse::query()
            ->where(fn ($q) => $q->whereNull('unit_id')->orWhereIn('unit_id', CurrentOrganization::unitIds() ?: [0]))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }
}
