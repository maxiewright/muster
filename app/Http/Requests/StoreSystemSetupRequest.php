<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreSystemSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! User::query()->where('is_platform_admin', true)->exists();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::default()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Administrator name is required.',
            'email.required' => 'Administrator email is required.',
            'password.required' => 'A password is required.',
        ];
    }
}
