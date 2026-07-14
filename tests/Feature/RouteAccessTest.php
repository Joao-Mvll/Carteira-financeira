<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;

class RouteAccessTest extends TestCase
{
    public function test_guests_are_redirected_from_the_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_guests_are_redirected_from_deposit_transfer_and_statement(): void
    {
        $this->get('/deposit')->assertRedirect('/login');
        $this->get('/transfer')->assertRedirect('/login');
        $this->get('/statement')->assertRedirect('/login');
    }

    public function test_guests_can_see_login_and_register(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_authenticated_users_are_redirected_away_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect();
    }

    public function test_authenticated_users_can_open_the_dashboard(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_authenticated_users_can_open_deposit_and_transfer_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/deposit')->assertOk();
        $this->actingAs($user)->get('/transfer')->assertOk();
    }

    public function test_authenticated_users_can_open_their_statement(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get('/statement')->assertOk();
    }
}
