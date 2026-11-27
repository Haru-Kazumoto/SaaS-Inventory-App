<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Items extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'name',
        'brand_name',
        'price',
        'category',
        'unit',
        'major_id',
        'created_by',
        'updated_by',
    ];

    public function partialItems(): HasMany
    {
        return $this->hasMany(PartialItem::class,'item_id');
    }
}
