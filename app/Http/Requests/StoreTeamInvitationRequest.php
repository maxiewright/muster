<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()?->organization_id !== null
            && auth()->user()?->canInviteMembers(auth()->user()?->activeUnit());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(array_map(fn (Role $role) => $role->value, Role::cases()))],
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where('organization_id', $this->user()?->organization_id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Invite email is required.',
            'role.required' => 'Role is required for an invitation.',
            'unit_id.required' => 'Choose the unit this invitation should join.',
        ];
    }
}
