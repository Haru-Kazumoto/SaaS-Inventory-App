<?php

namespace App\Http\Controllers;

use App\Exports\ExportRequestItem;
use App\Http\Services\AuthService;
use App\Models\Major;
use App\Models\RequestItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RequestItemController extends Controller
{

    private $authService;

    public function __construct(AuthService $authService) 
    {
        $this->authService = $authService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // filter param queries
        $period = $request->filled('period') ? (int) $request->input('period') : Carbon::now()->year; 
        $item_name = $request->input('item_name', null);
        $status = $request->input('status', null);
        $major_request = $request->input('major_request', null);

        $current_major = $this->authService->getMajorFromCurrentUser();
        $query = DB::table('request_item as ri')
            ->join('major as m', 'm.id', '=', 'ri.major_id');

        $query->when($status, function($query, $status) {
            $query->where('ri.request_status', '=', $status);
        });

        $query->when($current_major, function($query, $current_major) {
            $query->where('m.id', '=', $current_major->id);
        });

        $query->when($major_request, function($query, $major_request) {
            $query->where('m.code','=',$major_request);
        });

        $query->whereYear('ri.created_at', $period);

        $query->when($item_name, function($query, $item_name) {
            $query->where('ri.brand_name', 'like',"%{$item_name}%");
        });

        // Ambil data paginasi
        $requestItems = $query
            ->select([
                'ri.id',
                'ri.brand_name',
                'ri.quantity',
                'ri.request_status',
                'ri.reason_request',
                'ri.proceed_at',
                'ri.created_at',
                'ri.unit',
                'm.name'
            ])
            ->orderBy('ri.created_at','desc')
            ->paginate(20)
            ->appends($request->only(['period', 'item_name','status']));

         // === Query Summary Dalam 1 Kali Request ===
        $summaryQuery = DB::table('request_item as ri')
            ->join('major as m', 'm.id', '=', 'ri.major_id');

        // terapkan filter yang sama
        $summaryQuery->when($current_major, function($q, $current_major) {
            $q->where('m.id', '=', $current_major->id);
        });

        $summaryQuery->whereYear('ri.created_at', $period);

        $summaryQuery->when($item_name, function($q, $item_name) {
            $q->where('ri.brand_name', 'like', "%{$item_name}%");
        });

        // hitung total, approved, rejected, pending dengan conditional count
        $summary = $summaryQuery
            ->selectRaw("
                COUNT(*) as total_request,
                SUM(CASE WHEN ri.request_status = 'PENDING' THEN 1 ELSE 0 END) as total_pending,
                SUM(CASE WHEN ri.request_status = 'APPROVED' THEN 1 ELSE 0 END) as total_approved,
                SUM(CASE WHEN ri.request_status = 'REJECTED' THEN 1 ELSE 0 END) as total_rejected
            ")
            ->first();

        return Inertia::render('RequestItem/Index', [
            'request_items' => $requestItems,
            'count' => [
                'request'  => $summary->total_request ?? 0,
                'pending'  => $summary->total_pending ?? 0,
                'approved' => $summary->total_approved ?? 0,
                'rejected' => $summary->total_rejected ?? 0,
            ],
            'filters' => [
                'period' => $period,
                'item_name' => $item_name,
                'status' => $status,
                'majors' => Major::all()
            ],
        ]);
    }


    //dua fungsi ini harusnya dipindahin ke service ya pak ade, pindahin aja karna saya males hehehe
    public function countQuantityRequestItemByMajor(?Major $major)
    {
        $query = DB::table('request_item');

        if(!is_null($major)){
            $query->where('major_id','=',$major->id);
        }

        $result = $query->sum('quantity');

        return $result;
    }

    public function bulkAssignments(Request $request)
    {
        $validated = $request->validate([
            'assign_type' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        DB::beginTransaction();

        try {
            DB::table('request_item')
                ->whereIn('id', $validated['ids'])
                ->where('request_status', 'PENDING')
                ->update([
                    'request_status' => $validated['assign_type'],
                    'proceed_at' => now()
                ]);

            DB::commit();

            return back()->with('success','Berhasil Memproses!');
        } catch(\Exception $e) {
            DB::rollback();
            return back()->with('failed',"Gagal Memperoses :(, \n{$e->getMessage()}");
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('RequestItem/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string',
            'quantity' => 'required|numeric',
            'link_shop' => 'nullable|string',
            'reason_request' => 'nullable|string',
            'image_item' => 'nullable|image',
            'unit' => 'required|string'
        ]);

        $major = $this->authService->getMajorFromCurrentUser();

        DB::beginTransaction();
        try {
            // Simpan gambar jika ada
            $imagePath = null;
            if ($request->hasFile('image_item')) {
                $file = $request->file('image_item');
                $extension = $file->getClientOriginalExtension(); // Ambil ekstensi file
                $fileName = 'request_item_' . Carbon::now()->format('d_m_Y') . '.' . $extension;
                
                // Simpan ke storage/public/request_items
                $imagePath = $file->storeAs('request_items', $fileName, 'public');
            }

            // Simpan data ke database
            RequestItem::create([
                'brand_name' => $request->brand_name,
                'quantity' => $request->quantity,
                'link_shop' => $request->link_shop,
                'reason_request' => $request->reason_request,
                'image_item' => $imagePath, // Simpan path gambar di database,
                'requestor_id' => Auth::user()->id,
                'request_status' => 'PENDING',
                'unit' => $request->unit,
                'major_id' => $major->id,
            ]);

            DB::commit();

            return redirect()->route('request-item.index')->with('success','Berhasil membuat pengajuan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('failed',$e->getMessage());
        }
    }

    // buat field buat nyimpen deskripsi kalo di tolak sama superadmin, sama bkin step (pengajuan, proses (tolak, acc), barang otw, barang sampe)

    public function processRequest(RequestItem $requestItem)
    {
        $requestItem->image_item = $requestItem->image_item
            ? asset('storage/'.$requestItem->image_item)
            : null;

        return Inertia::render('RequestItem/Process', ['data' => $requestItem]);
    }

    public function storeProcessRequest(Request $request, RequestItem $requestItem) 
    {
        $next_step = $request->current_step;
        $next_status = $request->current_step_status;

        DB::beginTransaction();
        try {
            $requestItem->update([
                'current_step' => $next_step,
                'current_step_status' => $next_status
            ]);

            DB::commit();

            return back()->with('success','Berhasil memproses barang!');
        } catch(\Exception $e) {
            DB::rollBack();
            return back()->with('failed', 'Gagal menambahkan barang :'.$e->getMessage());
        }
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
