<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class SubmitRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer'],
            'item_name' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'integer', 'min:1'],
            'uom' => ['required', 'string', 'max:50'],
            'reference_url' => ['nullable', 'string', 'max:500'],
            'reason' => ['required', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'unit_id' => 'unit',
            'item_name' => 'nama barang',
            'quantity' => 'jumlah',
            'uom' => 'satuan',
            'reference_url' => 'link toko',
            'reason' => 'alasan',
            'image' => 'gambar',
        ];
    }

    public function payload(): array
    {
        return [
            'unit_id' => $this->integer('unit_id'),
            'item_name' => strtoupper(trim($this->input('item_name'))),
            'quantity' => $this->integer('quantity'),
            'uom' => strtoupper(trim($this->input('uom'))),
            'reference_url' => $this->input('reference_url') ?: null,
            'reason' => $this->input('reason'),
        ];
    }
}
