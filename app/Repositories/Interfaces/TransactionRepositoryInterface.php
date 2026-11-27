<?php

namespace App\Repositories\Interfaces;

use App\Dto\Transaction\CreateTransactionDto;
use App\Dto\Transaction\FilterIndexDto;

interface TransactionRepositoryInterface
{
    public function store(CreateTransactionDto $data);
    public function getAll(int $major_id, FilterIndexDto $filter);
    public function getComputedStatus(int $txId): ?string;
    public function getAllToday(int $major_id);
    public function getById(int $id);
    public function findById(int $id);
    public function updateHeader(int $id, array $fields);
}
