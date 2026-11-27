<?php

namespace App\Http\Controllers;

use App\Dto\ProductLogs\CreateProductLogsDto;
use App\Http\Requests\CreateWriteOffRequest;
use App\Models\Major;
use App\Services\ProductLogsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductLogsController extends Controller
{
    public function __construct(
        protected ProductLogsService $productLogsService
    ) {}

    public function writeOff(CreateWriteOffRequest $request)
    {
        $data = $request->validated();

        $this->productLogsService->writeOff($data['product_id'], $data['warehouse_id'], $data['quantity']);

        return back()->with('success', 'Barang berhasil dikeluarkan!');
    }

    public function restock(CreateWriteOffRequest $request)
    {
        $data = $request->validated();

        $this->productLogsService->restock($data['product_id'], $data['warehouse_id'], $data['quantity']);

        return back()->with('success', 'Barang berhasil ditambahkan!');
    }

    public function indexRestockProducts(Request $request)
    {
        #filters
        $product_name = $request->query('product_name', null);
        $product_type = $request->query('product_type', null);
        $major_item = $request->query('major', null);
        $occured_at = $request->query('date', null);

        $user = $request->user();
        $major = $user?->major;
        $data = $this->productLogsService->getRestockProducts($major ? $major?->id : $major_item, $product_name, $product_type, $occured_at);
        $majors = null;

        if(!$major) $majors = Major::all(['id', 'name', 'code']);

        return Inertia::render("Lines/IndexRestock", [
            'restocks' => $data,
            'major' => $major?->name,
            'majors' => $majors
        ]);
    }
}
