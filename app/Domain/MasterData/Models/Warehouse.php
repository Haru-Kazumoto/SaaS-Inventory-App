<?php

namespace App\Domain\MasterData\Models;

use App\Domain\Organization\Models\Unit;
use App\Domain\Organization\Scopes\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Gudang / lokasi penyimpanan. unit_id null = gudang bersama organisasi. */
class Warehouse extends Model
{
    use BelongsToOrganization;

    protected $fillable = ['organization_id', 'unit_id', 'name', 'code', 'description'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
