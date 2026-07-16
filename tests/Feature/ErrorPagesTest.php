<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Services\DepositService;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
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

    public function test_reversing_someone_elses_transaction_shows_403_page(): void
    {
        $owner = $this->userWithWallet(100);
        $stranger = $this->userWithWallet(0);
        $deposit = (new DepositService)->execute($owner->wallet, 50);

        $this->actingAs($stranger)
            ->post("/transactions/{$deposit->id}/reverse")
            ->assertForbidden()
            ->assertSee('Acesso negado');
    }

    public function test_reversing_a_nonexistent_transaction_shows_404_page(): void
    {
        $user = $this->userWithWallet(100);

        $this->actingAs($user)
            ->post('/transactions/999999/reverse')
            ->assertNotFound()
            ->assertSee('Página não encontrada');
    }

    public function test_too_many_login_attempts_show_429_page(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Senha1234')]);

        // o grupo guest usa throttle:5,1 — a sexta tentativa no mesmo minuto estoura
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'errada']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'errada'])
            ->assertStatus(429)
            ->assertSee('Muitas tentativas');
    }
}
