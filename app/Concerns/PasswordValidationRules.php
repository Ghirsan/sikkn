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
     * Generate a readable, unambiguous random temporary password.
     */
    protected function generateTemporaryPassword(int $length = 10): string
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';
        $digits = '23456789';
        $all = $uppercase.$lowercase.$digits;

        $chars = [
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
        ];

        for ($i = count($chars); $i < $length; $i++) {
            $chars[] = $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle using CSPRNG
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        return implode('', $chars);
    }

    /**
     * Resolve password hash, optional one-time plaintext password, and onboarding flag.
     *
     * @return array{hash: ?string, temporary_password: ?string, must_change_password: bool}
     */
    protected function resolveProvisionedPassword(?string $password, bool $isUpdating): array
    {
        if (filled($password)) {
            return [
                'hash' => Hash::make($password),
                'temporary_password' => null,
                'must_change_password' => true,
            ];
        }

        if ($isUpdating) {
            return [
                'hash' => null,
                'temporary_password' => null,
                'must_change_password' => false,
            ];
        }

        $temporaryPassword = $this->generateTemporaryPassword();

        return [
            'hash' => Hash::make($temporaryPassword),
            'temporary_password' => $temporaryPassword,
            'must_change_password' => true,
        ];
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
