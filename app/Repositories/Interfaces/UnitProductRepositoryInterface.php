<?php

namespace App\Repositories\Interfaces;

use App\Dto\UnitProduct\CreateUnitProductDto;
use App\Dto\UnitProduct\UpdateUnitProductDto;

interface UnitProductRepositoryInterface
{
    public function getAll(int $major_id);
    public function getByid(int $id);
    public function getByCode(string $code);
    public function store(CreateUnitProductDto $data);
    public function update(UpdateUnitProductDto $data, int $id);
    public function delete(int $id);
}