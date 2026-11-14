<?php

namespace Tests\Feature\Settings;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('phone', '+62 812 3456 7890')
            ->set('emergency_name', 'Budi Santoso')
            ->set('emergency_relationship_type', 'Orang Tua')
            ->set('emergency_phone', '0812-1111-2222')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('+62 812 3456 7890', $user->phone);
        $this->assertEquals('Budi Santoso', $user->emergency_name);
        $this->assertEquals('Orang Tua', $user->emergency_relationship);
        $this->assertEquals('0812-1111-2222', $user->emergency_phone);
        $this->assertNull($user->email_verified_at);
    }

    public function test_student_can_set_custom_emergency_relationship(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('phone', '+62 812 3456 7890')
            ->set('emergency_name', 'Pak Ahmad')
            ->set('emergency_relationship_type', 'Lainnya')
            ->set('custom_emergency_relationship', 'Paman')
            ->set('emergency_phone', '0812-3333-4444')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Pak Ahmad', $user->emergency_name);
        $this->assertEquals('Paman', $user->emergency_relationship);
        $this->assertEquals('0812-3333-4444', $user->emergency_phone);
    }

    public function test_student_profile_requires_emergency_contact_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('emergency_name', '')
            ->set('emergency_relationship_type', null)
            ->set('custom_emergency_relationship', '')
            ->set('emergency_phone', '')
            ->call('updateProfileInformation')
            ->assertHasErrors(['emergency_name', 'emergency_relationship', 'emergency_phone']);
    }

    public function test_phone_numbers_must_use_phone_number_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('phone', 'contact-me')
            ->set('emergency_name', 'Budi Santoso')
            ->set('emergency_relationship_type', 'Orang Tua')
            ->set('emergency_phone', '0812 3456 7890')
            ->call('updateProfileInformation')
            ->assertHasErrors(['phone']);

        $this->assertNull($user->fresh()->phone);
    }

    public function test_whatsapp_url_keeps_only_digits_for_the_link(): void
    {
        $user = User::factory()->create(['phone' => '+62 (812) 3456-7890']);

        $this->assertSame('https://wa.me/6281234567890', $user->whatsappUrl());
        $this->assertSame('https://wa.me/6281234567890', $user->whatsappUrl('0812 3456 7890'));
    }

    public function test_non_student_profiles_do_not_update_emergency_contact(): void
    {
        $user = User::factory()->dpl()->create();

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('phone', '+62 812 3456 7890')
            ->set('emergency_name', 'Budi Santoso')
            ->set('emergency_relationship_type', 'Orang Tua')
            ->set('emergency_phone', '0812-1111-2222')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame(UserRole::Dpl, $user->role);
        $this->assertSame('+62 812 3456 7890', $user->phone);
        $this->assertNull($user->emergency_phone);
        $this->assertNull($user->emergency_name);
        $this->assertNull($user->emergency_relationship);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create([
            'emergency_name' => 'Budi Santoso',
            'emergency_relationship' => 'Orang Tua',
            'emergency_phone' => '0812-1111-2222',
        ]);

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_self_service_account_deletion_is_not_available_in_profile_settings(): void
    {
        $user = User::factory()->create([
            'emergency_name' => 'Budi Santoso',
            'emergency_relationship' => 'Orang Tua',
            'emergency_phone' => '0812-1111-2222',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Delete account')
            ->assertDontSee('Hapus Akun');
    }
}
