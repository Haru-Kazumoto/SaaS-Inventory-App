<?php

namespace App\Dto\Borrower;

class CreateBorrowerDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $fullname,
        public ?string $supervisor_name,
        public string $phone,
        public string $room_name,
        public ?string $description,
        public int $major_id
    ) {}

    public function toArray(): array
    {
        return [
            'fullname' => $this->fullname,
            'supervisor_name' => $this->supervisor_name,
            'phone' => $this->phone,
            'room_name' => $this->room_name,
            'description' => $this->description,
            'major_id' => $this->major_id
        ];
    }
}
