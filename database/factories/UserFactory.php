<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Mahasiswa,
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Set the user's role.
     */
    public function withRole(UserRole $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    /**
     * Create a Mahasiswa user.
     */
    public function mahasiswa(): static
    {
        return $this->withRole(UserRole::Mahasiswa);
    }

    /**
     * Create a DPL user.
     */
    public function dpl(): static
    {
        return $this->withRole(UserRole::Dpl);
    }

    /**
     * Create a P2KKN Admin user.
     */
    public function p2kkn(): static
    {
        return $this->withRole(UserRole::P2kkn);
    }

    /**
     * Create a Prodi user.
     */
    public function prodi(): static
    {
        return $this->withRole(UserRole::Prodi);
    }

    /**
     * Create a Fakultas user.
     */
    public function fakultas(): static
    {
        return $this->withRole(UserRole::Fakultas);
    }
}
