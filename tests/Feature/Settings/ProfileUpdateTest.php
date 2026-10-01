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
            ->set('emergency_phone', '0812-1111-2222')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('+62 812 3456 7890', $user->phone);
        $this->assertEquals('0812-1111-2222', $user->emergency_phone);
        $this->assertNull($user->email_verified_at);
    }

    public function test_phone_numbers_must_use_phone_number_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('phone', 'contact-me')
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

    public function test_non_student_profiles_do_not_update_emergency_phone(): void
    {
        $user = User::factory()->dpl()->create();

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('phone', '+62 812 3456 7890')
            ->set('emergency_phone', '0812-1111-2222')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame(UserRole::Dpl, $user->role);
        $this->assertSame('+62 812 3456 7890', $user->phone);
        $this->assertNull($user->emergency_phone);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.delete-user-modal')
            ->set('password', 'password')
            ->call('deleteUser');

        $response
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertFalse(auth()->check());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.delete-user-modal')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $response->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
    }
}
