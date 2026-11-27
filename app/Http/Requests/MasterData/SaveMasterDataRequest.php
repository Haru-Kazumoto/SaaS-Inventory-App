<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class SaveMasterDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // hak akses per unit dicek di Action
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_\-]+$/'],
            'description' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['nullable', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama', 'code' => 'kode', 'description' => 'deskripsi', 'unit_id' => 'pemilik'];
    }

    public function messages(): array
    {
        return ['code.regex' => 'Kode hanya boleh huruf kapital, angka, "_" atau "-".'];
    }

    public function payload(): array
    {
        return [
            'name' => $this->input('name'),
            'code' => $this->input('code'),
            'description' => $this->input('description'),
            'unit_id' => $this->filled('unit_id') ? $this->integer('unit_id') : null,
        ];
    }
}
