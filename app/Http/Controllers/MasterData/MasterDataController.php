<?php

namespace App\Http\Controllers\MasterData;

use App\Domain\MasterData\Actions\DeleteMasterData;
use App\Domain\MasterData\Actions\SaveMasterData;
use App\Domain\MasterData\MasterDataType;
use App\Domain\MasterData\Queries\MasterDataQuery;
use App\Domain\Organization\Support\Access;
use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\SaveMasterDataRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/** Satu controller untuk unit, gudang, kategori, satuan (halaman generik MasterDataPage). */
class MasterDataController extends Controller
{
    public function index(string $type, MasterDataQuery $query): Response
    {
        $type = $this->type($type);
        if ($type === MasterDataType::Units) {
            Access::authorizeOrganization();
        }

        return Inertia::render('MasterData/Index', [
            'type' => $type->value,
            'rows' => $query->rows($type),
            'ownerOptions' => $query->ownerOptions($type),
            'canCreate' => $type === MasterDataType::Units
                ? Access::managesOrganization()
                : $query->ownerOptions($type) !== [],
        ]);
    }

    public function store(string $type, SaveMasterDataRequest $request, SaveMasterData $save): RedirectResponse
    {
        $type = $this->type($type);
        if ($type === MasterDataType::Units) {
            Access::authorizeOrganization();
        }

        $record = $save->handle($type, $request->payload());

        return back()->with('success', "{$type->label()} {$record->name} ditambahkan");
    }

    public function update(string $type, int $id, SaveMasterDataRequest $request, SaveMasterData $save, MasterDataQuery $query): RedirectResponse
    {
        $type = $this->type($type);
        $record = $this->find($type, $id, $query);

        $save->handle($type, $request->payload(), $record);

        return back()->with('success', "{$type->label()} {$record->name} diperbarui");
    }

    public function destroy(string $type, int $id, DeleteMasterData $delete, MasterDataQuery $query): RedirectResponse
    {
        $type = $this->type($type);
        $record = $this->find($type, $id, $query);

        $delete->handle($type, $record);

        return back()->with('success', "{$type->label()} {$record->name} dihapus");
    }

    private function type(string $type): MasterDataType
    {
        return MasterDataType::tryFrom($type) ?? abort(404);
    }

    private function find(MasterDataType $type, int $id, MasterDataQuery $query): Model
    {
        $record = $type->model()::findOrFail($id); // global scope: hanya organisasi aktif
        abort_unless($query->canEdit($type, $record), 403, 'Anda tidak bisa mengubah data ini.');

        return $record;
    }
}
