<?php

namespace App\Domain\Procurement\Actions;

use App\Domain\Organization\Support\Access;
use App\Domain\Procurement\Enums\RequestStatus;
use App\Domain\Procurement\Models\RequestItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/** Ajukan pengadaan barang untuk satu unit. */
final class SubmitRequest
{
    /**
     * @param  array{unit_id: int, item_name: string, quantity: int, uom: string, reference_url?: ?string, reason?: ?string}  $data
     */
    public function handle(array $data, ?UploadedFile $image = null): RequestItem
    {
        Access::authorizeManage($data['unit_id']);

        $imagePath = $image?->storeAs('request-items', 'request_'.Str::uuid().'.'.$image->getClientOriginalExtension(), 'public');

        return RequestItem::create([
            'unit_id' => $data['unit_id'],
            'requested_by' => auth()->id(),
            'item_name' => $data['item_name'],
            'quantity' => $data['quantity'],
            'uom' => $data['uom'],
            'reference_url' => $data['reference_url'] ?? null,
            'reason' => $data['reason'] ?? null,
            'image_path' => $imagePath,
            'status' => RequestStatus::Pending,
        ]);
    }
}
