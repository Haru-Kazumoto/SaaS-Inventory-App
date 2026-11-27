<?php

namespace App\Http\Requests\Platform;

use App\Domain\Platform\Data\OrganizationData;
use App\Domain\Platform\Enums\BillingCycle;
use App\Domain\Organization\Enums\OrganizationTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPlatformAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'template' => ['required', Rule::enum(OrganizationTemplate::class)],
            'owner_name' => ['nullable', 'string', 'max:150'],
            'owner_email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            // hanya saat membuat
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'billing_cycle' => ['nullable', Rule::enum(BillingCycle::class)],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:90'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama organisasi',
            'owner_name' => 'nama pemilik',
            'owner_email' => 'email pemilik',
            'plan_id' => 'paket',
            'trial_days' => 'masa coba',
        ];
    }

    public function toData(): OrganizationData
    {
        return new OrganizationData(
            name: $this->string('name')->trim()->toString(),
            template: OrganizationTemplate::from($this->input('template')),
            ownerName: $this->input('owner_name'),
            ownerEmail: $this->input('owner_email'),
            phone: $this->input('phone'),
            address: $this->input('address'),
            notes: $this->input('notes'),
            planId: $this->integer('plan_id') ?: null,
            billingCycle: BillingCycle::tryFrom((string) $this->input('billing_cycle')) ?? BillingCycle::Monthly,
            trialDays: $this->integer('trial_days'),
        );
    }
}
