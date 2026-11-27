<?php

namespace App\Domain\MasterData\Actions;

use App\Domain\MasterData\MasterDataType;
use App\Domain\Organization\Support\Access;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class SaveMasterData
{
    /**
     * @param  array{name: string, code: string, description?: ?string, unit_id?: ?int}  $data
     */
    public function handle(MasterDataType $type, array $data, ?Model $record = null): Model
    {
        $attributes = [
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'] ?? null,
        ];

        if ($type->belongsToUnit()) {
            $unitId = $data['unit_id'] ?? null;

            // data bersama (unit_id null) hanya oleh admin organisasi; data unit oleh pengelola unit itu
            if ($unitId === null ? ! Access::managesOrganization() : ! Access::canManage($unitId)) {
                throw ValidationException::withMessages(['unit_id' => 'Anda tidak bisa menyimpan data untuk unit ini.']);
            }

            $attributes['unit_id'] = $unitId;
        }

        $modelClass = $type->model();
        $duplicate = $modelClass::query()
            ->where('code', $attributes['code'])
            ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['code' => 'Kode sudah dipakai di organisasi ini.']);
        }

        if ($record) {
            $record->update($attributes);

            return $record;
        }

        return $modelClass::create($attributes);
    }
}
