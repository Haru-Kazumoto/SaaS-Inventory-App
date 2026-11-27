<?php

namespace App\Http\Controllers;

use App\Models\Items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SuperadminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexTJKT(Request $request)
    {
        $major = DB::table('major')->where('code', 'TJKT')->first();
        $query = DB::table('items')
            ->join('major', 'items.major_id', '=', 'major.id')
            ->join('partial_item','items.id', '=','partial_item.item_id')
            ->where('major.id', '=', $major->id)
            ->select([
                'items.id',
                'items.name',
                'items.brand_name',
                'items.category',
                'items.unit',
                'partial_item.base_quantity',
                DB::raw('COUNT(
                    CASE 
                        WHEN partial_item.is_available > 0 then 1
                        ELSE null
                    END
                ) as quantity'), // Menghitung jumlah partial_items
                DB::raw("CASE 
                            WHEN COUNT(partial_item.id) > 0 THEN 'AVAILABLE'
                            ELSE 'UNAVAILABLE'
                         END as status") // Menentukan status berdasarkan quantity
            ])
            ->groupBy('items.id', 'items.name', 'items.brand_name', 'items.category', 'partial_item.base_quantity');
        
        if($request->filled('search_name')) {
            $query->where('items.name', 'like',"%{$request->search_name}%");
        }
        if($request->filled('search_category')) {
            $query->where('items.category', $request->search_category);
        }

        $items = $query->orderByDesc('items.created_at')->paginate(20);

        $item_count = DB::table('items')->where('major_id','=',$major->id)->count();
        $warehouse = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();
        $item_type = DB::table('lookup as l')->where('code', '=','ITEM_TYPE')->get();

        return Inertia::render('ItemManagement/IndexMajor', [
            'items' => $items,
            'item_count' => $item_count,
            'filters' => $request->only(['search_name','search_category','search_warehouse']),
            'utils' => [
                'warehouses' => $warehouse,
                'item_types' => $item_type
            ],
            'major' => $major
        ]);
    }

    public function indexTO(Request $request)
    {
        $major = DB::table('major')->where('code', 'TO')->first();
        $query = DB::table('items')
            ->join('major', 'items.major_id', '=', 'major.id')
            ->join('partial_item','items.id', '=','partial_item.item_id')
            ->where('major.id', '=', $major->id)
            ->select([
                'items.id',
                'items.name',
                'items.brand_name',
                'items.category',
                'items.unit',
                'partial_item.base_quantity',
                DB::raw('COUNT(
                    CASE 
                        WHEN partial_item.is_available > 0 then 1
                        ELSE null
                    END
                ) as quantity'), // Menghitung jumlah partial_items
                DB::raw("CASE 
                            WHEN COUNT(partial_item.id) > 0 THEN 'AVAILABLE'
                            ELSE 'UNAVAILABLE'
                         END as status") // Menentukan status berdasarkan quantity
            ])
            ->groupBy('items.id', 'items.name', 'items.brand_name', 'items.category', 'partial_item.base_quantity');
        
        if($request->filled('search_name')) {
            $query->where('items.name', 'like',"%{$request->search_name}%");
        }
        if($request->filled('search_category')) {
            $query->where('items.category', $request->search_category);
        }

        $items = $query->orderByDesc('items.created_at')->paginate(20);

        $item_count = DB::table('items')->where('major_id','=',$major->id)->count();
        $warehouse = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();
        $item_type = DB::table('lookup as l')->where('code', '=','ITEM_TYPE')->get();

        return Inertia::render('ItemManagement/IndexMajor', [
            'items' => $items,
            'item_count' => $item_count,
            'filters' => $request->only(['search_name','search_category','search_warehouse']),
            'utils' => [
                'warehouses' => $warehouse,
                'item_types' => $item_type
            ],
            'major' => $major
        ]);
    }

    public function indexTE(Request $request)
    {
        $major = DB::table('major')->where('code', 'TE')->first();
        $query = DB::table('items')
            ->join('major', 'items.major_id', '=', 'major.id')
            ->join('partial_item','items.id', '=','partial_item.item_id')
            ->where('major.id', '=', $major->id)
            ->select([
                'items.id',
                'items.name',
                'items.brand_name',
                'items.category',
                'items.unit',
                'partial_item.base_quantity',
                DB::raw('COUNT(
                    CASE 
                        WHEN partial_item.is_available > 0 then 1
                        ELSE null
                    END
                ) as quantity'), // Menghitung jumlah partial_items
                DB::raw("CASE 
                            WHEN COUNT(partial_item.id) > 0 THEN 'AVAILABLE'
                            ELSE 'UNAVAILABLE'
                         END as status") // Menentukan status berdasarkan quantity
            ])
            ->groupBy('items.id', 'items.name', 'items.brand_name', 'items.category', 'partial_item.base_quantity');
        
        if($request->filled('search_name')) {
            $query->where('items.name', 'like',"%{$request->search_name}%");
        }
        if($request->filled('search_category')) {
            $query->where('items.category', $request->search_category);
        }

        $items = $query->orderByDesc('items.created_at')->paginate(20);

        $item_count = DB::table('items')->where('major_id','=',$major->id)->count();
        $warehouse = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();
        $item_type = DB::table('lookup as l')->where('code', '=','ITEM_TYPE')->get();

        return Inertia::render('ItemManagement/IndexMajor', [
            'items' => $items,
            'item_count' => $item_count,
            'filters' => $request->only(['search_name','search_category','search_warehouse']),
            'utils' => [
                'warehouses' => $warehouse,
                'item_types' => $item_type
            ],
            'major' => $major
        ]);
    }

    public function indexAK(Request $request)
    {
        $major = DB::table('major')->where('code','AK')->first();
        $query = DB::table('items')
            ->join('major', 'items.major_id', '=', 'major.id')
            ->join('partial_item','items.id', '=','partial_item.item_id')
            ->where('major.id', '=', $major->id)
            ->select([
                'items.id',
                'items.name',
                'items.brand_name',
                'items.category',
                'items.unit',
                'partial_item.base_quantity',
                DB::raw('COUNT(
                    CASE 
                        WHEN partial_item.is_available > 0 then 1
                        ELSE null
                    END
                ) as quantity'), // Menghitung jumlah partial_items
                DB::raw("CASE 
                            WHEN COUNT(partial_item.id) > 0 THEN 'AVAILABLE'
                            ELSE 'UNAVAILABLE'
                         END as status") // Menentukan status berdasarkan quantity
            ])
            ->groupBy('items.id', 'items.name', 'items.brand_name', 'items.category', 'partial_item.base_quantity');
        
        if($request->filled('search_name')) {
            $query->where('items.name', 'like',"%{$request->search_name}%");
        }
        if($request->filled('search_category')) {
            $query->where('items.category', $request->search_category);
        }

        $items = $query->orderByDesc('items.created_at')->paginate(20);

        $item_count = DB::table('items')->where('major_id','=',$major->id)->count();
        $warehouse = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();
        $item_type = DB::table('lookup as l')->where('code', '=','ITEM_TYPE')->get();

        return Inertia::render('ItemManagement/IndexMajor', [
            'items' => $items,
            'item_count' => $item_count,
            'filters' => $request->only(['search_name','search_category','search_warehouse']),
            'utils' => [
                'warehouses' => $warehouse,
                'item_types' => $item_type
            ],
            'major' => $major
        ]);
    }

    public function indexTU(Request $request)
    {
        $major = DB::table('major')->where('code', 'TU')->first();
        $query = DB::table('items')
            ->join('major', 'items.major_id', '=', 'major.id')
            ->join('partial_item','items.id', '=','partial_item.item_id')
            ->where('major.id', '=', $major->id)
            ->select([
                'items.id',
                'items.name',
                'items.brand_name',
                'items.category',
                'items.unit',
                'partial_item.base_quantity',
                DB::raw('COUNT(
                    CASE 
                        WHEN partial_item.is_available > 0 then 1
                        ELSE null
                    END
                ) as quantity'), // Menghitung jumlah partial_items
                DB::raw("CASE 
                            WHEN COUNT(partial_item.id) > 0 THEN 'AVAILABLE'
                            ELSE 'UNAVAILABLE'
                         END as status") // Menentukan status berdasarkan quantity
            ])
            ->groupBy('items.id', 'items.name', 'items.brand_name', 'items.category', 'partial_item.base_quantity');
        
        if($request->filled('search_name')) {
            $query->where('items.name', 'like',"%{$request->search_name}%");
        }
        if($request->filled('search_category')) {
            $query->where('items.category', $request->search_category);
        }

        $items = $query->orderByDesc('items.created_at')->paginate(20);

        $item_count = DB::table('items')->where('major_id','=',$major->id)->count();
        $warehouse = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();
        $item_type = DB::table('lookup as l')->where('code', '=','ITEM_TYPE')->get();

        return Inertia::render('ItemManagement/IndexMajor', [
            'items' => $items,
            'item_count' => $item_count,
            'filters' => $request->only(['search_name','search_category','search_warehouse']),
            'utils' => [
                'warehouses' => $warehouse,
                'item_types' => $item_type
            ],
            'major' => $major
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Items $items, string $code)
    {
        $items->load(['partialItems.warehouse.major'])->loadCount([
            'partialItems as count_all' => function ($query) {
                $query->select(DB::raw('count(*)'));
            },
            'partialItems as count_good_condition' => function ($query) {
                $query->where('condition_item', 'GOOD');
            },
            'partialItems as count_damaged_condition' => function ($query) {
                $query->where('condition_item', 'DAMAGED');
            }
        ]);
        
        // Optimasi transformasi item_image
        $items->partialItems = $items->partialItems->map(function ($partial) {
            $partial->item_image = $partial->item_image
                ? asset("storage/{$partial->item_image}")
                : null;
        
            return $partial;
        });
    
        $major = DB::table('major')->where('code', $code)->first();
        $warehouses = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();

        return Inertia::render('ItemManagement/DetailItems', [
            'data' => $items,
            'data_partial_count' => [
                'count_all' => $items->count_all,
                'count_good_condition' => $items->count_good_condition,
                'count_damaged_condition' => $items->count_damaged_condition,
            ],
            'utils' => [
                'major' => $major,
                'warehouses' => $warehouses
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
