<?php

namespace Tests\Unit;

use App\Enums\LedgerDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TransferService;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    private TransferService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TransferService();
    }

    public function test_transfer_moves_balance_from_origin_to_destination(): void
    {
        $from = Wallet::factory()->withBalance(200)->create();
        $to = Wallet::factory()->withBalance(50)->create();

        $this->service->execute($from, $to, 75.00);

        $this->assertEquals(125.00, $from->fresh()->balance);
        $this->assertEquals(125.00, $to->fresh()->balance);
    }

    public function test_transfer_creates_a_completed_transfer_transaction(): void
    {
        $from = Wallet::factory()->withBalance(100)->create();
        $to = Wallet::factory()->create();

        $transaction = $this->service->execute($from, $to, 40.00);

        $this->assertSame(TransactionType::Transfer, $transaction->type);
        $this->assertSame(TransactionStatus::Completed, $transaction->status);
        $this->assertEquals(40.00, $transaction->amount);
    }

    public function test_transfer_creates_debit_and_credit_ledger_entries_for_the_same_transaction(): void
    {
        $from = Wallet::factory()->withBalance(100)->create();
        $to = Wallet::factory()->withBalance(10)->create();

        $transaction = $this->service->execute($from, $to, 30.00);

        $debit = $from->ledgerEntries()->first();
        $credit = $to->ledgerEntries()->first();

        $this->assertSame(LedgerDirection::Debit, $debit->direction);
        $this->assertEquals(30.00, $debit->amount);
        $this->assertEquals(70.00, $debit->balance_after);

        $this->assertSame(LedgerDirection::Credit, $credit->direction);
        $this->assertEquals(30.00, $credit->amount);
        $this->assertEquals(40.00, $credit->balance_after);

        $this->assertEquals($transaction->id, $debit->transaction_id);
        $this->assertEquals($transaction->id, $credit->transaction_id);
    }

    public function test_transfer_fails_when_origin_has_insufficient_balance(): void
    {
        $from = Wallet::factory()->withBalance(20)->create();
        $to = Wallet::factory()->create();

        $this->expectException(InsufficientBalanceException::class);

        $this->service->execute($from, $to, 100.00);
    }

    public function test_a_negative_balance_wallet_cannot_transfer(): void
    {
        $from = Wallet::factory()->withBalance(-50)->create();
        $to = Wallet::factory()->create();

        $this->expectException(InsufficientBalanceException::class);

        $this->service->execute($from, $to, 10.00);
    }

    public function test_failed_transfer_leaves_balances_and_records_untouched(): void
    {
        $from = Wallet::factory()->withBalance(20)->create();
        $to = Wallet::factory()->withBalance(5)->create();

        try {
            $this->service->execute($from, $to, 100.00);
        } catch (\Exception) {
            // esperado: saldo insuficiente
        }

        $this->assertEquals(20.00, $from->fresh()->balance);
        $this->assertEquals(5.00, $to->fresh()->balance);
        $this->assertSame(0, Transaction::count());
        $this->assertSame(0, $from->ledgerEntries()->count());
        $this->assertSame(0, $to->ledgerEntries()->count());
    }

    public function test_transfer_of_exact_balance_is_allowed(): void
    {
        $from = Wallet::factory()->withBalance(100)->create();
        $to = Wallet::factory()->create();

        $this->service->execute($from, $to, 100.00);

        $this->assertEquals(0.00, $from->fresh()->balance);
        $this->assertEquals(100.00, $to->fresh()->balance);
    }

    public function test_transfer_works_regardless_of_wallet_id_ordering(): void
    {
        // origem com id maior que destino, para exercitar a ordem de lock
        $to = Wallet::factory()->withBalance(0)->create();
        $from = Wallet::factory()->withBalance(100)->create();

        $this->assertGreaterThan($to->id, $from->id);

        $this->service->execute($from, $to, 60.00);

        $this->assertEquals(40.00, $from->fresh()->balance);
        $this->assertEquals(60.00, $to->fresh()->balance);
    }

    public function test_transfer_uses_provided_idempotency_key(): void
    {
        $from = Wallet::factory()->withBalance(100)->create();
        $to = Wallet::factory()->create();

        $transaction = $this->service->execute($from, $to, 10.00, 'transfer-key-1');

        $this->assertSame('transfer-key-1', $transaction->idempotency_key);
    }

    public function test_repeating_the_same_idempotency_key_does_not_move_balance_twice(): void
    {
        $from = Wallet::factory()->withBalance(100)->create();
        $to = Wallet::factory()->create();

        $first = $this->service->execute($from, $to, 40.00, 'transfer-key-2');
        $second = $this->service->execute($from, $to, 40.00, 'transfer-key-2');

        $this->assertEquals($first->id, $second->id);
        $this->assertEquals(60.00, $from->fresh()->balance);
        $this->assertEquals(40.00, $to->fresh()->balance);
        $this->assertSame(1, Transaction::count());
        $this->assertSame(1, $from->ledgerEntries()->count());
        $this->assertSame(1, $to->ledgerEntries()->count());
    }
}
