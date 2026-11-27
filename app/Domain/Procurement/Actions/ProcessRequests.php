<?php

namespace App\Domain\Procurement\Actions;

use App\Domain\Organization\Support\Access;
use App\Domain\Procurement\Enums\RequestStatus;
use App\Domain\Procurement\Models\RequestItem;
use Illuminate\Validation\ValidationException;

/** Setujui / tolak pengajuan (massal) — khusus admin organisasi. Hanya yang masih Menunggu yang diproses. */
final class ProcessRequests
{
    /** @param int[] $ids @return int jumlah yang diproses */
    public function handle(array $ids, RequestStatus $decision): int
    {
        Access::authorizeOrganization();

        if ($decision === RequestStatus::Pending) {
            throw ValidationException::withMessages(['assign_type' => 'Keputusan harus Disetujui atau Ditolak.']);
        }

        return RequestItem::query()
            ->whereIn('id', $ids)
            ->where('status', RequestStatus::Pending)
            ->update([
                'status' => $decision,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
    }
}
