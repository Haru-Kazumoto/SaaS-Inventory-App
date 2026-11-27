<?php

namespace App\Http\Requests\Platform;

use App\Domain\Platform\Data\PaymentData;
use App\Domain\Platform\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPlatformAdmin();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'paid_at' => ['required', 'date', 'before_or_equal:now'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'jumlah',
            'method' => 'metode',
            'paid_at' => 'tanggal bayar',
            'reference' => 'no. referensi',
        ];
    }

    public function toData(): PaymentData
    {
        return new PaymentData(
            amount: $this->integer('amount'),
            method: PaymentMethod::from($this->input('method')),
            paidAt: Carbon::parse($this->input('paid_at')),
            reference: $this->input('reference'),
            notes: $this->input('notes'),
        );
    }
}
