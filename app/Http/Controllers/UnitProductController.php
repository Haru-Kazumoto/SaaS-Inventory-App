<?php

namespace App\Http\Controllers;

use App\Dto\UnitProduct\CreateUnitProductDto;
use App\Dto\UnitProduct\UpdateUnitProductDto;
use App\Http\Requests\CreateUnitProductRequest;
use App\Http\Requests\UpdateUnitProductRequest;
use App\Models\UnitProduct;
use App\Services\UnitProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UnitProductController extends Controller
{
    public function __construct(
        protected UnitProductService $unitProductService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $major = $user?->major;
        $current_major = $major;

        return Inertia::render("UnitProduct/Index", [
            'datas' => $this->unitProductService->getAll($current_major->id)
        ]);
    }

    public function store(CreateUnitProductRequest $request)
    {
        $data = $request->validated();

        $this->unitProductService->store(new CreateUnitProductDto(
            name: $data['name'],
            code: $data['code'],
            description: $data['description'],
            major_id: Auth::user()?->major->id
        ));

        return back()->with('success','Berhasil membuat data');
    }

    public function update(UpdateUnitProductRequest $request, UnitProduct $unitProduct)
    {
        $data = $request->validated();

        $this->unitProductService->update(new UpdateUnitProductDto(...$data), $unitProduct->id);

        return back()->with('success','Berhasil memperbarui');
    }

    public function delete(UnitProduct $unitProduct)
    {
        $this->unitProductService->delete($unitProduct->id);

        return back()->with('success','Berhasil menghapus data');
    }
}
