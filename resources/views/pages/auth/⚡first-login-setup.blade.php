<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Aktivasi Akun')] class extends Component {
    use PasswordValidationRules, ProfileValidationRules;

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $phone = null;

    public ?string $emergency_name = null;

    public ?string $emergency_phone = null;

    public ?string $emergency_relationship = null;

    public ?string $emergency_relationship_type = null;

    public ?string $custom_emergency_relationship = null;

    public bool $requiresRealEmail = false;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->must_change_password) {
            $this->redirect(route('dashboard', absolute: false), navigate: true);

            return;
        }

        $this->requiresRealEmail = $user->hasPlaceholderEmail();
        $this->email = $this->requiresRealEmail ? '' : $user->email;
        $this->phone = $user->phone;

        if ($user->hasRole(UserRole::Mahasiswa)) {
            $this->emergency_name = $user->emergency_name;
            $this->emergency_phone = $user->emergency_phone;
            $this->emergency_relationship = $user->emergency_relationship;

            if (in_array($this->emergency_relationship, User::EMERGENCY_RELATIONSHIPS, true)) {
                $this->emergency_relationship_type = $this->emergency_relationship;
            } elseif ($this->emergency_relationship) {
                $this->emergency_relationship_type = 'Lainnya';
                $this->custom_emergency_relationship = $this->emergency_relationship;
            }
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

    public function completeSetup(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login', absolute: false), navigate: true);

            return;
        }

        if ($user->hasRole(UserRole::Mahasiswa)) {
            if ($this->emergency_relationship_type === 'Lainnya') {
                $this->emergency_relationship = trim($this->custom_emergency_relationship ?? '') ?: null;
            } elseif ($this->emergency_relationship_type) {
                $this->emergency_relationship = $this->emergency_relationship_type;
            }
        }

        $emailRules = $this->emailRules($user->id);

        if ($this->requiresRealEmail) {
            $emailRules[] = function (string $attribute, mixed $value, Closure $fail): void {
                if (User::isPlaceholderEmail((string) $value)) {
                    $fail(__('Gunakan alamat email aktif pribadi Anda, bukan email bawaan sistem.'));
                }
            };
        }

        $rules = [
            'email' => $emailRules,
            'password' => $this->passwordRules(),
            'phone' => $this->phoneRules(),
        ];

        if ($user->hasRole(UserRole::Mahasiswa)) {
            $rules['emergency_name'] = $this->emergencyNameRules();
            $rules['emergency_relationship'] = $this->emergencyRelationshipRules();
            $rules['emergency_phone'] = $this->emergencyPhoneRules();
        }

        $validated = $this->validate($rules);

        $normalizedEmail = Str::lower(trim($validated['email']));
        $emailChanged = $normalizedEmail !== Str::lower($user->email);

        $updateData = [
            'email' => $normalizedEmail,
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?: null,
            'must_change_password' => false,
        ];

        if ($emailChanged) {
            $updateData['email_verified_at'] = null;
        }

        if ($user->hasRole(UserRole::Mahasiswa)) {
            $updateData['emergency_name'] = $validated['emergency_name'] ?: null;
            $updateData['emergency_relationship'] = $validated['emergency_relationship'] ?: null;
            $updateData['emergency_phone'] = $validated['emergency_phone'] ?: null;
        }

        $user->forceFill($updateData)->save();

        Flux::toast(variant: 'success', text: __('Password dan profil akun berhasil disimpan.'));

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Autentikasi Awal')"
        :description="$requiresRealEmail
            ? __('Buat password baru dan masukkan email aktif Anda sebelum melanjutkan.')
            : __('Buat password baru yang aman sebelum mulai menggunakan SIKKN.')"
    />

    @php($currentUser = auth()->user())
    @if ($currentUser)
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700/70 dark:bg-zinc-900/60">
            <div class="flex items-center justify-between gap-2">
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $currentUser->name }}</span>
                <flux:badge size="sm" :color="$currentUser->role->color()">{{ $currentUser->role->label() }}</flux:badge>
            </div>
            @if ($currentUser->nim || $currentUser->nip)
                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $currentUser->nim ? __('NIM') : __('NIP') }}: {{ $currentUser->nim ?: $currentUser->nip }}
                </div>
            @endif
        </div>
    @endif

    <form wire:submit="completeSetup" class="flex flex-col gap-5">
        <div>
            <flux:input
                wire:model="email"
                name="email"
                :label="$requiresRealEmail ? __('Email Aktif') : __('Email')"
                type="email"
                required
                autocomplete="email"
                :placeholder="__('nama@univ.ac.id')"
            />
            @if ($requiresRealEmail)
                <flux:text class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Email ini menggantikan email bawaan sistem dan dipakai untuk pemulihan password.') }}
                </flux:text>
            @endif
        </div>

        <flux:input
            wire:model="password"
            name="password"
            :label="__('Password Baru')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Buat password baru')"
            viewable
        />

        <flux:input
            wire:model="password_confirmation"
            name="password_confirmation"
            :label="__('Konfirmasi Password Baru')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Ulangi password baru')"
            viewable
        />

        <flux:separator variant="subtle" />

        <div class="space-y-4">
            <div>
                <flux:heading size="sm">{{ __('Informasi Kontak (Opsional)') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Boleh dilengkapi sekarang atau diperbarui nanti melalui menu Pengaturan.') }}
                </flux:text>
            </div>

            <x-phone-input
                wire:model="phone"
                name="phone"
                :label="__('Nomor WhatsApp / HP')"
                country-code="+62"
                placeholder="812 3456 7890"
            />

            @if ($currentUser?->hasRole(UserRole::Mahasiswa))
                <flux:input
                    wire:model="emergency_name"
                    name="emergency_name"
                    :label="__('Nama kontak darurat')"
                    placeholder="{{ __('Nama keluarga atau wali') }}"
                />

                <flux:select
                    wire:model.live="emergency_relationship_type"
                    name="emergency_relationship_type"
                    :label="__('Hubungan kontak darurat')"
                    placeholder="{{ __('Pilih hubungan...') }}"
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
                        placeholder="{{ __('Misal: Ibu Kos, Wali, Kerabat') }}"
                    />
                @endif

                <x-phone-input
                    wire:model="emergency_phone"
                    name="emergency_phone"
                    :label="__('Nomor telepon darurat')"
                    country-code="+62"
                    placeholder="812 3456 7890"
                />
            @endif
        </div>

        <flux:button type="submit" variant="primary" class="w-full" data-test="complete-setup-button">
            {{ __('Simpan & Lanjutkan') }}
        </flux:button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="text-center">
        @csrf
        <flux:button type="submit" variant="ghost" size="sm" class="text-zinc-500">
            {{ __('Keluar dari akun ini') }}
        </flux:button>
    </form>
</div>

