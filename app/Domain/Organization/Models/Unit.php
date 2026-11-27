<?php

namespace App\Domain\Organization\Models;

use App\Domain\Organization\Scopes\BelongsToOrganization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Unit organisasi (Jurusan / Divisi / Cabang / … sesuai terminologi). */
class Unit extends Model
{
    use BelongsToOrganization;

    protected $fillable = ['organization_id', 'parent_id', 'name', 'code', 'description'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(UnitMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'unit_members')->withPivot('role')->withTimestamps();
    }
}
