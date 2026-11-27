<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemsOut extends Model
{
    protected $table = 'items_out';

    protected $fillable = [
        'action',
        'description',
        'status_in',
        'process_date',
        'transaction_id',
        'item_id',
        'quantity',
        'borrower_id',
        'partial_item_id'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    public function partialItem(): BelongsTo
    {
        return $this->belongsTo(PartialItem::class);
    }
}
