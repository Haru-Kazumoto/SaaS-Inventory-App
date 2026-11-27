<?php

namespace App\Domain\MasterData\Actions;

use App\Domain\MasterData\MasterDataType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Hapus data master, ditolak bila masih dipakai (mis. kategori masih dipakai barang). */
final class DeleteMasterData
{
    public function handle(MasterDataType $type, Model $record): void
    {
        foreach ($type->usages() as $table => $column) {
            $query = DB::table($table)->where($column, $record->getKey());
            if ($table === 'products') {
                $query->whereNull('deleted_at');
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'delete' => "{$type->label()} \"{$record->name}\" masih dipakai, tidak bisa dihapus.",
                ]);
            }
        }

        $record->delete();
    }
}
