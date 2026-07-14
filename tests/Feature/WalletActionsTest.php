<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DepositService;
use Tests\TestCase;

class WalletActionsTest extends TestCase
{
    /**
     * Cria um usuário já com carteira (e saldo opcional).
     */
    private function userWithWallet(float $balance = 0): User
    {
        $user = User::factory()->create();
        Wallet::factory()->withBalance($balance)->create(['user_id' => $user->id]);

        return $user->refresh();
    }

    // ----- Registro -----

    public function test_registering_creates_user_wallet_and_logs_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Maria',
            'email' => 'maria@example.com',
            'password' => 'Senha1234',
            'password_confirmation' => 'Senha1234',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'maria@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->wallet);
        $this->assertEquals(0, $user->wallet->balance);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Maria',
            'email' => 'maria@example.com',
            'password' => 'Senha1234',
            'password_confirmation' => 'diferente',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_registration_rejects_weak_password(): void
    {
        // sem letra maiúscula
        $response = $this->post('/register', [
            'name' => 'Maria',
            'email' => 'maria@example.com',
            'password' => 'senha1234',
            'password_confirmation' => 'senha1234',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    // ----- Login / Logout -----

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Senha1234')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Senha1234',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Senha1234')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'errada',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = $this->userWithWallet();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    // ----- Depósito -----

    public function test_deposit_increases_balance(): void
    {
        $user = $this->userWithWallet(100);

        $response = $this->actingAs($user)->post('/deposit', ['amount' => 50]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');
        $this->assertEquals(150.00, $user->wallet->fresh()->balance);
    }

    public function test_deposit_accepts_brazilian_formatted_amount(): void
    {
        $user = $this->userWithWallet(0);

        $response = $this->actingAs($user)->post('/deposit', ['amount' => '20.000,50']);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');
        $this->assertEquals(20000.50, $user->wallet->fresh()->balance);
    }

    public function test_deposit_accepts_comma_as_decimal_separator(): void
    {
        $user = $this->userWithWallet(0);

        $response = $this->actingAs($user)->post('/deposit', ['amount' => '10,5']);

        $response->assertRedirect('/dashboard');
        $this->assertEquals(10.50, $user->wallet->fresh()->balance);
    }

    public function test_deposit_rejects_non_positive_amount(): void
    {
        $user = $this->userWithWallet(100);

        $response = $this->actingAs($user)->post('/deposit', ['amount' => 0]);

        $response->assertSessionHasErrors('amount');
        $this->assertEquals(100.00, $user->wallet->fresh()->balance);
    }

    // ----- Transferência -----

    public function test_transfer_moves_funds_between_users(): void
    {
        $sender = $this->userWithWallet(200);
        $recipient = $this->userWithWallet(0);

        $response = $this->actingAs($sender)->post('/transfer', [
            'email' => $recipient->email,
            'amount' => 75,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertEquals(125.00, $sender->wallet->fresh()->balance);
        $this->assertEquals(75.00, $recipient->wallet->fresh()->balance);
    }

    public function test_transfer_accepts_brazilian_formatted_amount(): void
    {
        $sender = $this->userWithWallet(2000);
        $recipient = $this->userWithWallet(0);

        $response = $this->actingAs($sender)->post('/transfer', [
            'email' => $recipient->email,
            'amount' => '1.250,75',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertEquals(749.25, $sender->wallet->fresh()->balance);
        $this->assertEquals(1250.75, $recipient->wallet->fresh()->balance);
    }

    public function test_transfer_fails_with_insufficient_balance(): void
    {
        $sender = $this->userWithWallet(20);
        $recipient = $this->userWithWallet(0);

        $response = $this->actingAs($sender)->post('/transfer', [
            'email' => $recipient->email,
            'amount' => 100,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(20.00, $sender->wallet->fresh()->balance);
        $this->assertEquals(0.00, $recipient->wallet->fresh()->balance);
    }

    public function test_transfer_requires_an_existing_recipient(): void
    {
        $sender = $this->userWithWallet(100);

        $response = $this->actingAs($sender)->post('/transfer', [
            'email' => 'ninguem@example.com',
            'amount' => 10,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals(100.00, $sender->wallet->fresh()->balance);
    }

    public function test_user_cannot_transfer_to_themselves(): void
    {
        $sender = $this->userWithWallet(100);

        $response = $this->actingAs($sender)->post('/transfer', [
            'email' => $sender->email,
            'amount' => 10,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals(100.00, $sender->wallet->fresh()->balance);
    }

    // ----- Estorno -----

    public function test_user_can_reverse_a_transaction_involving_their_wallet(): void
    {
        $user = $this->userWithWallet(100);
        $deposit = (new DepositService)->execute($user->wallet, 50);

        $response = $this->actingAs($user)
            ->post("/transactions/{$deposit->id}/reverse");

        $response->assertSessionHas('success');
        $this->assertEquals(100.00, $user->wallet->fresh()->balance);
        $this->assertSame(TransactionStatus::Reversed, $deposit->fresh()->status);
    }

    public function test_user_cannot_reverse_a_transaction_from_another_wallet(): void
    {
        $owner = $this->userWithWallet(100);
        $stranger = $this->userWithWallet(0);

        $deposit = (new DepositService)->execute($owner->wallet, 50);

        $this->actingAs($stranger)
            ->post("/transactions/{$deposit->id}/reverse")
            ->assertForbidden();

        $this->assertSame(TransactionStatus::Completed, $deposit->fresh()->status);
    }
}
