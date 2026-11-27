<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPlatformAdmin();
    }

    public function rules(): array
    {
        return [
            'fullname' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'username' => ['nullable', 'string', 'min:4', 'max:50', 'regex:/^[a-z0-9._]+$/', 'unique:users,username'],
        ];
    }

    public function attributes(): array
    {
        return ['fullname' => 'nama', 'email' => 'email', 'username' => 'username'];
    }
}
