<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    private WarehouseService $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $major = $user?->major;
        $data = $this->warehouseService->getWarehouseByMajorId($major?->id);

        return Inertia::render('Warehouse/Index', [
            'data' => $data->toArray()
        ]);
    }

    public function indexJson(int $product_id)
    {
        $major = Auth::user()?->major;
        $data = $this->warehouseService->getAllByProductsQuantityInformation($major->id, $product_id);

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Warehouse/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WarehouseRequest $request)
    {
        $request->validated();
        $major = Auth::user()?->major;
        $this->warehouseService->storeWarehouse([
            'name' => $request['name'],
            'code' => $request['code'],
            'major_id' => $major->id
        ]);

        return redirect()->route('warehouse.index')->with('success', 'Berhasil membuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return Inertia::render('Warehouse/Edit', [
            'data' => $warehouse
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $this->warehouseService->update($request->validated(), $warehouse);

        return redirect()->route('warehouse.index')->with('success', 'Berhasil memperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $this->warehouseService->delete($warehouse);

        return back()->with('success', 'Berhasil menghapus');
    }
}
