<?php

namespace App\Domain\Procurement\Queries;

use App\Domain\Organization\Support\OrgScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RequestListQuery
{
    /** @param array{period?: ?string, item_name?: ?string, status?: ?string, unit_id?: ?string} $filters */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->base($filters)
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('ri.status', $v))
            ->orderByDesc('ri.created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($r) => [...(array) $r, 'quantity' => (int) $r->quantity]);
    }

    /** Ringkasan per status untuk periode & filter yang sama (tanpa filter status). */
    public function summary(array $filters): array
    {
        $row = $this->base($filters)
            ->reorder()
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN ri.status = 'PENDING' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN ri.status = 'APPROVED' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN ri.status = 'REJECTED' THEN 1 ELSE 0 END) AS rejected
            ")
            ->first();

        return [
            'request' => (int) $row->total,
            'pending' => (int) $row->pending,
            'approved' => (int) $row->approved,
            'rejected' => (int) $row->rejected,
        ];
    }

    /** Semua baris untuk export (filter status opsional). */
    public function forExport(array $filters, array $statuses): array
    {
        return $this->base($filters)
            ->when($statuses !== [], fn ($q) => $q->whereIn('ri.status', $statuses))
            ->orderBy('ri.created_at')
            ->get()
            ->all();
    }

    private function base(array $filters): Builder
    {
        $year = (int) ($filters['period'] ?? now()->year);

        return DB::table('request_items as ri')
            ->join('units as un', 'un.id', '=', 'ri.unit_id')
            ->leftJoin('users as u', 'u.id', '=', 'ri.requested_by')
            ->tap(fn ($q) => OrgScope::apply($q, 'ri'))
            ->tap(fn ($q) => OrgScope::units($q, 'ri.unit_id', ! empty($filters['unit_id']) ? (int) $filters['unit_id'] : null))
            ->whereYear('ri.created_at', $year)
            ->when($filters['item_name'] ?? null, fn ($q, $v) => $q->where('ri.item_name', 'like', "%{$v}%"))
            ->select([
                'ri.id', 'ri.item_name', 'ri.quantity', 'ri.uom', 'ri.reason', 'ri.reference_url', 'ri.status',
                'ri.processed_at', 'ri.created_at', 'ri.unit_id', 'un.name as unit_name', 'u.fullname as requested_by_name',
            ]);
    }
}
