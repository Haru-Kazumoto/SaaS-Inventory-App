<?php

namespace App\Repositories;

use App\Http\Requests\WarehouseRequest;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    public function getByCode(string $code)
    {
        return Warehouse::where('code', $code)->first();
    }

    public function getAllByProductsQuantityInformation(int $major_id, int $product_id)
    {
        return DB::table('warehouse as w')
            ->leftJoin('product_logs as pl', function ($join) use ($product_id) {
                $join->on('pl.warehouse_id', '=', 'w.id')
                    ->where('pl.product_id', '=', $product_id);
            })
            ->leftJoin('products as p', 'p.id', '=', 'pl.product_id')
            ->leftJoin('unit_products as up', 'up.id', '=', 'p.unit_id')
            ->where('w.major_id', $major_id)
            ->groupBy('w.id', 'w.name', 'up.name')
            ->orderBy('w.name')
            ->selectRaw("
                w.id,
                w.name,
                COALESCE(SUM(
                    CASE
                        WHEN pl.`condition` IS NULL OR pl.`condition` <> 'OFF'
                        THEN pl.delta ELSE 0
                    END
                ), 0) AS current_quantity,
                COALESCE(SUM(
                    CASE
                        WHEN pl.`condition` = 'GOOD'
                        THEN pl.delta ELSE 0
                    END
                ), 0) AS good_quantity,
                up.name AS product_unit
            ")
            ->get();
    }

    public function indexByMajorId(?int $major_id)
    {
        return Warehouse::query()
            ->when($major_id, function($q) use ($major_id) {
                $q->where('major_id', '=', $major_id);
            })
            // ->where('major_id', '=', $major_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function index()
    {
        return Warehouse::latest()->get();
    }

    public function store(array $data)
    {
        $createdData = DB::transaction(function () use ($data) {
            return Warehouse::create($data);
        });

        return $createdData;
    }

    public function update(array $data, Warehouse $warehouse)
    {
        return DB::transaction(function () use ($data, $warehouse) {
            $warehouse->update($data);
        });
    }

    public function delete(Warehouse $warehouse)
    {
        return DB::transaction(function () use ($warehouse) {
            $warehouse->delete();
        });
    }
}
