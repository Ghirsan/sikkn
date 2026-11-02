<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'phone' => $this->phoneRules(),
            'emergency_name' => $this->emergencyNameRules(),
            'emergency_relationship' => $this->emergencyRelationshipRules(),
            'emergency_phone' => $this->emergencyPhoneRules(),
        ];
    }

    /**
     * Get the mandatory emergency contact validation rules for student profile updates.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function studentEmergencyRules(): array
    {
        return [
            'emergency_name' => ['required', 'string', 'max:255'],
            'emergency_relationship' => ['required', 'string', 'max:100'],
            'emergency_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]+$/'],
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate WhatsApp phone numbers.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function phoneRules(): array
    {
        return ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]+$/'];
    }

    /**
     * Get the validation rules used to validate emergency contact name.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emergencyNameRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate emergency contact relationship.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emergencyRelationshipRules(): array
    {
        return ['nullable', 'string', 'max:100'];
    }

    /**
     * Get the validation rules used to validate emergency contact phone numbers.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emergencyPhoneRules(): array
    {
        return ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]+$/'];
    }
}
