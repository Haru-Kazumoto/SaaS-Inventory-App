<?php

namespace App\Services;

use App\Dto\SubTypeProduct\CreateSubTypeProductDto;
use App\Dto\SubTypeProduct\UpdateSubTypeProductDto;
use App\Repositories\Interfaces\SubTypeProductRepositoryInterface;

class SubTypeProductService
{
    public function __construct(
        protected SubTypeProductRepositoryInterface $subTypeProductRepository
    ){}

    public function getAll(int $major_id)
    {
        return $this->subTypeProductRepository->getAll($major_id);
    }

    public function getById(int $id)
    {
        return $this->subTypeProductRepository->getById($id);
    }

    public function getByCode(string $code)
    {
        return $this->subTypeProductRepository->getByCode($code);
    }

    public function store(CreateSubTypeProductDto $data)
    {
        return $this->subTypeProductRepository->store($data);
    }

    public function update(UpdateSubTypeProductDto $data, int $id)
    {
        return $this->subTypeProductRepository->update($data, $id);
    }

    public function delete(int $id)
    {
        return $this->subTypeProductRepository->delete($id);
    }
}