<?php

namespace App\Http\Controllers;

use App\Services\TransactionLinesService;
use App\Services\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionLinesController extends Controller
{
    public function __construct(
        protected TransactionLinesService $transactionLineService,
        protected WarehouseService $warehouseService
    ) {}

    public function indexBorrowedDurableProducts(Request $request)
    {
        $borrower_name = $request->query('borrower_name');
        $product_code = $request->query('product_code');
        $warehouse_id = $request->query('warehouse_id');
        $date = $request->query('date');

        $user = $request->user();
        $major = $user?->major;

        $warehouses = $this->warehouseService->getWarehouseByMajorId($major?->id);

        $current_major = $major;

        return Inertia::render("Lines/IndexDurable", [
            'lines' => $this->transactionLineService->getBorrowedDurableProductLines($current_major?->id, $borrower_name, $product_code, $warehouse_id, $date),
            'warehouses' => $warehouses
        ]);
    }

    public function indexBorrowedConsumableProducts(Request $request)
    {
        $borrower_name = $request->query('borrower_name');
        $product_code = $request->query('product_code');
        $warehouse_id = $request->query('warehouse_id');
        $date = $request->query('date');

        $user = $request->user();
        $major = $user?->major;

        $current_major = $major;
        $warehouses = $this->warehouseService->getWarehouseByMajorId($major?->id);

        return Inertia::render("Lines/IndexConsumable", [
            'lines'      => $this->transactionLineService->getBorrowedConsumableProductLines(
                $current_major?->id,
                $borrower_name,
                $product_code,
                $warehouse_id,
                $date
            ),
            'warehouses' => $warehouses
        ]);
    }
}
