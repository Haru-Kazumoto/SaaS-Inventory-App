<?php

namespace App\Http\Requests\Borrowing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReturnBorrowingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.borrow_line_id' => ['required', 'integer'],
            'lines.*.quantity' => ['required', 'integer', 'min:0'],
            'lines.*.condition' => ['nullable', Rule::in(['GOOD', 'DAMAGED'])],
            'lines.*.warehouse_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['lines' => 'barang', 'lines.*.quantity' => 'jumlah', 'lines.*.condition' => 'kondisi'];
    }
}
