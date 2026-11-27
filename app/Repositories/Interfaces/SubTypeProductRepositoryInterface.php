<?php

namespace App\Repositories\Interfaces;

use App\Dto\SubTypeProduct\CreateSubTypeProductDto;
use App\Dto\SubTypeProduct\UpdateSubTypeProductDto;

interface SubTypeProductRepositoryInterface
{
    public function getAll(int $major_id);
    public function getById(int $id);
    public function getByCode(string $code);
    public function store(CreateSubTypeProductDto $data);
    public function update(UpdateSubTypeProductDto $data, int $id);
    public function delete(int $id);
}