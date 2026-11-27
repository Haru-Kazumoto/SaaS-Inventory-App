<?php

namespace App\Models;

use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Models\Unit;
use App\Domain\Organization\Models\UnitMember;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Akses:
 * - is_platform_admin → pengelola SaaS (area /platform), tanpa organisasi.
 * - organization_role owner/admin → kelola seluruh organisasi.
 * - organization_role member → akses unit lewat unit_members (role admin/staff/viewer per unit).
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'organization_id',
        'organization_role',
        'is_platform_admin',
        'username',
        'fullname',
        'email',
        'phone',
        'password',
        'profile_image',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
            'organization_role' => OrganizationRole::class,
        ];
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image ? asset('storage/'.$this->profile_image) : null;
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'unit_members')->withPivot('role')->withTimestamps();
    }

    public function unitMemberships(): HasMany
    {
        return $this->hasMany(UnitMember::class);
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    /** Owner/admin organisasi: akses semua unit, anggota, data master. */
    public function managesOrganization(): bool
    {
        return (bool) $this->organization_role?->managesOrganization();
    }

    /**
     * Kode akses ringkas untuk frontend (menu per peran):
     * DEVELOPER = pengelola platform, SUPER = admin organisasi, ADMIN = anggota unit.
     */
    public function accessCode(): string
    {
        return match (true) {
            $this->isPlatformAdmin() => 'DEVELOPER',
            $this->managesOrganization() => 'SUPER',
            default => 'ADMIN',
        };
    }

    public function accessLabel(): string
    {
        return match (true) {
            $this->isPlatformAdmin() => 'Pengelola Platform',
            default => $this->organization_role?->label() ?? 'Anggota',
        };
    }
}
