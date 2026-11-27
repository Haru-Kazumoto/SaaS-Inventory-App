<?php

namespace App\Http\Controllers\Inventory;

use App\Domain\Inventory\Actions\RestockProduct;
use App\Domain\Inventory\Actions\WriteOffProduct;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Queries\ProductDetailQuery;
use App\Domain\Inventory\Queries\StockMovementQuery;
use App\Domain\Organization\Support\Access;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockMoveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Barang masuk, penghapusan barang, dan stok per gudang. */
class StockController extends Controller
{
    public function restock(StockMoveRequest $request, Product $product, RestockProduct $restock): RedirectResponse
    {
        $restock->handle($product, $request->integer('warehouse_id'), $request->integer('quantity'), $request->condition());

        return back()->with('success', "Stok {$product->name} bertambah {$request->integer('quantity')}");
    }

    public function writeOff(StockMoveRequest $request, Product $product, WriteOffProduct $writeOff): RedirectResponse
    {
        $writeOff->handle($product, $request->integer('warehouse_id'), $request->integer('quantity'), $request->condition(), $request->input('reason'));

        return back()->with('success', "{$request->integer('quantity')} {$product->name} dihapuskan dari gudang");
    }

    public function restocks(Request $request, StockMovementQuery $query): Response
    {
        return Inertia::render('Movements/Restocks', [
            'restocks' => $query->restocks($request->only(['product_name', 'product_type', 'unit_id', 'date'])),
        ]);
    }

    public function writeOffs(Request $request, StockMovementQuery $query): Response
    {
        return Inertia::render('Movements/WriteOffs', [
            'writeOffs' => $query->writeOffs($request->only(['product_name', 'unit_id'])),
        ]);
    }

    /** Stok per gudang untuk satu barang (dipakai form peminjaman). */
    public function stock(Product $product, ProductDetailQuery $query): JsonResponse
    {
        Access::authorizeView($product->unit_id);

        return response()->json($query->warehousesFor($product));
    }
}
