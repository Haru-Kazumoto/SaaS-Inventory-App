<?php

namespace App\Domain\Identity\Data;

use App\Domain\Organization\Enums\OrganizationRole;
use Illuminate\Http\UploadedFile;

final readonly class MemberData
{
    /**
     * @param  array<int, array{unit_id: int, role: string}>  $memberships  peran per unit (untuk anggota)
     */
    public function __construct(
        public string $fullname,
        public string $username,
        public ?string $email,
        public ?string $phone,
        public ?string $password,
        public OrganizationRole $organizationRole,
        public array $memberships,
        public ?UploadedFile $avatar,
    ) {}
}
