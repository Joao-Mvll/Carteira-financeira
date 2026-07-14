<?php

namespace Tests\Unit;

use App\Enums\LedgerDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Wallet;
use App\Services\DepositService;
use Tests\TestCase;

class DepositServiceTest extends TestCase
{
    private DepositService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DepositService();
    }

    public function test_deposit_increases_wallet_balance(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();

        $this->service->execute($wallet, 50.00);

        $this->assertEquals(150.00, $wallet->fresh()->balance);
    }

    public function test_deposit_creates_a_completed_deposit_transaction(): void
    {
        $wallet = Wallet::factory()->create();

        $transaction = $this->service->execute($wallet, 75.00);

        $this->assertSame(TransactionType::Deposit, $transaction->type);
        $this->assertSame(TransactionStatus::Completed, $transaction->status);
        $this->assertEquals(75.00, $transaction->amount);
    }

    public function test_deposit_creates_a_credit_ledger_entry_with_running_balance(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();

        $transaction = $this->service->execute($wallet, 25.00);

        $entry = $wallet->ledgerEntries()->first();

        $this->assertNotNull($entry);
        $this->assertSame(LedgerDirection::Credit, $entry->direction);
        $this->assertEquals(25.00, $entry->amount);
        $this->assertEquals(125.00, $entry->balance_after);
        $this->assertEquals($transaction->id, $entry->transaction_id);
    }

    public function test_deposit_uses_provided_idempotency_key(): void
    {
        $wallet = Wallet::factory()->create();

        $transaction = $this->service->execute($wallet, 10.00, 'my-key-123');

        $this->assertSame('my-key-123', $transaction->idempotency_key);
    }

    public function test_deposit_generates_an_idempotency_key_when_none_is_given(): void
    {
        $wallet = Wallet::factory()->create();

        $transaction = $this->service->execute($wallet, 10.00);

        $this->assertNotEmpty($transaction->idempotency_key);
    }

    public function test_multiple_deposits_accumulate_the_balance(): void
    {
        $wallet = Wallet::factory()->create();

        $this->service->execute($wallet, 30.00);
        $this->service->execute($wallet, 20.00);

        $this->assertEquals(50.00, $wallet->fresh()->balance);
        $this->assertSame(2, $wallet->ledgerEntries()->count());
    }

    public function test_deposit_onto_a_negative_balance_adds_to_it(): void
    {
        // Carteira negativa (ex.: após um estorno) deve receber o crédito por cima.
        $wallet = Wallet::factory()->withBalance(-90)->create();

        $this->service->execute($wallet, 100.00);

        $this->assertEquals(10.00, $wallet->fresh()->balance);
    }

    public function test_repeating_the_same_idempotency_key_does_not_apply_the_deposit_twice(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();

        $first = $this->service->execute($wallet, 50.00, 'dep-key-1');
        $second = $this->service->execute($wallet, 50.00, 'dep-key-1');

        // Mesma transação devolvida, saldo aplicado uma única vez.
        $this->assertEquals($first->id, $second->id);
        $this->assertEquals(150.00, $wallet->fresh()->balance);
        $this->assertSame(1, $wallet->ledgerEntries()->count());
        $this->assertSame(1, \App\Models\Transaction::count());
    }
}
