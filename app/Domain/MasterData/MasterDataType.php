<?php

namespace App\Domain\MasterData;

use App\Domain\MasterData\Models\Category;
use App\Domain\MasterData\Models\Uom;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Models\Unit;
use Illuminate\Database\Eloquent\Model;

/**
 * Jenis data master yang dikelola lewat satu halaman generik (MasterDataPage).
 * `units` = unit organisasi (hanya admin organisasi); lainnya bisa milik unit atau bersama.
 */
enum MasterDataType: string
{
    case Units = 'units';
    case Warehouses = 'warehouses';
    case Categories = 'categories';
    case Uoms = 'uoms';

    /** @return class-string<Model> */
    public function model(): string
    {
        return match ($this) {
            self::Units => Unit::class,
            self::Warehouses => Warehouse::class,
            self::Categories => Category::class,
            self::Uoms => Uom::class,
        };
    }

    /** Data per unit (punya kolom unit_id). Unit organisasi sendiri tidak. */
    public function belongsToUnit(): bool
    {
        return $this !== self::Units;
    }

    /**
     * Tabel & kolom yang mereferensikan data ini (untuk cegah hapus data yang masih dipakai).
     *
     * @return array<string, string> table => column
     */
    public function usages(): array
    {
        return match ($this) {
            self::Units => ['products' => 'unit_id', 'unit_members' => 'unit_id', 'warehouses' => 'unit_id'],
            self::Warehouses => ['product_logs' => 'warehouse_id'],
            self::Categories => ['products' => 'category_id'],
            self::Uoms => ['products' => 'uom_id'],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Units => 'Unit',
            self::Warehouses => 'Penyimpanan',
            self::Categories => 'Kategori',
            self::Uoms => 'Satuan',
        };
    }
}
