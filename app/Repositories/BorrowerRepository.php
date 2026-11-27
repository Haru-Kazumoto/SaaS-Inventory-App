<?php

namespace App\Repositories;

use App\Dto\Borrower\CreateBorrowerDto;
use App\Dto\Borrower\UpdateBorrowerDto;
use App\Models\Borrower;
use App\Repositories\Interfaces\BorrowerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BorrowerRepository implements BorrowerRepositoryInterface
{
    public function store(CreateBorrowerDto $data)
    {
        return DB::table("borrower")->insertGetId($data->toArray());
    }

    public function getById(int $id)
    {
        return Borrower::find($id);
    }

    public function update(int $id, UpdateBorrowerDto $data)
    {
        return DB::table("borrower")
            ->where('id', $id)
            ->update($data->toArray());
    }
}
