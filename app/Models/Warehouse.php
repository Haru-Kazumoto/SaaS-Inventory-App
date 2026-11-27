<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    protected $table = 'warehouse';

    protected $guarded = [];

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'major_id');
    }
}
