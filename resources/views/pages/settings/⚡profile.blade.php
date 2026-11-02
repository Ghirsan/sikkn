<?php

use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $emergency_phone = null;
    public ?string $emergency_name = null;
    public ?string $emergency_relationship = null;
    public ?string $emergency_relationship_type = null;
    public ?string $custom_emergency_relationship = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = Auth::user()->phone;
        $this->emergency_phone = Auth::user()->emergency_phone;
        $this->emergency_name = Auth::user()->emergency_name;
        $this->emergency_relationship = Auth::user()->emergency_relationship;

        if (in_array($this->emergency_relationship, \App\Models\User::EMERGENCY_RELATIONSHIPS, true)) {
            $this->emergency_relationship_type = $this->emergency_relationship;
            $this->custom_emergency_relationship = null;
        } elseif ($this->emergency_relationship) {
            $this->emergency_relationship_type = 'Lainnya';
            $this->custom_emergency_relationship = $this->emergency_relationship;
        } else {
            $this->emergency_relationship_type = null;
            $this->custom_emergency_relationship = null;
        }
    }

    public function updatedEmergencyRelationshipType(?string $value): void
    {
        if ($value !== 'Lainnya') {
            $this->emergency_relationship = $value;
            $this->custom_emergency_relationship = null;
        } else {
            $this->emergency_relationship = trim($this->custom_emergency_relationship ?? '') ?: null;
        }
    }

    public function updatedCustomEmergencyRelationship(?string $value): void
    {
        if ($this->emergency_relationship_type === 'Lainnya') {
            $this->emergency_relationship = trim($value ?? '') ?: null;
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        if ($user->hasRole(UserRole::Mahasiswa)) {
            if ($this->emergency_relationship_type === 'Lainnya') {
                $this->emergency_relationship = trim($this->custom_emergency_relationship ?? '') ?: null;
            } elseif ($this->emergency_relationship_type) {
                $this->emergency_relationship = $this->emergency_relationship_type;
            }
        }

        $rules = $this->profileRules($user->id);

        if ($user->hasRole(UserRole::Mahasiswa)) {
            $rules = array_merge($rules, $this->studentEmergencyRules());
        } else {
            unset($rules['emergency_phone'], $rules['emergency_name'], $rules['emergency_relationship']);
        }

        $validated = $this->validate($rules);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name, email address, and contact details')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                    </div>
                @endif
            </div>

            <x-phone-input
                wire:model="phone"
                name="phone"
                :label="__('Phone number')"
                country-code="+62"
                placeholder="812 3456 7890"
            />

            @if (Auth::user()->hasRole(UserRole::Mahasiswa))
                <flux:separator variant="subtle" />

                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Kontak Darurat') }}</flux:heading>
                        <flux:subheading size="sm">{{ __('Informasi keluarga atau wali yang dapat dihubungi saat situasi darurat di lokasi KKN.') }}</flux:subheading>
                    </div>

                    <flux:input
                        wire:model="emergency_name"
                        name="emergency_name"
                        :label="__('Nama kontak darurat')"
                        placeholder="{{ __('Nama lengkap kontak') }}"
                        required
                    />

                    <flux:select
                        wire:model.live="emergency_relationship_type"
                        name="emergency_relationship_type"
                        :label="__('Hubungan kontak darurat')"
                        placeholder="{{ __('Pilih hubungan...') }}"
                        required
                    >
                        @foreach (\App\Models\User::EMERGENCY_RELATIONSHIPS as $relation)
                            <flux:select.option value="{{ $relation }}">{{ $relation }}</flux:select.option>
                        @endforeach
                        <flux:select.option value="Lainnya">{{ __('Lainnya') }}</flux:select.option>
                    </flux:select>

                    @if ($emergency_relationship_type === 'Lainnya')
                        <flux:input
                            wire:model.live="custom_emergency_relationship"
                            name="custom_emergency_relationship"
                            :label="__('Sebutkan hubungan')"
                            placeholder="{{ __('Misal: Ibu Kos, Pacar, Teman') }}"
                            required
                        />
                    @endif

                    <x-phone-input
                        wire:model="emergency_phone"
                        name="emergency_phone"
                        :label="__('Nomor telepon darurat')"
                        country-code="+62"
                        placeholder="812 3456 7890"
                    />
                </div>
            @endif

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-profile-button">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
