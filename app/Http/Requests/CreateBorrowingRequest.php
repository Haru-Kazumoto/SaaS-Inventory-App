<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBorrowingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'borrower_name'             => ['required', 'string'],
            'borrower_phone'            => ['required', 'string', 'numeric'],
            'borrower_room'             => ['required', 'string'],
            'borrower_supervisor'       => ['nullable', 'string'],
            'description'               => ['nullable', 'string'],
            'lines'                     => ['required', 'array'],
            'lines.*.product_id'        => ['required', 'integer', Rule::exists('products', 'id')],
            'lines.*.warehouse_id'      => ['required', 'integer', Rule::exists('warehouse', 'id')],
            'lines.*.quantity'          => ['required', 'integer', 'min:1'],
        ];
    }
}
