<?php

namespace App\Http\Requests\Platform;

use App\Domain\Platform\Enums\BillingCycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPlatformAdmin();
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'issue_invoice' => ['boolean'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:90'],
        ];
    }

    public function attributes(): array
    {
        return ['plan_id' => 'paket', 'billing_cycle' => 'siklus tagihan', 'trial_days' => 'masa coba'];
    }
}
