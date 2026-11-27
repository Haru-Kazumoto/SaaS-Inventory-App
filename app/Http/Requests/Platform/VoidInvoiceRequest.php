<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class VoidInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPlatformAdmin();
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:300']];
    }

    public function attributes(): array
    {
        return ['reason' => 'alasan pembatalan'];
    }
}
