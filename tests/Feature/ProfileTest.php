<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    private function userWithWallet(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        Wallet::factory()->create(['user_id' => $user->id]);

        return $user->refresh();
    }

    public function test_guest_is_redirected_from_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_user_can_view_profile_page(): void
    {
        $user = $this->userWithWallet();

        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_user_can_update_name_and_email(): void
    {
        $user = $this->userWithWallet();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Novo Nome',
            'email' => 'novo@example.com',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Novo Nome', $user->name);
        $this->assertSame('novo@example.com', $user->email);
    }

    public function test_email_must_be_unique_to_other_users(): void
    {
        $user = $this->userWithWallet();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $other->email,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertNotSame($other->email, $user->refresh()->email);
    }

    public function test_user_can_keep_their_own_email(): void
    {
        $user = $this->userWithWallet();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Só Mudou o Nome',
            'email' => $user->email,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertSame('Só Mudou o Nome', $user->refresh()->name);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = $this->userWithWallet(['password' => bcrypt('SenhaAtual1')]);

        $response = $this->actingAs($user)->patch('/profile/password', [
            'current_password' => 'errada',
            'password' => 'NovaSenha1',
            'password_confirmation' => 'NovaSenha1',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('SenhaAtual1', $user->refresh()->password));
    }

    public function test_password_change_rejects_weak_new_password(): void
    {
        $user = $this->userWithWallet(['password' => bcrypt('SenhaAtual1')]);

        // sem letra maiúscula
        $response = $this->actingAs($user)->patch('/profile/password', [
            'current_password' => 'SenhaAtual1',
            'password' => 'novasenha1',
            'password_confirmation' => 'novasenha1',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_can_change_password_and_login_with_it(): void
    {
        $user = $this->userWithWallet(['password' => bcrypt('SenhaAtual1')]);

        $response = $this->actingAs($user)->patch('/profile/password', [
            'current_password' => 'SenhaAtual1',
            'password' => 'NovaSenha1',
            'password_confirmation' => 'NovaSenha1',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('NovaSenha1', $user->refresh()->password));

        $this->post('/logout');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'NovaSenha1',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }
}
