<?php

namespace App\Services;

use App\Dto\Borrower\CreateBorrowerDto;
use App\Dto\Borrower\UpdateBorrowerDto;
use App\Repositories\Interfaces\BorrowerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BorrowerService
{
    public function __construct(
        protected BorrowerRepositoryInterface $borrowerRepository
    ){}

    public function getById(int $id) 
    {
        return $this->borrowerRepository->getById($id);
    }

    public function store(CreateBorrowerDto $data)
    {
        return DB::transaction(function() use ($data) {
            return $this->borrowerRepository->store($data);
        });
    }

    public function update(int $id, UpdateBorrowerDto $data)
    {
        return DB::transaction(function() use ($id, $data) {
            return $this->borrowerRepository->update($id, $data);
        });
    }
}