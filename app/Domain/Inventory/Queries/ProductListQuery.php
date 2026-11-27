<?php

namespace App\Domain\Inventory\Queries;

use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\OrgScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ProductListQuery
{
    /** @param array{name?: ?string, code?: ?string, type?: ?string, unit_id?: ?string} $filters */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $stock = DB::table('product_logs')
            ->where('condition', '!=', 'OFF')
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(delta) AS qty');

        $query = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('uoms as u', 'u.id', '=', 'p.uom_id')
            ->join('units as un', 'un.id', '=', 'p.unit_id')
            ->leftJoinSub($stock, 'stk', 'stk.product_id', '=', 'p.id')
            ->whereNull('p.deleted_at')
            ->tap(fn ($q) => OrgScope::apply($q, 'p'))
            ->tap(fn ($q) => OrgScope::units($q, 'p.unit_id', isset($filters['unit_id']) ? (int) $filters['unit_id'] : null))
            ->when($filters['name'] ?? null, fn ($q, $v) => $q->where('p.name', 'like', "%{$v}%"))
            ->when($filters['code'] ?? null, fn ($q, $v) => $q->where('p.code', 'like', "%{$v}%"))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('p.type', $v))
            ->orderByDesc('p.created_at')
            ->selectRaw('
                p.id, p.name, p.code, p.brand_name, p.type, p.price, p.unit_id,
                un.name AS unit_name, c.name AS category_name, u.name AS uom_name,
                COALESCE(stk.qty, 0) AS quantity_on_hand
            ');

        return $query->paginate($perPage)->withQueryString()->through(fn ($r) => [
            ...(array) $r,
            'quantity_on_hand' => (int) $r->quantity_on_hand,
            'can_manage' => Access::canManage((int) $r->unit_id),
        ]);
    }
}
