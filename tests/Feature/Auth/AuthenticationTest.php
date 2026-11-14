<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrorsIn('email');

        $this->assertGuest();
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge(): void
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withTwoFactor()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_student_can_authenticate_using_nim(): void
    {
        $student = User::factory()->mahasiswa()->create([
            'nim' => '24060120120001',
            'email' => '24060120120001@student.sikkn.ac.id',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => '24060120120001',
            'password' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($student);
    }

    public function test_dpl_can_authenticate_using_nip(): void
    {
        $dpl = User::factory()->dpl()->create([
            'nip' => '198001012005011001',
            'email' => '198001012005011001@lecturer.sikkn.ac.id',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => '198001012005011001',
            'password' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($dpl);
    }

    public function test_user_requiring_first_login_setup_is_redirected_from_app_routes(): void
    {
        $student = User::factory()->mahasiswa()->mustChangePassword()->create([
            'nim' => '24060120120009',
            'email' => '24060120120009@student.sikkn.ac.id',
        ]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertRedirect(route('first-login.setup'));

        $this->actingAs($student)
            ->get(route('profile.edit'))
            ->assertRedirect(route('first-login.setup'));

        $this->actingAs($student)
            ->get(route('first-login.setup'))
            ->assertOk();
    }

    public function test_student_with_placeholder_email_must_set_real_email_and_new_password_on_first_login(): void
    {
        $student = User::factory()->mahasiswa()->mustChangePassword()->create([
            'nim' => '24060120120010',
            'email' => '24060120120010@student.sikkn.ac.id',
            'password' => Hash::make('TempPass123'),
        ]);

        $this->actingAs($student);

        Livewire::test('pages::auth.first-login-setup')
            ->set('email', '24060120120010@student.sikkn.ac.id')
            ->set('password', 'NewSecurePass123!')
            ->set('password_confirmation', 'NewSecurePass123!')
            ->call('completeSetup')
            ->assertHasErrors(['email']);

        Livewire::test('pages::auth.first-login-setup')
            ->set('email', 'mahasiswa.aktif@students.undip.ac.id')
            ->set('password', 'NewSecurePass123!')
            ->set('password_confirmation', 'NewSecurePass123!')
            ->set('phone', '081234567890')
            ->call('completeSetup')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $student->refresh();
        $this->assertFalse($student->must_change_password);
        $this->assertSame('mahasiswa.aktif@students.undip.ac.id', $student->email);
        $this->assertSame('081234567890', $student->phone);
        $this->assertTrue(Hash::check('NewSecurePass123!', $student->password));

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_onboarded_user_visiting_setup_account_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $this->actingAs($user)
            ->get(route('first-login.setup'))
            ->assertRedirect(route('dashboard', absolute: false));
    }
}
