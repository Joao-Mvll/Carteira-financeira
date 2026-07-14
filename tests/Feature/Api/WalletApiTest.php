<?php

namespace Tests\Feature\Api;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DepositService;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    private function userWithWallet(float $balance = 0): User
    {
        $user = User::factory()->create();
        Wallet::factory()->withBalance($balance)->create(['user_id' => $user->id]);

        return $user->refresh();
    }

    // ----- Auth por token -----

    public function test_register_returns_a_token_and_creates_a_wallet(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'senha1234',
            'password_confirmation' => 'senha1234',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $user = User::where('email', 'ana@example.com')->first();
        $this->assertNotNull($user->wallet);
    }

    public function test_login_returns_a_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('senha1234')]);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'senha1234'])
            ->assertOk()
            ->assertJsonStructure(['token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('senha1234')]);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'errada'])
            ->assertUnauthorized();
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->getJson('/api/wallet')->assertUnauthorized();
        $this->postJson('/api/deposit', ['amount' => 10])->assertUnauthorized();
    }

    // ----- Depósito -----

    public function test_deposit_increases_balance(): void
    {
        $user = $this->userWithWallet(100);
        Sanctum::actingAs($user);

        $this->postJson('/api/deposit', ['amount' => 50.25])
            ->assertCreated()
            ->assertJson(['balance' => 150.25]);

        $this->assertEquals(150.25, $user->wallet->fresh()->balance);
    }

    public function test_deposit_rejects_more_than_two_decimals(): void
    {
        $user = $this->userWithWallet();
        Sanctum::actingAs($user);

        $this->postJson('/api/deposit', ['amount' => 10.999])
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');
    }

    public function test_deposit_rejects_non_positive_amount(): void
    {
        $user = $this->userWithWallet();
        Sanctum::actingAs($user);

        $this->postJson('/api/deposit', ['amount' => -5])
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');
    }

    // ----- Transferência -----

    public function test_transfer_moves_funds(): void
    {
        $sender = $this->userWithWallet(200);
        $recipient = $this->userWithWallet(0);
        Sanctum::actingAs($sender);

        $this->postJson('/api/transfer', ['email' => $recipient->email, 'amount' => 75])
            ->assertCreated()
            ->assertJson(['balance' => 125]);

        $this->assertEquals(75.00, $recipient->wallet->fresh()->balance);
    }

    public function test_transfer_with_insufficient_balance_returns_422(): void
    {
        $sender = $this->userWithWallet(20);
        $recipient = $this->userWithWallet(0);
        Sanctum::actingAs($sender);

        $this->postJson('/api/transfer', ['email' => $recipient->email, 'amount' => 100])
            ->assertStatus(422)
            ->assertJson(['message' => 'Saldo insuficiente para realizar a transferência.']);

        $this->assertEquals(20.00, $sender->wallet->fresh()->balance);
    }

    public function test_cannot_transfer_to_self(): void
    {
        $user = $this->userWithWallet(100);
        Sanctum::actingAs($user);

        $this->postJson('/api/transfer', ['email' => $user->email, 'amount' => 10])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_transfer_requires_existing_recipient(): void
    {
        $user = $this->userWithWallet(100);
        Sanctum::actingAs($user);

        $this->postJson('/api/transfer', ['email' => 'ninguem@example.com', 'amount' => 10])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    // ----- Extrato & Estorno -----

    public function test_statement_lists_ledger_entries(): void
    {
        $user = $this->userWithWallet(0);
        (new DepositService())->execute($user->wallet, 50);
        Sanctum::actingAs($user);

        $this->getJson('/api/statement')
            ->assertOk()
            ->assertJsonFragment(['direction' => 'credit', 'amount' => 50.0]);
    }

    public function test_user_can_reverse_own_transaction(): void
    {
        $user = $this->userWithWallet(100);
        $deposit = (new DepositService())->execute($user->wallet, 50);
        Sanctum::actingAs($user);

        $this->postJson("/api/transactions/{$deposit->id}/reverse")
            ->assertCreated();

        $this->assertEquals(100.00, $user->wallet->fresh()->balance);
        $this->assertSame(TransactionStatus::Reversed, $deposit->fresh()->status);
    }

    public function test_user_cannot_reverse_someone_elses_transaction(): void
    {
        $owner = $this->userWithWallet(100);
        $stranger = $this->userWithWallet(0);
        $deposit = (new DepositService())->execute($owner->wallet, 50);

        Sanctum::actingAs($stranger);

        $this->postJson("/api/transactions/{$deposit->id}/reverse")
            ->assertForbidden();
    }
}
