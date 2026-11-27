<?php

namespace App\Domain\Borrowing\Queries;

use App\Domain\Borrowing\Models\Transaction;
use App\Domain\Inventory\Enums\ProductType;
use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Domain\Organization\Support\OrgScope;
use Illuminate\Support\Facades\DB;

/** Data untuk form peminjaman & form pengembalian. */
final class BorrowFormQuery
{
    /** Barang (dengan stok Bagus) di unit yang boleh dicatat user. */
    public function createOptions(): array
    {
        $unitIds = Access::operableUnitIds();

        $good = DB::table('product_logs')
            ->where('condition', 'GOOD')
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(delta) AS qty');

        $products = DB::table('products as p')
            ->leftJoin('uoms as u', 'u.id', '=', 'p.uom_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoinSub($good, 'g', 'g.product_id', '=', 'p.id')
            ->whereNull('p.deleted_at')
            ->whereIn('p.unit_id', $unitIds ?: [0])
            ->tap(fn ($q) => OrgScope::apply($q, 'p'))
            ->orderBy('p.name')
            ->selectRaw('p.id, p.unit_id, p.name, p.code, p.type, u.name AS uom_name, c.name AS category_name, COALESCE(g.qty, 0) AS available')
            ->get()
            ->map(fn ($r) => [...(array) $r, 'available' => (int) $r->available])
            ->all();

        return [
            'units' => CurrentOrganization::units()->whereIn('id', $unitIds)->map->only(['id', 'name'])->values()->all(),
            'products' => $products,
        ];
    }

    /** Baris pinjaman beserta sisa yang bisa dikembalikan. */
    public function returnOptions(Transaction $tx): array
    {
        $detail = app(BorrowingQuery::class)->detail($tx);

        return [
            'transaction' => $detail['transaction'],
            'lines' => collect($detail['lines'])->map(fn ($l) => [
                ...$l,
                'is_consumable' => $l['product_type'] === ProductType::Consumable->value,
            ])->all(),
            'warehouses' => DB::table('warehouses')
                ->where('organization_id', CurrentOrganization::id())
                ->where(fn ($q) => $q->whereNull('unit_id')->orWhere('unit_id', $tx->unit_id))
                ->orderBy('name')
                ->get(['id as value', 'name as label']),
        ];
    }
}
