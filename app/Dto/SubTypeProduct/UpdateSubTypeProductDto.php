<?php

namespace App\Dto\SubTypeProduct;

class UpdateSubTypeProductDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $code,
        public ?string $description,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
        ];
    }
}
