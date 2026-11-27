<?php

namespace App\Domain\Procurement\Models;

use App\Domain\Organization\Models\Unit;
use App\Domain\Organization\Scopes\BelongsToOrganization;
use App\Domain\Procurement\Enums\RequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** Pengajuan pengadaan barang. */
class RequestItem extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'unit_id', 'requested_by', 'item_name', 'quantity', 'uom', 'reference_url',
        'image_path', 'reason', 'status', 'processed_by', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'quantity' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
