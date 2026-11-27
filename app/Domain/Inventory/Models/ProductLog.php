<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Inventory\Enums\MoveType;
use App\Domain\Inventory\Enums\StockCondition;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Scopes\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Baris ledger stok. Hanya ditulis lewat StockLedger. */
class ProductLog extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'product_id', 'warehouse_id', 'move_type', 'condition', 'quantity', 'delta',
        'ref_type', 'ref_id', 'line_no', 'reason', 'created_by', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'move_type' => MoveType::class,
            'condition' => StockCondition::class,
            'quantity' => 'integer',
            'delta' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
