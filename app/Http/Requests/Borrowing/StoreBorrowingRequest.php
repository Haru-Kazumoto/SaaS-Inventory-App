<?php

namespace App\Http\Requests\Borrowing;

use App\Domain\Borrowing\Data\BorrowData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreBorrowingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer'],
            'borrower_name' => ['required', 'string', 'max:150'],
            'identity_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'responsible_name' => ['nullable', 'string', 'max:150'],
            'usage_location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'due_at' => ['nullable', 'date', 'after_or_equal:today'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer'],
            'lines.*.warehouse_id' => ['required', 'integer'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'unit_id' => 'unit',
            'borrower_name' => 'nama peminjam',
            'identity_number' => 'nomor identitas',
            'phone' => 'telepon',
            'responsible_name' => 'penanggung jawab',
            'usage_location' => 'lokasi pemakaian',
            'due_at' => 'jatuh tempo',
            'lines' => 'barang',
            'lines.*.quantity' => 'jumlah',
        ];
    }

    public function toData(): BorrowData
    {
        return new BorrowData(
            unitId: $this->integer('unit_id'),
            borrowerName: trim($this->input('borrower_name')),
            identityNumber: $this->input('identity_number') ?: null,
            phone: $this->input('phone') ?: null,
            responsibleName: $this->input('responsible_name') ?: null,
            usageLocation: $this->input('usage_location') ?: null,
            description: $this->input('description') ?: null,
            dueAt: $this->filled('due_at') ? Carbon::parse($this->input('due_at')) : null,
            lines: array_map(fn ($l) => [
                'product_id' => (int) $l['product_id'],
                'warehouse_id' => (int) $l['warehouse_id'],
                'quantity' => (int) $l['quantity'],
            ], $this->input('lines', [])),
        );
    }
}
