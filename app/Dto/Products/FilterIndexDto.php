<?php

namespace App\Dto\Products;

class FilterIndexDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public ?string $name,
        public ?string $code,
        public ?string $type
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type
        ];
    }
}
