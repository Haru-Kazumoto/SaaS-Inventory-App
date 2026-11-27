<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'title',
        'description',
        'reported_by',
        'status',
        'related_module',
        'proceed_description'
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ReportImages::class);
    }
}
