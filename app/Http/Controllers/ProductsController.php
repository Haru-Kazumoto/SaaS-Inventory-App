<?php

namespace App\Http\Controllers;

use App\Dto\ProductLogs\CreateProductLogsDto;
use App\Dto\Products\CreateProductsDto;
use App\Dto\Products\FilterIndexDto;
use App\Exports\ExportProducts;
use App\Exports\ExportProductsPerSubType;
use App\Http\Requests\CreateProductRequest;
use App\Models\Major;
use App\Models\Products;
use App\Services\MonthlyReport;
use App\Services\ProductLogsService;
use App\Services\ProductsService;
use App\Services\SubTypeProductService;
use App\Services\UnitProductService;
use App\Services\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{

    public function __construct(
        public ProductsService $productsService,
        public ProductLogsService $productLogsService,
        public WarehouseService $warehouseService,
        public UnitProductService $unitProductService,
        public SubTypeProductService $subTypeProductService
    ) {}

    public function index(Request $request)
    {
        $user = $request?->user();
        $major = $user?->major;
        $name = $request->query('name', null);
        $code = $request->query('code', null);
        $type = $request->query('type', null);
        // $warehouse_id => $request->query('')

        $filter = new FilterIndexDto($name, $code, $type);

        return Inertia::render("Products/Index", [
            'products' => $this->productsService->getAll($filter, $major?->id, 15),
            'createable' => true,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request?->user();
        $major = $user?->major;

        return Inertia::render("Products/Create", [
            'warehouses' => $this->warehouseService->getWarehouseByMajorId($major->id),
            'units' => $this->unitProductService->getAll($major->id),
            'subTypes' => $this->subTypeProductService->getAll($major->id)
        ]);
    }

    public function indexMajor(string $major, Request $request)
    {
        abort_if(!in_array($major, ['tjkt', 'kkpi', 'to', 'te', 'ak', 'tu']), 403, 'plis lah jangan iseng begitu bro');

        $name = $request->query('name', null);
        $code = $request->query('code', null);
        $type = $request->query('type', null);

        $major = Major::where('code', strtoupper($major))->first();
        $filter = new FilterIndexDto($name, $code, $type);
        

        $res = $this->productsService->getAll($filter, $major->id, 15);

        return Inertia::render("Products/Index", [
            'products' => $res,
            'createable' => false,
            'major' => strtolower($major->name)
        ]);
    }

    public function export(string $major)
    {
        $upper_major = strtoupper($major);
        $curr_major = Major::where('name', $upper_major)->first();
        
        $current_date = now()->format('Y-m-d');

        $export = new ExportProductsPerSubType(
            major_id: $curr_major->id
        );

        return Excel::download($export, "Daftar Barang $upper_major $current_date.xlsx");
    }

    public function exportOffProducts(string $major)
    {
        $major_id = Major::where('code', strtoupper($major))->first()->id;

        $export = new \App\Exports\ExportOffProductsPerSubType(
            major_id: (int) $major_id,
        );

        $current_date = now()->format('Y-m-d');
        return Excel::download($export, "Daftar Barang Dihapuskan $current_date.xlsx");
    }

    public function indexOffProducts(Request $request)
    {
        $user = $request->user();
        $major = $user?->major;
        $product_name = $request->query('name', null);

        $current_major = $major;
        $offProducts = $this->productLogsService->getOffByMajor((int) $current_major?->id, null, $product_name, 15);

        return Inertia::render('Products/IndexOff', [
            'offProducts' => $offProducts,
            'major' => strtolower($current_major?->name)
        ]);
    }

    public function edit(Products $products, Request $request)
    {
        $user = $request?->user();
        $major = $user?->major;

        if (!empty($products->product_image)) {
            $products->product_image = Storage::disk('public')->url($products->product_image);
        }

        return Inertia::render('Products/Edit', [
            'warehouses' => $this->warehouseService->getWarehouseByMajorId($major->id),
            'units' => $this->unitProductService->getAll($major->id),
            'subTypes' => $this->subTypeProductService->getAll($major->id),
            'product' => $this->productsService->getByid($products->id),
            'quantities_product' => $this->productLogsService->getProductsWarehouse($products->id)
        ]);
    }

    public function update(Request $request, int $id)
    {
        // Validasi ringan (lengkapi sesuai FormRequest-mu)
        $data = $request->validate([
            'name'          => 'required|string',
            'code'          => 'required|string|max:100',
            'brand_name'    => 'nullable|string',
            'type'          => 'required|in:CONSUMABLE,DURABLE',
            'price'         => 'required|numeric|min:0',
            'labeling_date' => 'required|date_format:Y-m-d',
            'sub_type_code' => 'required|string',
            'unit_code'     => 'required|string',
            'image_product' => 'nullable|image',
            'quantities'    => 'array',
            'quantities.*.warehouse_id' => 'required|integer',
            'quantities.*.status'       => 'nullable|in:GOOD,DAMAGED',
            'quantities.*.quantity'     => 'required|integer|min:0',
        ], [
            'code.max' => 'Kode barang tidak boleh lebih dari 100 karakter.'
        ]);

        // dd($data);

        // Resolve sub_type_id & unit_id dari code (atau jika kamu sudah pakai service lain, panggil di sini)
        $subType   = DB::table('sub_type_products')->where('code', $data['sub_type_code'])->first();
        $unit      = DB::table('unit_products')->where('code', $data['unit_code'])->first();
        abort_if(!$subType || !$unit, 422, 'Unit/SubType tidak ditemukan');

        $master = [
            'name'          => $data['name'],
            'code'          => $data['code'],
            'brand_name'    => $data['brand_name'] ?? null,
            'type'          => $data['type'],
            'price'         => $data['price'],
            'labeling_date' => $data['labeling_date'],
            'sub_type_id'   => $subType->id,
            'unit_id'       => $unit->id,
        ];

        $quantities = $data['quantities'] ?? [];
        $image      = $request->file('image_product'); // UploadedFile|null

        $this->productsService->updateAndReconcile($id, $master, $image, $quantities);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui & stok direkonsiliasi.');
    }

    public function store(CreateProductRequest $request)
    {
        $data = $request->validated();
        $user = $request?->user();
        $major = $user?->major;

        $unit    = $this->unitProductService->getByCode($data['unit_code']);
        $subType = $this->subTypeProductService->getByCode($data['sub_type_code']);

        $fromDto = new CreateProductsDto(
            name: $data['name'],
            code: $data['code'],
            brand_name: $data['brand_name'] ?? null,
            type: $data['type'],
            price: (int) $data['price'],
            labeling_date: $data['labeling_date'],           // 'Y-m-d'
            image_product: $request->file('image_product'),  // UploadedFile|null
            major_id: $major?->id,
            unit_id: $unit->id,
            sub_type_id: $subType->id
            // NOTE: warehouse_id DIHAPUS dari DTO ini
        );

        // Handle 'quantities' bisa berupa array atau string JSON dari FormData
        $openingLinesRaw = $data['quantities'] ?? [];
        if (is_string($openingLinesRaw)) {
            $decoded = json_decode($openingLinesRaw, true);
            $openingLinesRaw = is_array($decoded) ? $decoded : [];
        }

        // Normalisasi shape: status, warehouse_id, quantity
        $openingLines = array_values(array_map(function ($row) {
            return [
                'status'       => $row['status'] ?? null,         // GOOD/DAMAGED/… (OFF sebaiknya tidak dipakai untuk opening)
                'warehouse_id' => (int) ($row['warehouse_id'] ?? 0),
                'quantity'     => (int) ($row['quantity'] ?? 0),
            ];
        }, $openingLinesRaw));

        $productId = $this->productsService->store($fromDto, $openingLines);

        return redirect(route('products.index'))
            ->with('success', "Berhasil membuat produk baru! (ID: {$productId})");
    }

    public function show(Products $products, Request $request)
    {
        $user = $request?->user();
        $major = $user?->major;

        $is_createable = $major === null;

        $warehouses = $this->warehouseService->getAllByProductsQuantityInformation($products->major_id, $products->id);

        $major = Major::find($products->major_id);
        $lower_major = strtolower($major->name);

        return Inertia::render('Products/Detail', [
            'product' => $this->productsService->getProductDetailWithAggregates($products->id),
            'warehouse_products' => $this->productLogsService->getProductsWarehouse($products->id),
            'warehouses' => $warehouses,
            'createable' => $is_createable,
            'major' => $lower_major
        ]);
    }

    public function softDelete(Products $products)
    {
        $this->productsService->softDelete($products->id);

        return back()->with('success', 'Berhasil menghapus produk!');
    }
}
