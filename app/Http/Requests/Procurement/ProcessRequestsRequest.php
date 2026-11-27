<?php

namespace App\Http\Requests\Procurement;

use App\Domain\Procurement\Enums\RequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dicek di Action (admin organisasi)
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'decision' => ['required', Rule::in(['APPROVED', 'REJECTED'])],
        ];
    }

    public function decision(): RequestStatus
    {
        return RequestStatus::from($this->input('decision'));
    }
}
