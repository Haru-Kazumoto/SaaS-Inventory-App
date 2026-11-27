<?php

namespace App\Http\Controllers;

use App\Dto\SubTypeProduct\CreateSubTypeProductDto;
use App\Dto\SubTypeProduct\UpdateSubTypeProductDto;
use App\Http\Requests\CreateSubTypeProduct;
use App\Http\Requests\UpdateSubTypeProduct;
use App\Models\SubTypeProduct;
use App\Services\SubTypeProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubTypeProductController extends Controller
{
    public function __construct(
        protected SubTypeProductService $subTypeProductService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $current_major = $user?->major;

        return Inertia::render("SubTypeProduct/Index", [
            'datas' => $this->subTypeProductService->getAll($current_major?->id)
        ]);
    }

    public function store(CreateSubTypeProduct $request)
    {
        $data = $request->validated();
        $current_user = Auth::user();

        $fromDto = new CreateSubTypeProductDto(
            name: $data['name'],
            code: $data['code'],
            description: $data['description'],
            major_id: $current_user->major->id
        );

        $this->subTypeProductService->store($fromDto);

        return back()->with('success', 'Berhasil membuat sub tipe Barang!');
    }

    public function update(UpdateSubTypeProduct $request, SubTypeProduct $subTypeProduct)
    {
        $data = $request->validated();

        $fromDto = new UpdateSubTypeProductDto(...$data);
        $this->subTypeProductService->update($fromDto, $subTypeProduct->id);

        return back()->with('success', 'Berhasil memperbarui sub tipe Barang!');
    }

    public function delete(SubTypeProduct $subTypeProduct)
    {
        $this->subTypeProductService->delete($subTypeProduct->id);

        return back()->with('success', 'Berhasil menghapus sub tipe Barang!');
    }
}
