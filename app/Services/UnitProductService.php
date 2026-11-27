<?php

namespace App\Services;

use App\Dto\UnitProduct\CreateUnitProductDto;
use App\Dto\UnitProduct\UpdateUnitProductDto;
use App\Repositories\UnitProductRepository;

class UnitProductService
{
    public function __construct(
        protected UnitProductRepository $unitProductRepository
    ) {}

    public function getAll(int $major_id)
    {
        return $this->unitProductRepository->getAll($major_id);
    }

    public function getByCode(string $code)
    {
        return $this->unitProductRepository->getByCode($code);
    }

    public function store(CreateUnitProductDto $data)
    {
        return $this->unitProductRepository->store($data);
    }

    public function update(UpdateUnitProductDto $data, int $id)
    {
        return $this->unitProductRepository->update($data, $id);
    }

    public function delete(int $id)
    {
        return $this->unitProductRepository->delete($id);
    }
}
