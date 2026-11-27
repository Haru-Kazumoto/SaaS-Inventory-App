<?php

namespace App\Domain\Inventory\Queries;

use App\Domain\Organization\Support\OrgScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Riwayat pergerakan stok non-peminjaman: barang masuk & barang dihapuskan. */
final class StockMovementQuery
{
    /** @param array{product_name?: ?string, product_type?: ?string, unit_id?: ?string, date?: ?string} $filters */
    public function restocks(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->base($filters)
            ->where('pl.move_type', 'RESTOCK')
            ->paginate($perPage)->withQueryString()
            ->through(fn ($r) => $this->row($r));
    }

    /** Setiap kejadian penghapusan (baris catatan masuk ke OFF). */
    public function writeOffs(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->base($filters)
            ->where('pl.move_type', 'WRITE_OFF')
            ->where('pl.condition', 'OFF')
            ->paginate($perPage)->withQueryString()
            ->through(fn ($r) => $this->row($r));
    }

    /** Semua penghapusan (tanpa paginasi) untuk export. */
    public function writeOffsForExport(?int $unitId = null): array
    {
        return $this->base(['unit_id' => $unitId])
            ->where('pl.move_type', 'WRITE_OFF')
            ->where('pl.condition', 'OFF')
            ->get()
            ->map(fn ($r) => $this->row($r))
            ->all();
    }

    private function base(array $filters): Builder
    {
        return DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->join('units as un', 'un.id', '=', 'p.unit_id')
            ->join('warehouses as w', 'w.id', '=', 'pl.warehouse_id')
            ->leftJoin('uoms as u', 'u.id', '=', 'p.uom_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('users as us', 'us.id', '=', 'pl.created_by')
            ->tap(fn ($q) => OrgScope::apply($q, 'pl'))
            ->tap(fn ($q) => OrgScope::units($q, 'p.unit_id', ! empty($filters['unit_id']) ? (int) $filters['unit_id'] : null))
            ->when($filters['product_name'] ?? null, fn ($q, $v) => $q->where('p.name', 'like', "%{$v}%"))
            ->when($filters['product_type'] ?? null, fn ($q, $v) => $q->where('p.type', $v))
            ->when($filters['date'] ?? null, fn ($q, $v) => $q->whereDate('pl.occurred_at', $v))
            ->orderByDesc('pl.occurred_at')
            ->orderByDesc('pl.id')
            ->selectRaw('
                pl.id AS log_id, pl.quantity, pl.occurred_at, pl.reason,
                p.id AS product_id, p.name AS product_name, p.code, p.type, p.brand_name,
                un.name AS unit_name, w.name AS warehouse_name,
                u.name AS uom_name, c.name AS category_name, us.fullname AS created_by_name
            ');
    }

    private function row(object $r): array
    {
        return [...(array) $r, 'quantity' => (int) $r->quantity];
    }
}
