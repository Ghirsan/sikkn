<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait IssuesTemporaryCredentials
{
    use PasswordValidationRules;

    public bool $showIssuedCredentialModal = false;

    /**
     * One-time credential payload displayed immediately after provisioning or resetting a password.
     *
     * @var array{heading: string, name: string, identifier: string, email: string, temporary_password: string}|null
     */
    public ?array $issuedCredential = null;

    /**
     * Show the one-time credential modal for a newly created or reset account.
     */
    protected function presentIssuedCredential(User $user, string $temporaryPassword, string $heading): void
    {
        $this->issuedCredential = [
            'heading' => $heading,
            'name' => $user->name,
            'identifier' => $user->nim ?: ($user->nip ?: $user->email),
            'email' => $user->email,
            'temporary_password' => $temporaryPassword,
        ];

        $this->showIssuedCredentialModal = true;
    }

    /**
     * Generate a fresh temporary password for an existing user and reveal it once.
     */
    protected function issueResetTemporaryPassword(User $user, string $heading): string
    {
        $temporaryPassword = $this->generateTemporaryPassword();

        $user->forceFill([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ])->save();

        $this->presentIssuedCredential($user, $temporaryPassword, $heading);

        return $temporaryPassword;
    }

    /**
     * Clear the one-time credential state when the modal is dismissed.
     */
    public function closeIssuedCredentialModal(): void
    {
        $this->showIssuedCredentialModal = false;
        $this->issuedCredential = null;
    }
}
