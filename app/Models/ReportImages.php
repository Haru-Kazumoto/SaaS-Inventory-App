<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportImages extends Model
{
    protected $table = 'report_images';
    protected $appends = ['image_url'];

    protected $fillable = [
        'image',
        'report_id'
    ];

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }
    
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}
