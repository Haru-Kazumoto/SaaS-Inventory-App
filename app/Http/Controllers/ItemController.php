<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequest;  
use App\Http\Services\AuthService;
use App\Models\Items;
use App\Models\Major;
use App\Models\PartialItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ItemController extends Controller
{

    private $authService;

    public function __construct(AuthService $authService) 
    {
        $this->authService = $authService;
    }

    public function index(Request $request)
    {
        $major = $this->authService->getMajorFromCurrentUser();
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
                DB::raw("COUNT(
                    CASE 
                        WHEN partial_item.is_available > 0 AND partial_item.condition_item = 'GOOD'
                        THEN 1 
                        ELSE NULL 
                    END
                ) as quantity"), // Menghitung jumlah partial_items
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

        return Inertia::render('ItemManagement/Index', [
            'items' => $items,
            'item_count' => $item_count,
            'filters' => $request->only(['search_name','search_category','search_warehouse']),
            'utils' => [
                'warehouses' => $warehouse,
                'item_types' => $item_type
            ]
        ]);
    }

    public function create()
    {
        $major = $this->authService->getMajorFromCurrentUser();
        $warehouses = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();

        return Inertia::render('ItemManagement/Create', compact('warehouses'));
    }

    public function store(ItemRequest $request)
    {
        $request->validated();

        $major = $this->authService->getMajorFromCurrentUser();

        DB::beginTransaction();
        try {
            $item = Items::create([
                'name' => $request->name,
                'major_id' => $major->id,
                'price' => $request->price,
                'category' => $request->category,
                'brand_name' => $request->brand_name,
                'unit' => $request->unit,
                'created_by' => Auth::user()->id
            ]); 
            $partial_items = $request->partial_items;

            foreach ($partial_items as $index => $partial) {
                $warehouse = Warehouse::where('code', $partial['warehouse_name'])->first(['id']);
                $imagePath = null;

                if ($request->hasFile("partial_items.$index.item_image")) {
                    $file = $request->file("partial_items.$index.item_image");
                    $customName = 'item_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $imagePath = $file->storeAs('item_images', $customName, 'public');
                }

                PartialItem::create([
                    'labeling_date' => $partial['labeling_date'],
                    'code_item' => $partial['code_item'],
                    'condition_item' => $partial['condition_item'],
                    'base_quantity' => $partial['base_quantity'],
                    'warehouse_id' => $warehouse->id,
                    'item_image' => $imagePath,
                    'item_id' => $item->id,
                    'created_by' => Auth::user()->id,
                ]);
            }

            DB::commit();
            
            return redirect()->route('item-management.index')->with('success', 'Item berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('failed', "Item gagal ditambahkan. Error: {$e->getMessage()}");
        }
    }

    public function show(Items $items) 
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
    
        $major = $this->authService->getMajorFromCurrentUser();
        $warehouses = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();

        return Inertia::render('ItemManagement/Detail', [
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

    public function edit(Items $items)
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
    
        $major = $this->authService->getMajorFromCurrentUser();
        $warehouses = DB::table('warehouse as w')->where('w.major_id', '=',$major->id)->get();

        return Inertia::render('ItemManagement/Edit', [
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

    public function update(Items $items, Request $request)
    {
        $data = $request->all();

        // Ubah semua item_image yang bernilai "null" menjadi null
        foreach ($data['partial_items'] as &$item) {
            if (isset($item['item_image']) && $item['item_image'] === "null") {
                $item['item_image'] = null;
            }

            if(isset($item['base_quantity']) && $item['base_quantity'] === "null") {
                $item['base_quantity'] = null;
            }
        }

        $request->replace($data);

        $request->validate([
            'name' => 'required|string',
            'brand_name' => 'required|string',
            'price' => 'required|numeric',
            'unit' => 'required|string',
            'partial_items' => 'required|array',
            'partial_items.*.labeling_date' => 'required|string',
            'partial_items.*.code_item' => 'required|string',
            'partial_items.*.condition_item' => 'required|string',
            'partial_items.*.item_image' => 'nullable|image',
            'partial_items.*.base_quantity' => 'nullable|numeric'
        ]);
        $major = $this->authService->getMajorFromCurrentUser();

        DB::beginTransaction();
        try {
            // Update data item utama
            $items->update([
                'name' => $request['name'],
                'major_id' => $major->id,
                'price' => $request['price'],
                'category' => $request['category'],
                'brand_name' => $request['brand_name'],
                'unit' => $request['unit'],
                'updated_by' => Auth::user()->id
            ]);

            $partial_items = $request['partial_items'] ?? [];

            $existingPartialItemIds = $items->partialItems()->pluck('id')->toArray();
            $newPartialItemIds = [];

            foreach ($partial_items as $index => $partial) {

                // Cek apakah partial item ini sudah ada atau baru
                $partialItem = isset($partial['id']) ? PartialItem::where('id', $partial['id'])->first() : null;

                $imagePath = $partialItem->item_image ?? null;

                // Jika ada file gambar baru diupload
                if ($request->hasFile("partial_items.$index.item_image")) {
                    $file = $request->file("partial_items.$index.item_image");
                    $customName = 'item_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $imagePath = $file->storeAs('item_images', $customName, 'public');

                    // Hapus gambar lama jika ada
                    if ($partialItem && $partialItem->item_image) {
                        Storage::disk('public')->delete($partialItem->item_image);
                    }
                }

                if ($partialItem) {
                    $is_available = $partial['condition_item'] === 'GOOD' ? true : false;

                    // Update partial item yang sudah ada
                    $partialItem->update([
                        'labeling_date' => $partial['labeling_date'],
                        'code_item' => $partial['code_item'],
                        'condition_item' => $partial['condition_item'],
                        'is_available' => $is_available,
                        'base_quantity' => $partial['base_quantity'],
                        'item_image' => $imagePath,
                        'updated_by' => Auth::user()->id,
                    ]);
                } else {
                    // Buat partial item baru
                    $partialItem = PartialItem::create([
                        'labeling_date' => $partial['labeling_date'],
                        'code_item' => $partial['code_item'],
                        'condition_item' => $partial['condition_item'],
                        'base_quantity' => $partial['base_quantity'],
                        'item_image' => $imagePath,
                        'item_id' => $items->id,
                        'created_by' => Auth::user()->id,
                    ]);
                }

                $newPartialItemIds[] = $partialItem->id;
            }

            // Hapus partial item yang tidak ada dalam request
            $items->partialItems()->whereNotIn('id', $newPartialItemIds)->delete();

            DB::commit();
            
            return redirect()->route('item-management.index')->with('success', 'Item berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('failed', "Item gagal diperbarui. Error: {$e->getMessage()}");
        }
    }

    public function destroy(Items $items)
    {
        DB::transaction(function() use ($items) {
            // we don't have to delete partial items because its already cascade on delete on relations
            $items->delete();
        });

        return back()->with('success', 'Barang dihapus');
    }

    public function getProductsByMajor()
    {
        $major = $this->authService->getMajorFromCurrentUser();
        $items = DB::table('partial_item as pi')
            ->join('items as i', 'i.id', '=','pi.item_id')
            ->where('i.major_id', '=', $major->id)
            ->where('pi.condition_item','=','GOOD')
            ->where('pi.is_available','=',1)
            ->select([
                'pi.code_item',
                'i.name',
                'i.unit',
                'i.category',
                'pi.base_quantity'
            ])
            ->orderByDesc('pi.created_at')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $items
        ]);
    }
}
