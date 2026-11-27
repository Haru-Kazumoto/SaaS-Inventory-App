<?php

namespace App\Domain\MasterData\Queries;

use App\Domain\MasterData\MasterDataType;
use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\CurrentOrganization;
use Illuminate\Database\Eloquent\Model;

final class MasterDataQuery
{
    /** Baris yang terlihat user: data bersama + data unit yang boleh diakses. */
    public function rows(MasterDataType $type): array
    {
        $query = $type->model()::query()->orderBy('name');

        if ($type->belongsToUnit()) {
            $query->with('unit:id,name')
                ->where(fn ($q) => $q->whereNull('unit_id')->orWhereIn('unit_id', CurrentOrganization::unitIds() ?: [0]));
        } else {
            $query->whereIn('id', CurrentOrganization::unitIds() ?: [0])->withCount('members');
        }

        return $query->get()->map(fn (Model $r) => [
            'id' => $r->id,
            'name' => $r->name,
            'code' => $r->code,
            'description' => $r->description,
            'unit_id' => $type->belongsToUnit() ? $r->unit_id : null,
            'unit_name' => $type->belongsToUnit() ? $r->unit?->name : null,
            'members_count' => $type->belongsToUnit() ? null : $r->members_count,
            'can_edit' => $this->canEdit($type, $r),
        ])->all();
    }

    public function canEdit(MasterDataType $type, Model $record): bool
    {
        if (! $type->belongsToUnit()) {
            return Access::managesOrganization();
        }

        return $record->unit_id === null ? Access::managesOrganization() : Access::canManage($record->unit_id);
    }

    /** Pilihan pemilik data saat menambah: "bersama" (admin organisasi) + unit yang dikelola. */
    public function ownerOptions(MasterDataType $type): array
    {
        if (! $type->belongsToUnit()) {
            return [];
        }

        $units = CurrentOrganization::units()->whereIn('id', Access::manageableUnitIds());
        $options = $units->map(fn ($u) => ['label' => $u->name, 'value' => $u->id])->values()->all();

        if (Access::managesOrganization()) {
            array_unshift($options, ['label' => 'Bersama (seluruh organisasi)', 'value' => null]);
        }

        return $options;
    }
}
