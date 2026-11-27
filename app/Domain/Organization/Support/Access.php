<?php

namespace App\Domain\Organization\Support;

use App\Domain\Organization\Enums\UnitRole;
use Illuminate\Support\Facades\DB;

/**
 * Aturan akses per unit untuk user login (dipakai controller/Action).
 *
 *  - Admin organisasi (owner/admin): semua hak di semua unit.
 *  - Anggota unit: admin  → kelola barang, stok, data master unit, pengajuan
 *                  staff  → catat peminjaman & pengembalian
 *                  viewer → hanya melihat
 */
final class Access
{
    /** @var array<int, UnitRole>|null */
    private static ?array $roles = null;
    private static ?int $rolesFor = null;

    public static function managesOrganization(): bool
    {
        return CurrentOrganization::context()->managesOrganization();
    }

    public static function unitRole(int $unitId): ?UnitRole
    {
        $userId = auth()->id();

        if (self::$rolesFor !== $userId) {
            self::$roles = DB::table('unit_members')
                ->where('user_id', $userId)
                ->pluck('role', 'unit_id')
                ->map(fn ($r) => UnitRole::from($r))
                ->all();
            self::$rolesFor = $userId;
        }

        return self::$roles[$unitId] ?? null;
    }

    public static function canView(?int $unitId): bool
    {
        return $unitId !== null && in_array($unitId, CurrentOrganization::unitIds(), true);
    }

    /** Catat peminjaman / pengembalian. */
    public static function canOperate(?int $unitId): bool
    {
        if ($unitId === null || ! self::canView($unitId)) {
            return false;
        }

        return self::managesOrganization()
            || in_array(self::unitRole($unitId), [UnitRole::Admin, UnitRole::Staff], true);
    }

    /** Kelola barang, stok, data master unit, pengajuan. */
    public static function canManage(?int $unitId): bool
    {
        if ($unitId === null || ! self::canView($unitId)) {
            return false;
        }

        return self::managesOrganization() || self::unitRole($unitId) === UnitRole::Admin;
    }

    /** @return int[] unit yang boleh dikelola user */
    public static function manageableUnitIds(): array
    {
        return array_values(array_filter(CurrentOrganization::unitIds(), fn ($id) => self::canManage($id)));
    }

    /** @return int[] unit tempat user boleh mencatat peminjaman */
    public static function operableUnitIds(): array
    {
        return array_values(array_filter(CurrentOrganization::unitIds(), fn ($id) => self::canOperate($id)));
    }

    public static function authorizeView(?int $unitId): void
    {
        abort_unless(self::canView($unitId), 404);
    }

    public static function authorizeOperate(?int $unitId): void
    {
        abort_unless(self::canOperate($unitId), 403, 'Anda tidak punya akses untuk mencatat transaksi di unit ini.');
    }

    public static function authorizeManage(?int $unitId): void
    {
        abort_unless(self::canManage($unitId), 403, 'Anda tidak punya akses untuk mengelola data unit ini.');
    }

    public static function authorizeOrganization(): void
    {
        abort_unless(self::managesOrganization(), 403, 'Hanya admin organisasi yang dapat mengakses halaman ini.');
    }

    /** Reset cache (untuk test). */
    public static function flush(): void
    {
        self::$roles = null;
        self::$rolesFor = null;
    }
}
