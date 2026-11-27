<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
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
            'name' => 'required|string',
            'brand_name' => 'required|string',
            'price' => 'required|numeric',
            'unit' => 'required|string',
            'partial_items' => 'required|array',
            'partial_items.*.labeling_date' => 'required|string',
            'partial_items.*.code_item' => 'required|string|unique:partial_item,code_item',
            'partial_items.*.condition_item' => 'required|string',
            'partial_items.*.item_image' => 'nullable|image',
            'partial_items.*.base_quantity' => 'nullable|numeric'
        ];
    }
}
