<?php

namespace App\Domain\Platform\Data;

final readonly class PlanData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public int $priceMonthly,
        public int $priceYearly,
        public ?int $maxUnits,
        public ?int $maxUsers,
        public ?int $maxProducts,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'price_monthly' => $this->priceMonthly,
            'price_yearly' => $this->priceYearly,
            'max_units' => $this->maxUnits,
            'max_users' => $this->maxUsers,
            'max_products' => $this->maxProducts,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];
    }
}
