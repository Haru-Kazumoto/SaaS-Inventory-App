<?php

namespace App\Http\Requests\Platform;

use App\Domain\Organization\Enums\OrganizationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeOrganizationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPlatformAdmin();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrganizationStatus::class)->except([OrganizationStatus::Trial])],
            'reason' => [
                Rule::requiredIf(fn () => in_array($this->input('status'), ['suspended', 'cancelled'], true)),
                'nullable', 'string', 'max:500',
            ],
        ];
    }

    public function attributes(): array
    {
        return ['reason' => 'alasan'];
    }

    public function status(): OrganizationStatus
    {
        return OrganizationStatus::from($this->input('status'));
    }
}
