<?php

namespace App\Http\Controllers;

use App\Dto\Borrower\CreateBorrowerDto;
use App\Dto\Transaction\FilterIndexDto;
use App\Http\Requests\CreateBorrowingRequest;
use App\Http\Services\AuthService;
use App\Models\Borrower;
use App\Models\Items;
use App\Models\ItemsOut;
use App\Models\PartialItem;
use App\Models\Transaction;
use App\Services\BorrowerService;
use App\Services\ProductsService;
use App\Services\TransactionLinesService;
use App\Services\TransactionService;
use App\Services\WarehouseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionController extends Controller
{

    public function __construct(
        protected AuthService $authService,
        protected TransactionService $transactionService,
        protected TransactionLinesService $transactionLineService,
        protected WarehouseService $warehouseService,
        protected ProductsService $productsService,
        protected BorrowerService $borrowerService
    ) {}

    // Borrow
    public function borrow(Request $request)
    {
        $data = $request->validate([
            'borrower_id' => 'required|integer',
            'occurred_at' => 'required|date_format:Y-m-d H:i:s',
            'lines'       => 'required|array|min:1',
            'lines.*.product_id'  => 'required|integer',
            'lines.*.warehouse_id' => 'required|integer',
            'lines.*.quantity'    => 'required|integer|min:1',
            'lines.*.condition'   => 'nullable|in:GOOD,DAMAGED',
            'description'         => 'nullable|string',
        ]);

        $txId = $this->transactionService->borrowOut(
            $data['borrower_id'],
            $data['occurred_at'],
            $data['lines'],
            $data['description'] ?? null
        );

        return back()->with('success', "Peminjaman dibuat: TX#$txId");
    }

    // Return
    public function returnBorrow(Request $request)
    {
        $data = $request->validate([
            'borrow_tx_id' => 'required|integer|exists:transaction,id',
            'borrower_id' => 'required|integer',
            'occurred_at' => 'required|date_format:Y-m-d H:i:s',
            'lines'       => 'required|array|min:1',
            'lines.*.product_id'     => 'required|integer',
            'lines.*.to_warehouse_id' => 'required|integer',
            'lines.*.quantity'       => 'required|integer|min:1',
            'lines.*.to_condition'   => 'nullable|in:GOOD,DAMAGED',
            'description'            => 'nullable|string',
        ]);

        $txId = $this->transactionService->returnBorrow(
            $data['borrow_tx_id'],  // ← tambah
            $data['borrower_id'],
            $data['occurred_at'],
            $data['lines'],
            $data['description'] ?? null
        );

        return redirect(route('borrowing.index'))->with('success', "Pengembalian dibuat: TX#$txId");
    }

    public function index(Request $request)
    {
        $user = $request?->user();
        $current_major = $user?->major;
        $major_id = $current_major?->id;

        $borrower_name = $request->query('borrower_name', null);
        $status = $request->query('status', null);

        $filterDto = new FilterIndexDto(null, $borrower_name, $status);

        return Inertia::render('Borrowing/Index', [
            'transactions' => $this->transactionService->getAll($major_id, $filterDto)
        ]);
    }

    public function create(Request $request)
    {
        $user = $request?->user();
        $major = $user?->major;
        $current_major = $major;
        $products = $this->productsService->getAll(null, $current_major?->id, null);

        return Inertia::render('Borrowing/Create', [
            'products' => $products
        ]);
    }

    public function store(CreateBorrowingRequest $request)
    {
        $data = $request->validated();

        $borrower_id = $this->borrowerService->store(new CreateBorrowerDto(
            fullname: $data['borrower_name'],
            supervisor_name: $data['borrower_supervisor'],
            phone: $data['borrower_phone'],
            room_name: $data['borrower_room'],
            description: $data['description'],
            major_id: Auth::user()?->major->id
        ));

        $this->transactionService->borrowOut($borrower_id, now(), $data['lines'], $data['description']);

        return redirect(route('borrowing.index'))->with("success", 'Peminjaman berhasil dibuat');
    }

    public function show(Transaction $transaction)
    {
        $detail = $this->transactionService->getDetail($transaction->id);

        return Inertia::render("Borrowing/Detail", [
            'transaction' => $detail['header'],
            'lines'       => $detail['lines'],
            'ledger'      => $detail['ledger'],
            'summary'     => $detail['summary'],
        ]);
    }

    public function returning(Transaction $transaction)
    {
        $id = $transaction->id;

        // Header transaksi pinjam
        $tx = DB::table('transaction as t')
            ->leftJoin('borrower as b', 'b.id', '=', 't.borrower_id')
            ->leftJoin('users as u', 'u.id', '=', 't.created_by')
            ->where('t.id', $id)
            ->where('t.tx_type', 'BORROW')
            ->selectRaw("
                t.id, 
                t.tx_number, 
                t.tx_type, 
                t.status, 
                t.occurred_at, 
                t.borrower_id, 
                b.fullname as borrower_name, 
                b.phone as borrower_phone,
                b.supervisor_name as borrower_supervisor,
                b.room_name as borrower_room,
                t.description,
                t.created_by, 
                u.fullname as created_by_name
            ")
            ->first();

        abort_unless($tx, 404);

        // Lines BORROW
        $lines = DB::table('transaction_lines as tl')
            ->join('products as p', 'p.id', '=', 'tl.product_id')
            ->leftJoin('warehouse as w', 'w.id', '=', 'tl.warehouse_id')
            ->leftJoin('unit_products as up', 'up.id', '=', 'p.unit_id')
            ->where('tl.transaction_id', $id)
            ->orderBy('tl.line_no')
            ->selectRaw("
                tl.id as line_id, tl.line_no,
                tl.product_id, p.code as product_code, p.name as product_name,
                p.type as product_type,
                tl.warehouse_id, w.name as warehouse_name,
                tl.quantity as borrowed_qty,
                tl.condition_from,
                up.name as unit_name
            ")
            ->get();

        // Hitung returned_qty per (product_id, warehouse_id, line_no) yang merujuk parent BORROW ini
        $returnTxIds = DB::table('transaction')
            ->where('parent_borrow_tx_id', $id)
            ->pluck('id');

        $returned = DB::table('product_logs as pl')
            ->whereIn('pl.ref_id', $returnTxIds)
            ->where('pl.ref_type', 'transaction')
            ->where('pl.move_type', 'RETURN')
            ->groupBy('pl.product_id', 'pl.warehouse_id', 'pl.line_no')
            ->selectRaw('
                pl.product_id,
                pl.warehouse_id,
                pl.line_no,
                SUM(pl.quantity) as returned_qty
            ')
            ->get()
            ->keyBy(fn($r) => "{$r->product_id}-{$r->warehouse_id}-{$r->line_no}");

        // $returned = DB::table('product_logs as pl')
        //     ->join('transaction as tr', function ($j) use ($id) {
        //         $j->on('tr.id', '=', 'pl.ref_id')
        //             ->where('pl.ref_type', '=', 'transaction')
        //             // ->where('tr.parent_borrow_tx_id', '=', $id)
        //             ->where('pl.ref_id', '=', $id) // <- still issue, not confirmed yet.
        //             ->where('pl.move_type', '=', 'RETURN');
        //     })
        //     ->groupBy('pl.product_id', 'pl.warehouse_id', 'pl.line_no')
        //     ->selectRaw('
        //         pl.product_id,
        //         pl.warehouse_id,
        //         pl.line_no,
        //         SUM(pl.quantity) as returned_qty
        //     ')
        //     ->get()
        //     ->keyBy(fn($r) => "{$r->product_id}-{$r->warehouse_id}-{$r->line_no}");

        // dd($returned);

        // Gabungkan & hitung outstanding per line
        $enriched = $lines->map(function ($r) use ($returned) {
            // dd($returned);
            $key = "{$r->product_id}-{$r->warehouse_id}-{$r->line_no}";
            $ret = (int) ($returned[$key]->returned_qty ?? 0);
            $borrowed = (int) $r->borrowed_qty;
            // dd($borrowed, $ret);
            $outstanding = max($borrowed - $ret, 0);
            return (object) [
                'line_id'        => $r->line_id,
                'line_no'        => $r->line_no,
                'product_id'     => $r->product_id,
                'product_code'   => $r->product_code,
                'product_name'   => $r->product_name,
                'product_type'   => $r->product_type,              // <— penting
                'is_consumable'  => $r->product_type === 'CONSUMABLE',
                'warehouse_id'   => $r->warehouse_id,
                'warehouse_name' => $r->warehouse_name,
                'unit_name'      => $r->unit_name,
                'borrowed_qty'   => $borrowed,
                'returned_qty'   => $ret,
                'outstanding'    => $outstanding,
                'condition_from' => $r->condition_from ?? 'GOOD',
            ];
        })->values();

        // dd($enriched);

        // Pilihan condition & warehouse (kalau mau return ke gudang lain boleh)
        $conditions = [
            ['label' => 'Kondisi Bagus', 'value' => 'GOOD'],
            ['label' => 'Kondisi Rusak', 'value' => 'DAMAGED'],
        ];
        $warehouses = DB::table('warehouse')
            ->orderBy('name')
            ->select(['id as value', 'name as label'])
            ->get();


        return Inertia::render('Borrowing/Return', [
            'transaction'     => $tx,
            'lines'           => $enriched,
            'conditionOptions' => $conditions,
            'warehouseOptions' => $warehouses,
        ]);
    }

    public function edit(int $id)
    {
        // header transaksi
        $tx = $this->transactionService->getDetail($id)['header']; // sudah punya fungsi ini
        abort_unless($tx && $tx->tx_type === 'BORROW', 404);

        // detail baris (line_id, product_id, warehouse_id, quantity)
        $items = $this->transactionLineService->getByTransaction($id)
            ->map(fn($r) => [
                'line_id'      => (int) $r->id,
                'product_id'   => (int) $r->product_id,
                'warehouse_id' => (int) $r->warehouse_id,
                'quantity'     => (int) $r->quantity,
            ]);

        // dropdown options
        $productOptions = $this->productsService->getAll(null, Auth::user()?->major->id, null);
        // $ware

        return Inertia::render('Borrowing/Edit', [
            'txId'             => (int) $tx->id,
            'occurred_at'      => $tx->occurred_at,   // "YYYY-mm-dd HH:ii:ss"
            'description'      => $tx->description,
            'items'            => $items,
            'productOptions'   => $productOptions,
            // 'warehouseOptions' => $warehouseOptions,
        ]);
    }

    // app/Http/Controllers/BorrowingController.php
    public function update(int $id, Request $request)
    {
        $data = $request->validate([
            'occurred_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'description' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_id' => ['nullable', 'integer'], // id line lama, null = line baru
            'lines.*.product_id' => ['required', 'integer', 'min:1'],
            'lines.*.warehouse_id' => ['required', 'integer', 'min:1'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $txId = $id;

        $this->transactionService->updateBorrow(
            txId: $txId,
            occurredAt: $data['occurred_at'],
            description: $data['description'] ?? null,
            lines: $data['lines'],
        );

        return back()->with('success', 'Transaksi berhasil diperbarui');
    }

    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     // Current user (return major model)
    //     $major = $this->authService->getMajorFromCurrentUser();

    //     // Ambil semua parameter filter dari request
    //     $borrower_name = $request->input('search_borrower_name');
    //     $room_borrower = $request->input('search_room_name');
    //     $phone_borrower = $request->input('search_phone_borrower');
    //     $teacher_name = $request->input('search_teacher_name');

    //     $data = DB::table('transaction as tx')
    //         ->join('borrower as br', 'br.transaction_id', '=', 'tx.id')
    //         ->leftJoin('items_out as io', 'io.transaction_id', '=', 'tx.id');

    //     if (Auth::user()->role->code !== "SUPER") {
    //         $data->where('br.major_id', '=', $major->id);
    //     }

    //     // Filter berdasarkan nama peminjam
    //     $data->when($borrower_name, function ($query, $borrower_name) {
    //         return $query->where('br.fullname', 'LIKE', "%{$borrower_name}%");
    //     });

    //     // Filter berdasarkan ruangan peminjam
    //     $data->when($room_borrower, function ($query, $room_borrower) {
    //         return $query->where('br.room_name', 'LIKE', "%{$room_borrower}%");
    //     });

    //     // Filter berdasarkan nomor HP peminjam
    //     $data->when($phone_borrower, function ($query, $phone_borrower) {
    //         return $query->where('br.phone', 'LIKE', "%{$phone_borrower}%");
    //     });

    //     // Filter berdasarkan nama guru
    //     $data->when($teacher_name, function ($query, $teacher_name) {
    //         return $query->where('br.teacher_name', 'LIKE', "%{$teacher_name}%");
    //     });

    //     // Ambil data hasil query
    //     $results = $data->select([
    //         'br.id',
    //         'br.fullname as borrower_name',
    //         'br.room_name as room_borrower',
    //         'br.phone as phone_borrower',
    //         'br.teacher_name',
    //         'tx.in_date',
    //         'tx.transaction_number',
    //         'tx.id as transaction_id',
    //         DB::raw("SUM(CASE WHEN io.action = 'OUT' THEN 1 ELSE 0 END) as total_items_out")
    //     ])
    //         ->groupBy(
    //             'br.id',
    //             'br.fullname',
    //             'br.room_name',
    //             'br.phone',
    //             'br.teacher_name',
    //             'tx.in_date',
    //             'tx.transaction_number',
    //             'tx.id'
    //         ) // Pastikan semua kolom yang diambil ada di GROUP BY
    //         ->orderByRaw("tx.in_date IS NOT NULL, tx.in_date ASC") // Prioritaskan NULL, lalu urut ASC
    //         ->orderBy('tx.created_at', 'DESC') // Urutkan berdasarkan created_at terbaru
    //         ->paginate(20);

    //     return Inertia::render('Transactions/Index', [
    //         'transactions' => $results
    //     ]);
    // }


    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     $major = $this->authService->getMajorFromCurrentUser();
    //     $items = DB::table('partial_item as pi')
    //         ->join('items as i', 'i.id', '=', 'pi.item_id')
    //         ->join('major as m', 'm.id', '=', 'i.major_id')
    //         ->where('m.id', '=', $major->id)
    //         ->where('pi.condition_item', '=', 'GOOD')
    //         ->where('pi.is_available', '=', 1)
    //         ->select([
    //             'pi.code_item',
    //             'i.name',
    //             'i.unit',
    //             'i.category',
    //             'pi.base_quantity'
    //         ])
    //         ->orderByDesc('pi.created_at')
    //         ->get();
    //     $warehouse = DB::table('warehouse as w')
    //         ->join('major as m', 'm.id', '=', 'w.major_id')
    //         ->where('w.major_id', '=', $major->id)
    //         ->select(['w.name', 'w.code'])
    //         ->get();

    //     return Inertia::render('Transactions/Create', [
    //         'items' => $items,
    //         'warehouse' => $warehouse
    //     ]);
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     // dd($request->all());
    //     DB::beginTransaction();
    //     try {
    //         $major = $this->authService->getMajorFromCurrentUser();

    //         $transaction = Transaction::create([
    //             'out_date' => Carbon::now(),
    //             'staff_id' => Auth::user()->id,
    //             'major_id' => $major->id,
    //             'description' => $request->description,
    //             'transaction_number' => rand(1000, 9000)
    //         ]);

    //         $borrower = Borrower::create([
    //             'fullname' => $request->fullname,
    //             'phone' => $request->phone,
    //             'teacher_name' => $request->teacher_name,
    //             'room_name' => $request->room_code,
    //             'transaction_id' => $transaction->id,
    //             'major_id' => $transaction->major_id
    //         ]);

    //         $isOnlyConsumable = collect($request->item_choosens)
    //             ->every(fn($item) => PartialItem::where('code_item', $item['code_item'])
    //                 ->with('item')
    //                 ->first()
    //                 ->item
    //                 ->category === "CONSUMABLE");

    //         foreach ($request->item_choosens as $index => $item) {
    //             $partial_item = PartialItem::where('code_item', $item['code_item'])->with(['item'])->first();
    //             $category = $partial_item->item->category;

    //             // Buat ItemsOut
    //             $itemsOut = ItemsOut::create([
    //                 'action' => 'OUT',
    //                 'item_id' => $partial_item->item_id,
    //                 'transaction_id' => $transaction->id,
    //                 'borrower_id' => $borrower->id,
    //                 'partial_item_id' => $partial_item->id
    //             ]);

    //             if ($category === "DURABLE") {
    //                 // Update status jika item adalah DURABLE
    //                 // updateQuietly memaksa melewati event boot agar tidak terjadi event hooks di model.
    //                 $partial_item->updateQuietly([
    //                     'is_available' => 0
    //                 ]);
    //             } else {
    //                 $partial_item->update([
    //                     'base_quantity' => $partial_item->base_quantity - (int) $item['quantity'],
    //                 ]);

    //                 // Jika kategori CONSUMABLE, langsung update process_date & status_in
    //                 $itemsOut->update([
    //                     'process_date' => Carbon::now(),
    //                     'quantity' => (int) $item['quantity'],
    //                     'status_in' => 'GOOD'
    //                 ]);
    //             }
    //         }

    //         if ($isOnlyConsumable) {
    //             $transaction->update([
    //                 'in_date' => Carbon::now()
    //             ]);
    //         }

    //         DB::commit();
    //         return redirect()->route('transaction.index')->with('success', 'Transaksi berhasil dibuat!');
    //     } catch (\Exception $exception) {
    //         DB::rollBack();
    //         return back()->with('failed', 'Terjadi kesalahan: ' . $exception->getMessage());
    //     }
    // }

    /**
     * Display the specified resource.
     */
    // public function show(Transaction $transaction)
    // {
    //     $transaction->load([
    //         'borrower',
    //         'itemsOut.partialItem.item'
    //     ]);

    //     return Inertia::render('Transactions/Show', [
    //         'data' => $transaction
    //     ]);
    // }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Transaction $transaction)
    // {
    //     $data = DB::table('borrower as b')
    //         ->leftJoin('transaction as t', 't.id', '=', 'b.transaction_id') // ganti 'transaction' -> 'transactions' jika memang nama tabelnya jamak
    //         ->where('b.transaction_id', $transaction->id)
    //         ->select([
    //             'b.id',
    //             'b.fullname',
    //             'b.phone',
    //             'b.teacher_name',
    //             'b.room_name',
    //             DB::raw('t.description as description'),
    //         ])
    //         ->first();

    //     $data_items = DB::table('items_out as io')
    //         ->join('items as i', 'i.id', '=', 'io.item_id')
    //         ->join('partial_item as pi', 'pi.item_id', '=', 'i.id') // sesuai SQL-mu saat ini
    //         ->where('io.transaction_id', $transaction->id)
    //         ->where('io.action', 'OUT')
    //         ->select([
    //             'io.id',
    //             'i.name',
    //             'i.unit',
    //             'pi.code_item',
    //             'io.quantity',
    //             'pi.base_quantity'
    //         ])
    //         ->get();

    //     $major = $this->authService->getMajorFromCurrentUser();
    //     $items = DB::table('partial_item as pi')
    //         ->join('items as i', 'i.id', '=', 'pi.item_id')
    //         ->join('major as m', 'm.id', '=', 'i.major_id')
    //         ->where('m.id', '=', $major->id)
    //         ->where('pi.condition_item', '=', 'GOOD')
    //         ->where('pi.is_available', '=', 1)
    //         ->select([
    //             'pi.code_item',
    //             'i.name',
    //             'i.unit',
    //             'i.category',
    //             'pi.base_quantity'
    //         ])
    //         ->orderByDesc('pi.created_at')
    //         ->get();
    //     $warehouse = DB::table('warehouse as w')
    //         ->join('major as m', 'm.id', '=', 'w.major_id')
    //         ->where('w.major_id', '=', $major->id)
    //         ->select(['w.name', 'w.code'])
    //         ->get();

    //     return Inertia::render('Transactions/Edit', [
    //         'items' => $items,
    //         'warehouse' => $warehouse,
    //         'data' => $data,
    //         'data_items' => $data_items
    //     ]);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Transaction $transaction)
    // {
    //     DB::transaction(function () use ($request, $transaction) {
    //         // 1) Update transaksi + borrower
    //         $transaction->update([
    //             'staff_id'     => Auth::id(),
    //             'description'  => $request->description,
    //         ]);

    //         $borrower = Borrower::firstOrCreate(
    //             ['transaction_id' => $transaction->id],
    //             ['fullname' => $request->fullname, 'phone' => $request->phone, 'teacher_name' => $request->teacher_name, 'room_name' => $request->room_code]
    //         );
    //         $borrower->update([
    //             'fullname'     => $request->fullname,
    //             'phone'        => $request->phone,
    //             'teacher_name' => $request->teacher_name,
    //             'room_name'    => $request->room_code,
    //         ]);

    //         // 2) Siapkan peta request: code_item => qty_baru
    //         $reqItems = collect($request->item_choosens ?? [])
    //             ->keyBy('code_item')
    //             ->map(fn($x) => (int) ($x['quantity'] ?? 0));

    //         // 3) Ambil semua ItemsOut existing utk transaksi ini + lock baris terkait
    //         $existing = DB::table('items_out as io')
    //             ->join('partial_item as pi', 'pi.id', '=', 'io.partial_item_id')
    //             ->select('io.id', 'io.item_id', 'io.partial_item_id', 'io.quantity', 'pi.code_item', 'pi.base_quantity')
    //             ->where('io.transaction_id', $transaction->id)
    //             ->where('io.action', 'OUT')
    //             ->lockForUpdate()
    //             ->get();

    //         // 4) Proses setiap existing row
    //         foreach ($existing as $row) {
    //             $code = $row->code_item;
    //             $old  = (int) $row->quantity;

    //             if (!$reqItems->has($code)) {
    //                 // item DIHAPUS dari request -> RESTORE stok sebanyak old, delete row
    //                 DB::table('partial_item')->where('id', $row->partial_item_id)->update([
    //                     'base_quantity' => DB::raw("base_quantity + {$old}")
    //                 ]);
    //                 DB::table('items_out')->where('id', $row->id)->delete();
    //                 continue;
    //             }

    //             // item MASIH ADA -> hitung delta
    //             $new   = (int) $reqItems->get($code);
    //             $delta = $new - $old; // >0 = minta tambahan, <0 = pengembalian

    //             if ($delta !== 0) {
    //                 // lock stok item terkait
    //                 $pi = DB::table('partial_item')->where('id', $row->partial_item_id)->lockForUpdate()->first();
    //                 if (!$pi) {
    //                     throw new \RuntimeException("Partial item $code tidak ditemukan.");
    //                 }

    //                 if ($delta > 0) {
    //                     // minta tambahan -> cek stok cukup
    //                     if ($pi->base_quantity < $delta) {
    //                         throw new \RuntimeException("Stok {$code} tidak cukup. Sisa {$pi->base_quantity}, tambah {$delta}.");
    //                     }
    //                     // kurangi stok
    //                     DB::table('partial_item')->where('id', $row->partial_item_id)->update([
    //                         'base_quantity' => $pi->base_quantity - $delta
    //                     ]);
    //                 } else {
    //                     // delta < 0 -> kembalikan stok |delta|
    //                     DB::table('partial_item')->where('id', $row->partial_item_id)->update([
    //                         'base_quantity' => $pi->base_quantity + abs($delta)
    //                     ]);
    //                 }

    //                 // update quantity di items_out ke total baru
    //                 DB::table('items_out')->where('id', $row->id)->update([
    //                     'quantity'   => $new,
    //                     'updated_at' => now(),
    //                 ]);
    //             }

    //             // sudah diproses → hapus dari req map agar sisa req adalah item BARU
    //             $reqItems->forget($code);
    //         }

    //         // 5) Item BARU (ada di request tapi belum ada di items_out)
    //         foreach ($reqItems as $code => $qtyNew) {
    //             if ($qtyNew <= 0) {
    //                 continue; // abaikan nol/negatif
    //             }

    //             $pi = DB::table('partial_item')->where('code_item', $code)->lockForUpdate()->first();
    //             if (!$pi) {
    //                 throw new \RuntimeException("Item dengan kode {$code} tidak ditemukan.");
    //             }

    //             if ($pi->base_quantity < $qtyNew) {
    //                 throw new \RuntimeException("Stok {$code} tidak cukup. Sisa {$pi->base_quantity}, minta {$qtyNew}.");
    //             }

    //             // create items_out + potong stok
    //             ItemsOut::create([
    //                 'action'          => 'OUT',
    //                 'item_id'         => $pi->item_id,
    //                 'transaction_id'  => $transaction->id,
    //                 'borrower_id'     => $borrower->id,
    //                 'partial_item_id' => $pi->id,
    //                 'quantity'        => $qtyNew,
    //             ]);

    //             DB::table('partial_item')->where('id', $pi->id)->update([
    //                 'base_quantity' => $pi->base_quantity - $qtyNew
    //             ]);
    //         }
    //     });

    //     return redirect()->route('transaction.index')->with('success', 'Transaksi berhasil diperbarui!');
    // }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            // Load relasi itemsOut dan partialItem
            $transaction->load(['itemsOut.partialItem']);

            // Ubah is_available menjadi 1 untuk setiap partialItem yang terkait
            foreach ($transaction->itemsOut as $itemOut) {
                if ($itemOut->partialItem) {
                    $itemOut->partialItem->update(['is_available' => 1]);
                }
            }

            // Hapus transaksi setelah memperbarui item
            $transaction->delete();
        });

        return back()->with('success', 'Transaksi berhasil dihapus');
    }

    public function reStoreTransaction(Transaction $transaction)
    {
        $transaction->load([
            'borrower',
            'itemsOut' => fn($q) => $q
                ->where('action', 'OUT')
                ->where(function ($query) {
                    $query->whereNull('status_in')
                        ->orWhere('status_in', 'BORROWED');
                })
                ->with([
                    'partialItem.item' => fn($q) => $q
                        ->whereIn('category', ['DURABLE', 'CONSUMABLE'])
                ])
        ]);

        return Inertia::render("Transactions/Restore", [
            'data' => $transaction
        ]);
    }

    public function submitRestoreTransaction(Request $request, Transaction $transaction)
    {
        $transaction->load(['itemsOut']);
        $list_items = $request->item_choosens;

        DB::beginTransaction();
        try {
            // Cek apakah semua item memiliki status_in
            $allHaveStatus = collect($list_items)->every(fn($item) => !empty($item['status_in']));

            // Cek apakah hanya ada status APPROVED, DAMAGED, atau LOST
            $onlyApprovedOrDamaged = collect($list_items)->every(fn($item) => in_array($item['status_in'], ["APPROVED", "DAMAGED", "LOST"]));

            // Cek apakah ada item yang masih berstatus BORROWED
            $hasBorrowedItems = collect($list_items)->contains(fn($item) => $item['status_in'] === "BORROWED");

            foreach ($list_items as $item) {
                $partial_item = PartialItem::where('code_item', $item['code_item'])->with('item')->first();

                if (!$partial_item) {
                    continue;
                }

                $status = $item['status_in'];
                $description = $item['description'];

                // Update kondisi PartialItem berdasarkan status
                if ($status === "DAMAGED") {
                    $partial_item->update(['is_available' => 0, 'condition_item' => "DAMAGED"]);
                } elseif ($status === "LOST") {
                    $partial_item->update(['is_available' => 0, 'condition_item' => "LOST"]);
                } elseif ($status === "APPROVED") {
                    $partial_item->update(['is_available' => 1, 'condition_item' => "GOOD"]);
                }

                // Ambil semua items_out yang sesuai dengan kode item di transaksi ini
                $transactionItems = ItemsOut::where('transaction_id', $transaction->id)
                    ->where('item_id', $partial_item->item->id) // Sesuaikan dengan item_id dari partial_item
                    ->get(); // Mengambil semua record yang cocok

                foreach ($transactionItems as $transactionItem) {
                    if ($transactionItem->status_in !== 'BORROWED') {
                        $transactionItem->update([
                            'status_in' => $status,
                            'process_date' => Carbon::now()
                        ]);

                        ItemsOut::create([
                            'action' => 'IN',
                            'item_id' => $transactionItem->item_id,
                            'transaction_id' => $transactionItem->transaction_id,
                            'borrower_id' => $transactionItem->borrower_id,
                            'partial_item_id' => $transactionItem->partial_item_id,
                            'process_date' => Carbon::now(),
                            'status_in' => $status,
                            'description' => $description
                        ]);
                    } else {
                        $transactionItem->update([
                            'status_in' => $status,
                            'process_date' => Carbon::now()
                        ]);
                    }
                }
            }


            // Jika semua item memiliki status_in DAN tidak ada yang BORROWED, update transaction
            if ($allHaveStatus && !$hasBorrowedItems) {
                $transaction->updateOrFail([
                    'in_date' => Carbon::now(),
                ]);
            }

            DB::commit();
            return redirect()->route('transaction.index')->with('success', 'Pengembalian barang berhasil diproses.');
        } catch (\Exception $exception) {
            DB::rollback();
            return back()->with('failed', 'Terdapat kegagalan dalam mengembalikan barang: ' . $exception->getMessage());
        }
    }

    public function indexItemsOut(Request $request)
    {
        $item_name = $request->input("item_name");

        $major = $this->authService->getMajorFromCurrentUser();

        $query = DB::table('partial_item as pi')
            ->leftJoin('items as i', 'i.id', '=', 'pi.item_id')
            ->leftJoin('transaction as tx', function ($join) {
                $join->on('tx.id', '=', DB::raw('(SELECT io.transaction_id FROM items_out io WHERE io.partial_item_id = pi.id ORDER BY io.created_at DESC LIMIT 1)'));
            })
            ->leftJoin('borrower as b', 'b.transaction_id', '=', 'tx.id')
            ->join('major as m', 'm.id', '=', 'tx.major_id')
            ->where('pi.is_available', '=', 0);

        $query->when($major, function ($query, $major) {
            $query->where('m.id', '=', $major->id);
        });

        $query->when($item_name, function ($query, $item_name) {
            return $query->where('i.name', 'like', "%{$item_name}%");
        });

        $result = $query->select([
            'pi.id',
            'pi.code_item',
            'i.name as item_name',
            'tx.transaction_number',
            'tx.out_date',
            'b.room_name'
        ])
            ->orderByDesc('pi.created_at') // Urutkan berdasarkan waktu dibuatnya partial_item
            ->paginate(20);


        return Inertia::render('Transactions/IndexItems', [
            'items_out' => $result
        ]);
    }
}
