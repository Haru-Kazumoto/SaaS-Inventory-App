<?php

namespace App\Http\Requests\Inventory;

use App\Domain\Inventory\Enums\StockCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Barang masuk / penghapusan untuk satu barang. */
class StockMoveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'condition' => ['nullable', Rule::in(['GOOD', 'DAMAGED'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['warehouse_id' => 'gudang', 'quantity' => 'jumlah', 'condition' => 'kondisi', 'reason' => 'alasan'];
    }

    public function condition(): StockCondition
    {
        return StockCondition::tryFrom((string) $this->input('condition')) ?? StockCondition::Good;
    }
}
