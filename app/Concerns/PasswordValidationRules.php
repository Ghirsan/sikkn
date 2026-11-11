<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }

    /**
     * Get the validation rules used to validate optional admin-provisioned passwords.
     *
     * @return array<int, string>
     */
    protected function optionalDefaultPasswordRules(): array
    {
        return ['nullable', 'string', 'min:6'];
    }

    /**
     * Resolve the hashed password when creating or updating a user account.
     *
     * Returns null when updating without a new password so the existing hash is kept.
     */
    protected function resolvePasswordHash(?string $password, bool $isUpdating): ?string
    {
        if (filled($password)) {
            return Hash::make($password);
        }

        return $isUpdating ? null : Hash::make('password');
    }

    /**
     * Get the validation rules used to validate the current password.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
