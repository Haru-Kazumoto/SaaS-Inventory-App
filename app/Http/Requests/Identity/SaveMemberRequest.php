<?php

namespace App\Http\Requests\Identity;

use App\Domain\Identity\Data\MemberData;
use App\Domain\Organization\Enums\OrganizationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dicek di Action
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => strtolower(preg_replace('/\s+/', '', (string) $this->input('username'))),
            'email' => $this->filled('email') ? strtolower(trim($this->input('email'))) : null,
        ]);
    }

    public function rules(): array
    {
        $memberId = $this->route('member')?->id;

        return [
            'fullname' => ['required', 'string', 'min:3', 'max:150'],
            'username' => ['required', 'string', 'min:4', 'max:50', 'regex:/^[a-z0-9._]+$/', Rule::unique('users', 'username')->ignore($memberId)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($memberId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$memberId ? 'nullable' : 'required', 'string', 'min:8'],
            'organization_role' => ['required', Rule::enum(OrganizationRole::class)],
            'memberships' => ['array'],
            'memberships.*.unit_id' => ['required', 'integer'],
            'memberships.*.role' => ['required', Rule::in(['admin', 'staff', 'viewer'])],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'fullname' => 'nama',
            'organization_role' => 'peran',
            'memberships' => 'unit',
            'avatar' => 'foto',
        ];
    }

    public function messages(): array
    {
        return ['username.regex' => 'Username hanya huruf kecil, angka, titik, atau garis bawah.'];
    }

    public function toData(): MemberData
    {
        return new MemberData(
            fullname: trim($this->input('fullname')),
            username: $this->input('username'),
            email: $this->input('email'),
            phone: $this->input('phone') ?: null,
            password: $this->input('password') ?: null,
            organizationRole: OrganizationRole::from($this->input('organization_role')),
            memberships: $this->input('memberships', []),
            avatar: $this->file('avatar'),
        );
    }
}
