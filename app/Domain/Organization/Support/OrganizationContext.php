<?php

namespace App\Domain\Organization\Support;

use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Models\Unit;
use App\Models\User;
use Illuminate\Support\Collection;
use LogicException;

/**
 * Konteks tenant untuk satu request / job (di-bind sebagai scoped singleton).
 * Di-set oleh middleware ResolveOrganization dari user login.
 */
final class OrganizationContext
{
    private ?Organization $organization = null;
    private ?User $user = null;
    private ?Collection $units = null;

    public function set(Organization $organization, ?User $user = null): void
    {
        $this->organization = $organization;
        $this->user = $user;
        $this->units = null;
        Access::flush();
    }

    public function clear(): void
    {
        $this->organization = null;
        $this->user = null;
        $this->units = null;
    }

    public function has(): bool
    {
        return $this->organization !== null;
    }

    public function organization(): Organization
    {
        return $this->organization ?? throw new LogicException('Konteks organisasi belum di-set.');
    }

    public function id(): int
    {
        return $this->organization()->id;
    }

    public function idOrNull(): ?int
    {
        return $this->organization?->id;
    }

    /** User login mengelola seluruh organisasi (owner/admin). */
    public function managesOrganization(): bool
    {
        return (bool) $this->user?->managesOrganization();
    }

    /**
     * Unit yang boleh diakses user login:
     * owner/admin → semua unit organisasi; anggota → unit tempat ia terdaftar.
     *
     * @return Collection<int, Unit>
     */
    public function accessibleUnits(): Collection
    {
        if ($this->units !== null) {
            return $this->units;
        }

        $query = Unit::query()->orderBy('name');

        if ($this->user && ! $this->managesOrganization()) {
            $query->whereHas('members', fn ($q) => $q->where('user_id', $this->user->id));
        }

        return $this->units = $query->get(['id', 'organization_id', 'name', 'code']);
    }

    /** @return int[] */
    public function accessibleUnitIds(): array
    {
        return $this->accessibleUnits()->pluck('id')->all();
    }

    public function canAccessUnit(?int $unitId): bool
    {
        return $unitId !== null && in_array($unitId, $this->accessibleUnitIds(), true);
    }
}
