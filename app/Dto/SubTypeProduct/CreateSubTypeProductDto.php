<?php

namespace App\Dto\SubTypeProduct;

class CreateSubTypeProductDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $code,
        public ?string $description,
        public int $major_id,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'major_id' => $this->major_id,
        ];
    }
}
