<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
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
            'name' => ['required','string','max:100'],
            'code' => ['required','string','max:100'],
            'brand_name' => ['required','string','max:100'],
            'type' => ['required','string'],
            'price' => ['required','string'],
            'labeling_date' => ['nullable','string'],
            // 'warehouse_code' => ['required','string',Rule::exists('warehouse','code')],
            'sub_type_code' => ['required','string',Rule::exists('sub_type_products', 'code')],
            'unit_code' => ['required','string',Rule::exists('unit_products','code')],
            'image_product' => ['nullable',Rule::imageFile()],
            'quantities' => ['required','array'],
            'quantities.*.status' => ['required','string'],
            'quantities.*.warehouse_id' => ['required',Rule::exists('warehouse', 'id')],
            'quantities.*.quantity' => ['required','string'],
        ];
    }

        public function messages(): array
        {
            return [
                'name.required' => 'Nama barang wajib diisi.',
                'code.required' => 'Kode barang wajib diisi.',
                'code.max' => 'Kode barang tidak boleh lebih dari 100 karakter.',
                'brand_name.required' => 'Merek barang wajib diisi.',
                'type.required' => 'Tipe barang wajib diisi.',
                'price.required' => 'Harga barang wajib diisi.',
                // 'warehouse_code.required' => 'Kode gudang wajib diisi.',
                'sub_type_code.required' => 'Kode subtipe barang wajib diisi.',
                'unit_code.required' => 'Kode satuan barang wajib diisi.',
                'image_product.image' => 'File yang diunggah harus berupa gambar.',
                'quantities.required' => 'Data kuantitas barang wajib diisi.',
                'quantities.*.status.required' => 'Status kondisi barang wajib diisi.',
                'quantities.*.warehouse_id.required' => 'ID gudang untuk kuantitas barang wajib diisi.',
                'quantities.*.quantity.required' => 'Jumlah kuantitas barang wajib diisi.',
            ];
        }
}
