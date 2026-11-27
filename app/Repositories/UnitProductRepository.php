<?php

namespace App\Repositories;

use App\Dto\UnitProduct\CreateUnitProductDto;
use App\Dto\UnitProduct\UpdateUnitProductDto;
use App\Models\UnitProduct;
use App\Repositories\Interfaces\UnitProductRepositoryInterface;

class UnitProductRepository implements UnitProductRepositoryInterface
{
    public function getAll(int $major_id)
    {
        return UnitProduct::where('major_id', $major_id)->get();
    }

    public function getById(int $id)
    {
        return UnitProduct::find($id);
    }

    public function getByCode(string $code)
    {
        return UnitProduct::where('code',$code)->first();
    }

    public function store(CreateUnitProductDto $data)
    {
        return UnitProduct::create($data->toArray());
    }

    public function update(UpdateUnitProductDto $data, int $id)
    {
        $unit_product = $this->getById($id);

        return $unit_product->update($data->toArray());
    }

    public function delete(int $id)
    {
        $unit_product = $this->getById($id);

        return $unit_product->delete();
    }
}
