<?php

namespace App\Domain\Borrowing\Models;

use App\Domain\Inventory\Models\Product;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Scopes\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionLine extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'transaction_id', 'line_no', 'product_id', 'warehouse_id', 'quantity',
        'condition_from', 'condition_to', 'borrow_line_id',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** Baris pengembalian untuk baris pinjam ini. */
    public function returnLines(): HasMany
    {
        return $this->hasMany(self::class, 'borrow_line_id');
    }
}
