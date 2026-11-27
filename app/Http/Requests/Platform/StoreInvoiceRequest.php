<?php

namespace App\Http\Requests\Platform;

use App\Domain\Platform\Data\InvoiceLineData;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPlatformAdmin();
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'tax' => ['nullable', 'integer', 'min:0'],
            'due_in_days' => ['required', 'integer', 'min:0', 'max:90'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'organization_id' => 'organisasi',
            'items' => 'item tagihan',
            'items.*.description' => 'deskripsi item',
            'items.*.quantity' => 'jumlah',
            'items.*.unit_price' => 'harga',
            'due_in_days' => 'jatuh tempo',
        ];
    }

    /** @return InvoiceLineData[] */
    public function lines(): array
    {
        return array_map(
            fn (array $i) => new InvoiceLineData($i['description'], (int) $i['quantity'], (int) $i['unit_price']),
            $this->input('items', []),
        );
    }
}
