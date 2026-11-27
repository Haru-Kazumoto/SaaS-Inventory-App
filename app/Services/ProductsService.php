<?php

namespace App\Services;

use App\Dto\Products\CreateOpeningLineDto;
use App\Dto\Products\CreateProductsDto;
use App\Dto\Products\FilterIndexDto;
use App\Dto\Products\PersistProductDto;
use App\Dto\Transaction\CreateOpeningPayload;
use App\Dto\TransactionLines\CreateTransactionLineDto;
use App\Repositories\Interfaces\ProductLogsRepositoryInterface;
use App\Repositories\Interfaces\ProductsRepositoryInterface;
use App\Repositories\ProductLogsRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductsService
{
    public function __construct(
        protected ProductsRepositoryInterface $productRepository,
        protected ProductLogsRepositoryInterface $productLogsRepository,
        protected TransactionService $transactionService
    ) {}

    public function getAll(?FilterIndexDto $filter, ?int $major_id, ?int $page)
    {
        return $this->productRepository->getAll($filter, $major_id, $page);
    }

    public function getProductDetailWithAggregates(int $product_id)
    {
        return $this->productRepository->getProductDetailWithAggregates($product_id);
    }

    public function getById(int $id)
    {
        return $this->productRepository->getById($id);
    }

    /**
     * @param CreateProductsDto $data
     * @param array<int, array{status:string, quantity:int}> $openingLines
     * @return int product_id
     */
    public function store(CreateProductsDto $data, array $openingLines = []): int
    {
        $imagePath = null;
        if ($data->image_product instanceof UploadedFile) {
            $dir = 'products';
            $ext = $data->image_product->getClientOriginalExtension();
            $filename = 'product_image_' . Str::uuid() . '_' . now()->format('Y_m_d') . '.' . $ext;
            $imagePath = $data->image_product->storeAs($dir, $filename, 'public');
        }

        try {
            return DB::transaction(function () use ($data, $imagePath, $openingLines) {

                // 1) Insert product → product_id
                $productId = $this->productRepository->store([
                    'name'          => $data->name,
                    'code'          => $data->code,
                    'brand_name'    => $data->brand_name,
                    'type'          => $data->type,
                    'price'         => $data->price,
                    'labeling_date' => $data->labeling_date,
                    'unit_id'       => $data->unit_id,
                    'sub_type_id'   => $data->sub_type_id,
                    'major_id'      => $data->major_id,
                    'image_product' => $imagePath,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // 2) Jika ada opening → buat transaksi OPENING + lines + product_logs
                if (!empty($openingLines)) {
                    // Bangun DTO opening lines (per line mencakup warehouse & condition)
                    $lines = [];
                    $i = 1;
                    foreach ($openingLines as $l) {
                        if (($l['quantity'] ?? 0) <= 0) continue;

                        $lines[] = new CreateOpeningLineDto(
                            product_id: $productId,
                            warehouse_id: (int) $l['warehouse_id'],
                            quantity: (int) $l['quantity'],
                            condition: $l['status'] ?: 'GOOD',
                            line_no: $i++
                        );
                    }

                    if (!empty($lines)) {
                        $payload = new CreateOpeningPayload(
                            occurred_at: now()->format('Y-m-d H:i:s'),
                            lines: $lines
                        );
                        $this->transactionService->postOpeningForProduct($productId, $payload);
                    }
                }

                return $productId;
            }, 2);
        } catch (\Throwable $e) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $e;
        }
    }


    public function softDelete(int $id)
    {
        return DB::transaction(function () use ($id) {
            return $this->productRepository->softDelete($id);
        });
    }

    /**
     * Update master product + reconcile quantities (tanpa from/to eksplisit).
     * @param int $productId
     * @param array $master // field Barang: name, code, brand_name, type, price, labeling_date, sub_type_id/unit_id (atau via code sesuai controller-mu)
     * @param UploadedFile|null $image
     * @param array $targetLines // array of {warehouse_id, status(condition), quantity}
     */
    public function updateAndReconcile(int $productId, array $master, ?UploadedFile $image, array $targetLines): void
    {
        // Normalisasi target: gabungkan baris duplikat (jika ada)
        $target = [];
        foreach ($targetLines as $l) {
            /* $wh = (int) ($l['warehouse_id'] ?? 0); */
            $wh = $l['warehouse_id'];
            $cond = ($l['status'] ?? 'GOOD') ?: 'GOOD';
            $qty = (int) ($l['quantity'] ?? 0);
            if ($wh <= 0 || $qty < 0) continue;

            $key = $wh . '|' . $cond;
            $target[$key] = ($target[$key] ?? 0) + $qty;
        }

        // Simpan image (opsional)
        $imagePath = null;
        if ($image instanceof UploadedFile) {
            $dir = 'products';
            $filename = 'product_image_' . Str::uuid()->toString() . '_' . now()->format('Y_m_d') . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs($dir, $filename, 'public');
        }

        DB::transaction(function () use ($productId, $master, $imagePath, $target) {
            // 1) UPDATE MASTER
            $update = [
                'name'          => $master['name']        ?? null,
                'code'          => $master['code']        ?? null,
                'brand_name'    => $master['brand_name']  ?? null,
                'type'          => $master['type']        ?? null,
                'price'         => $master['price']       ?? null,
                'labeling_date' => $master['labeling_date'] ?? null,
                'sub_type_id'   => $master['sub_type_id'] ?? null,
                'unit_id'       => $master['unit_id']     ?? null,
                'updated_at'    => now(),
            ];
            if ($imagePath) {
                $update['image_product'] = $imagePath;
            }

            DB::table('products')->where('id', $productId)->update($update);

            // 2) Ambil SALDO SAAT INI per bucket
            $current = $this->productLogsRepository->getOnHandByBucket($productId); // key "whId|COND" => on_hand int

            // 3) Bangun diff -> surplus & defisit
            $allKeys = array_unique(array_merge(array_keys($current), array_keys($target)));
            $surplus = []; // key => amount (harus -)
            $deficit = []; // key => amount (harus +)

            foreach ($allKeys as $key) {
                $cur = $current[$key] ?? 0;
                $tar = $target[$key]  ?? 0;
                $delta = $tar - $cur; // positif: butuh tambah, negatif: kurangin

                if ($delta > 0) $deficit[$key] = $delta;
                elseif ($delta < 0) $surplus[$key] = -$delta;
            }

            // Helper parse key
            $parseKey = function (string $k) {
                [$w, $c] = explode('|', $k, 2);
                return [(int)$w, $c];
            };

            $rows = [];
            $now = now()->format('Y-m-d H:i:s');

            // 4) Pairing greedy: prioritaskan TRANSFER/RECLASSIFY/MOVE
            foreach ($surplus as $kFrom => $amountSurp) {
                if ($amountSurp <= 0) continue;
                [$whFrom, $condFrom] = $parseKey($kFrom);

                foreach ($deficit as $kTo => $amountDef) {
                    if ($amountDef <= 0) continue;
                    [$whTo, $condTo] = $parseKey($kTo);

                    // minimal pasangan yg masuk akal? kita izinkan semua → MOVE; optimalkan label:
                    $pairQty = min($amountSurp, $amountDef);
                    if ($pairQty <= 0) continue;

                    $moveType = 'MOVE';
                    if ($condFrom === $condTo && $whFrom !== $whTo) $moveType = 'TRANSFER';
                    elseif ($whFrom === $whTo && $condFrom !== $condTo) $moveType = 'RECLASSIFY';

                    // catat DOUBLE ENTRY
                    $rows[] = [
                        'product_id'   => $productId,
                        'warehouse_id' => $whFrom,
                        'condition'    => $condFrom,
                        'move_type'    => $moveType,
                        'quantity'     => $pairQty,
                        'delta'        => -$pairQty,
                        'ref_type'     => 'reconcile',
                        'ref_id'       => null,
                        'line_no'      => 0,
                        'occurred_at'  => $now,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                    $rows[] = [
                        'product_id'   => $productId,
                        'warehouse_id' => $whTo,
                        'condition'    => $condTo,
                        'move_type'    => $moveType,
                        'quantity'     => $pairQty,
                        'delta'        => +$pairQty,
                        'ref_type'     => 'reconcile',
                        'ref_id'       => null,
                        'line_no'      => 0,
                        'occurred_at'  => $now,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];

                    // kurangi sisa
                    $surplus[$kFrom] -= $pairQty;
                    $deficit[$kTo]   -= $pairQty;

                    if ($surplus[$kFrom] <= 0) break; // next surplus
                }
            }

            // 5) Sisa surplus → ADJUSTMENT_NEG, sisa deficit → ADJUSTMENT_POS
            foreach ($surplus as $k => $amt) {
                if ($amt <= 0) continue;
                [$wh, $cond] = $parseKey($k);
                $rows[] = [
                    'product_id'   => $productId,
                    'warehouse_id' => $wh,
                    'condition'    => $cond,
                    'move_type'    => 'ADJUSTMENT_NEG',
                    'quantity'     => $amt,
                    'delta'        => -$amt,
                    'ref_type'     => 'reconcile',
                    'ref_id'       => null,
                    'line_no'      => 0,
                    'occurred_at'  => $now,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
            foreach ($deficit as $k => $amt) {
                if ($amt <= 0) continue;
                [$wh, $cond] = $parseKey($k);
                $rows[] = [
                    'product_id'   => $productId,
                    'warehouse_id' => $wh,
                    'condition'    => $cond,
                    'move_type'    => 'ADJUSTMENT_POS',
                    'quantity'     => $amt,
                    'delta'        => +$amt,
                    'ref_type'     => 'reconcile',
                    'ref_id'       => null,
                    'line_no'      => 0,
                    'occurred_at'  => $now,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }

            // 6) Tulis ledger
            $this->productLogsRepository->bulkInsert($rows);
        });
    }

    public function count(int $major_id)
    {
        return $this->productRepository->count($major_id);
    }

    public function countFromAllMajor()
    {
        $count_all_products = $this->productRepository->countFromAllMajor();

        $map = [
            'TJKT' => 'tjkt',
            'TE'   => 'te',
            'TO'   => 'to',
            'AK'   => 'ak',
            'TU'   => 'tu',
            'KKPI' => 'kkpi'
        ];

        $base = [
            'all'  => 0,
            'kkpi' => 0,
            'tjkt' => 0,
            'te'   => 0,
            'to'   => 0,
            'ak'   => 0,
            'tu'   => 0,
        ];

        $result_count = collect($count_all_products)->reduce(function ($carry, $row) use ($map) {
            $cnt = (int) ($row->products_count ?? 0);
            $carry['all'] += $cnt;

            $key = $map[strtoupper($row->name ?? '')] ?? null;
            if ($key) {
                $carry[$key] = $cnt;
            }
            return $carry;
        }, $base);

        return $result_count;
    }
}
