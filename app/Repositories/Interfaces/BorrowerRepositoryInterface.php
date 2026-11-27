<?php

namespace App\Repositories\Interfaces;

use App\Dto\Borrower\CreateBorrowerDto;
use App\Dto\Borrower\UpdateBorrowerDto;

interface BorrowerRepositoryInterface
{
    public function store(CreateBorrowerDto $data);
    public function getById(int $id);
    public function update(int $id, UpdateBorrowerDto $data);
}