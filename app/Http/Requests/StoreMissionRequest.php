<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->canManageMissions(auth()->user()?->activeUnit());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('missions', 'name')->where('unit_id', $this->user()?->activeUnitId()),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'mission_commander_user_id' => [
                'required',
                'integer',
                Rule::exists('unit_memberships', 'user_id')->where('unit_id', $this->user()?->activeUnitId()),
            ],
            'roster_user_ids' => ['nullable', 'array'],
            'roster_user_ids.*' => [
                'integer',
                Rule::exists('unit_memberships', 'user_id')->where('unit_id', $this->user()?->activeUnitId()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Mission name is required.',
            'name.unique' => 'A mission with this name already exists in the active unit.',
            'mission_commander_user_id.required' => 'Choose the mission commander.',
        ];
    }
}
