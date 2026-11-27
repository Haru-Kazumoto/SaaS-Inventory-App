<?php

namespace App\Http\Requests\Platform;

use App\Domain\Platform\Data\PlanData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPlatformAdmin();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'alpha_dash', 'max:50', Rule::unique('plans', 'code')->ignore($this->route('plan'))],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'price_yearly' => ['required', 'integer', 'min:0'],
            'max_units' => ['nullable', 'integer', 'min:1'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_products' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'kode paket',
            'name' => 'nama paket',
            'price_monthly' => 'harga bulanan',
            'price_yearly' => 'harga tahunan',
            'max_units' => 'batas unit',
            'max_users' => 'batas pengguna',
            'max_products' => 'batas barang',
        ];
    }

    public function toData(): PlanData
    {
        $nullableInt = fn (string $key) => $this->filled($key) ? $this->integer($key) : null;

        return new PlanData(
            code: strtolower($this->input('code')),
            name: $this->input('name'),
            description: $this->input('description'),
            priceMonthly: $this->integer('price_monthly'),
            priceYearly: $this->integer('price_yearly'),
            maxUnits: $nullableInt('max_units'),
            maxUsers: $nullableInt('max_users'),
            maxProducts: $nullableInt('max_products'),
            isActive: $this->boolean('is_active', true),
            sortOrder: $this->integer('sort_order'),
        );
    }
}
