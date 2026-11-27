<?php

namespace App\Repositories;

use App\Dto\Products\CreateProductsDto;
use App\Dto\Products\FilterIndexDto;
use App\Dto\Products\PersistProductDto;
use App\Dto\Products\UpdateProductsDto;
use App\Models\Products;
use App\Repositories\Interfaces\ProductsRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Dotenv\Util\Str;
use Illuminate\Support\Facades\Auth;

class ProductsRepository implements ProductsRepositoryInterface
{
    public function getAll(?FilterIndexDto $filter, ?int $major_id, ?int $page)
    {
        // Subquery stok: sum(delta) exclude OFF
        $stockSub = DB::table('product_logs')
            ->select('product_id', DB::raw('COALESCE(SUM(delta), 0) AS qty_on_hand'))
            ->where(function ($q) {
                $q->whereNull('condition')
                    ->orWhere('condition', '<>', 'OFF');
            })
            ->groupBy('product_id');

        $query = DB::table('products as p')
            ->join('sub_type_products as st', 'p.sub_type_id', '=', 'st.id')
            ->join('unit_products as up', 'p.unit_id', '=', 'up.id')
            ->leftJoinSub($stockSub, 'stk', function ($join) {
                $join->on('stk.product_id', '=', 'p.id');
            })
            ->whereNull('p.deleted_at')
            ->when($major_id, fn($query, $m) => $query->where('p.major_id', $m))
            ->selectRaw('
                p.id,
                p.name,
                p.code,
                p.brand_name,
                p.type,
                p.price,
                st.name as sub_type_name,
                up.name as unit_name,
                COALESCE(stk.qty_on_hand, 0) AS quantity_on_hand
            ');

        $query->when($filter?->name, fn($q, $name) => $q->where('p.name', 'LIKE', "%$name%"));
        $query->when($filter?->code, fn($q, $code) => $q->where('p.code', 'LIKE', "%$code%"));
        $query->when($filter?->type, fn($q, $type) => $q->where('p.type', $type));

        if (!empty($page)) {
            return $query->orderBy('p.created_at', 'desc')->paginate($page);
        }

        return $query->orderBy('p.created_at', 'desc')->get();
    }

    function getProductDetailWithAggregates(int $product_id)
    {
        // ===== Base filter utk product_logs (dipakai ulang di subquery) =====
        $baseLogs = DB::table('product_logs as pl')
            ->where('pl.product_id', $product_id);

        // ===== Subquery: cond (GOOD, DAMAGED, OFF, usable) =====
        $condSub = DB::table('product_logs as pl')
            ->where('pl.product_id', $product_id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN pl.`condition` = 'GOOD'    THEN pl.delta ELSE 0 END), 0) AS good_qty,
                COALESCE(SUM(CASE WHEN pl.`condition` = 'DAMAGED' THEN pl.delta ELSE 0 END), 0) AS damaged_qty,
                COALESCE(SUM(CASE WHEN pl.`condition` = 'OFF' THEN pl.delta ELSE 0 END), 0) AS off_qty,
                COALESCE(SUM(CASE WHEN (pl.`condition` <> 'OFF' OR pl.`condition` IS NULL) THEN pl.delta ELSE 0 END), 0) AS usable_qty
            ");

        // ===== Subquery: borrow (OUT aktif = BORROW_OUT - RETURN) =====
        $borrowSub = DB::table('product_logs as pl')
            ->where('pl.product_id', $product_id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN pl.move_type = 'BORROW_OUT' THEN pl.quantity ELSE 0 END), 0)
                - COALESCE(SUM(CASE WHEN pl.move_type = 'RETURN'     THEN pl.quantity ELSE 0 END), 0) AS out_qty
            ");

        // ===== Subquery: prod (detail Barang + join master) =====
        $prodSub = DB::table('products as p')
            // ->join('warehouse as w', 'w.id', '=', 'p.warehouse_id')
            ->join('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->join('unit_products as up', 'up.id', '=', 'p.unit_id')
            ->join('major as m', 'm.id', '=', 'p.major_id')
            ->where('p.id', $product_id)
            ->selectRaw("
                p.id, p.name, p.code, p.brand_name, p.type, p.price, p.labeling_date,
                st.name AS sub_type_name,
                up.name AS unit_name,
                p.image_product,
                DATE(p.created_at) as created_at,
                m.name as major_name
            ");

        // ===== Gabungkan: prod LEFT JOIN cond, borrow (ON 1=1) =====
        $row = DB::query()
            ->fromSub($prodSub, 'prod')
            ->leftJoinSub($condSub, 'cond', fn($join) => $join->on(DB::raw('1'), '=', DB::raw('1')))
            ->leftJoinSub($borrowSub, 'borrow', fn($join) => $join->on(DB::raw('1'), '=', DB::raw('1')))
            ->selectRaw("
                prod.*,
                COALESCE(cond.good_qty, 0)    AS good_qty,
                COALESCE(cond.damaged_qty, 0) AS damaged_qty,
                COALESCE(borrow.out_qty, 0)   AS out_qty,
                COALESCE(cond.off_qty, 0)     AS off_qty,
                COALESCE(cond.usable_qty, 0)  AS usable_qty
            ")
            ->first();

        // dd($prodSub, $condSub, $borrowSub, $row);

        $row->image_product ? $row->image_product = Storage::disk('public')->url($row->image_product) : null;

        return $row;
    }

    public function getById(int $id)
    {
        $query = DB::table('products as p')
            ->selectRaw("
                p.id,
                p.name,
                p.code,
                p.brand_name,
                p.price,
                p.labeling_date,
                p.image_product,
                p.type,
                ( SELECT CODE FROM sub_type_products WHERE id = p.sub_type_id ) AS sub_type_code,
                ( SELECT CODE FROM unit_products WHERE id = p.unit_id ) AS unit_code
            ")
            ->where('p.id', $id)
            ->first();

        if (!empty($query->image_product)) {
            $query->image_product = Storage::disk('public')->url($query->image_product);
        }

        return $query;
    }

    public function store(array $data)
    {
        return DB::table('products')->insertGetId($data);
    }

    public function update(UpdateProductsDto $data, int $id) {}

    public function softDelete(int $id)
    {
        return Products::where('id', $id)
            ->update([
                'updated_at' => now(),
                'deleted_at' => now()
            ]);
    }

    public function count(int $major_id)
    {
        return Products::where('major_id', $major_id)
            ->where('deleted_at', null)
            ->count();
    }

    public function countFromAllMajor()
    {
        return DB::table("major as m")
            ->join('products as p', function ($join) {
                $join->on('p.major_id', 'm.id')
                    ->where('p.deleted_at', null);
            })
            ->groupBy('m.id', 'm.name')
            ->orderBy('m.id')
            ->selectRaw("
              m.id,
                m.name,
                COALESCE(COUNT(p.id), 0) AS products_count
            ")
            ->get();
    }
}
