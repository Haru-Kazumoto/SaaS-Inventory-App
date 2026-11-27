<?php

namespace App\Domain\Organization\Models;

use App\Domain\Organization\Enums\UnitRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitMember extends Model
{
    protected $fillable = ['unit_id', 'user_id', 'role'];

    protected function casts(): array
    {
        return ['role' => UnitRole::class];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
