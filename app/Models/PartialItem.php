<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartialItem extends Model
{
    protected $table = 'partial_item';

    protected $fillable = [
        'labeling_date',
        'code_item',
        'is_available',
        'condition_item',
        'base_quantity',
        'item_image',
        'created_by',
        'updated_by',
        'warehouse_id',
        'item_id'
    ];

    public static function booted() 
    {
        static::creating(function($item) {
            $item->is_available = $item->condition_item === 'GOOD';
        });

        static::updating(function($item) {
            $item->is_available = $item->condition_item === 'GOOD';
        });
    }


    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class);
    }
}
