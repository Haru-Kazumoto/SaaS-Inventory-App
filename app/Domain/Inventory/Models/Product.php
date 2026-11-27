<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Inventory\Enums\ProductType;
use App\Domain\MasterData\Models\Category;
use App\Domain\MasterData\Models\Uom;
use App\Domain\Organization\Models\Unit;
use App\Domain\Organization\Scopes\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id', 'unit_id', 'category_id', 'uom_id', 'code', 'name', 'brand_name',
        'type', 'price', 'labeling_date', 'image_path',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'price' => 'integer',
            'labeling_date' => 'date',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProductLog::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
