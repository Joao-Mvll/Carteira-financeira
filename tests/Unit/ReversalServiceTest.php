<?php

namespace Tests\Unit;

use App\Enums\LedgerDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Reversal;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\DepositService;
use App\Services\ReversalService;
use App\Services\TransferService;
use Tests\TestCase;

class ReversalServiceTest extends TestCase
{
    private ReversalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ReversalService();
    }

    public function test_reversing_a_deposit_removes_the_credited_amount(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();
        $deposit = (new DepositService())->execute($wallet, 50.00);

        $this->assertEquals(150.00, $wallet->fresh()->balance);

        $this->service->execute($deposit);

        $this->assertEquals(100.00, $wallet->fresh()->balance);
    }

    public function test_reversing_a_transfer_restores_both_balances(): void
    {
        $from = Wallet::factory()->withBalance(200)->create();
        $to = Wallet::factory()->withBalance(50)->create();
        $transfer = (new TransferService())->execute($from, $to, 75.00);

        $this->service->execute($transfer);

        $this->assertEquals(200.00, $from->fresh()->balance);
        $this->assertEquals(50.00, $to->fresh()->balance);
    }

    public function test_reversal_creates_a_completed_reversal_transaction(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();
        $deposit = (new DepositService())->execute($wallet, 50.00);

        $reversalTransaction = $this->service->execute($deposit, 'fraude');

        $this->assertSame(TransactionType::Reversal, $reversalTransaction->type);
        $this->assertSame(TransactionStatus::Completed, $reversalTransaction->status);
        $this->assertEquals(50.00, $reversalTransaction->amount);
    }

    public function test_reversal_marks_original_transaction_as_reversed(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();
        $deposit = (new DepositService())->execute($wallet, 50.00);

        $this->service->execute($deposit);

        $this->assertSame(TransactionStatus::Reversed, $deposit->fresh()->status);
    }

    public function test_reversal_records_the_link_and_reason(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();
        $deposit = (new DepositService())->execute($wallet, 50.00);

        $reversalTransaction = $this->service->execute($deposit, 'estorno solicitado');

        $reversal = Reversal::first();
        $this->assertNotNull($reversal);
        $this->assertEquals($deposit->id, $reversal->original_transaction_id);
        $this->assertEquals($reversalTransaction->id, $reversal->reversal_transaction_id);
        $this->assertSame('estorno solicitado', $reversal->reason);
    }

    public function test_reversal_writes_inverse_ledger_entries(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();
        $deposit = (new DepositService())->execute($wallet, 50.00);

        $reversalTransaction = $this->service->execute($deposit);

        $entry = $reversalTransaction->ledgerEntries()->first();

        // O depósito creditou; o estorno deve debitar.
        $this->assertSame(LedgerDirection::Debit, $entry->direction);
        $this->assertEquals(50.00, $entry->amount);
        $this->assertEquals(100.00, $entry->balance_after);
    }

    public function test_reversing_twice_returns_the_same_reversal_without_double_applying(): void
    {
        $wallet = Wallet::factory()->withBalance(100)->create();
        $deposit = (new DepositService())->execute($wallet, 50.00);

        $first = $this->service->execute($deposit);
        $second = $this->service->execute($deposit);

        $this->assertEquals($first->id, $second->id);
        $this->assertEquals(100.00, $wallet->fresh()->balance);
        $this->assertSame(1, Reversal::count());
    }

    public function test_cannot_reverse_a_non_completed_transaction(): void
    {
        $transaction = Transaction::create([
            'type' => TransactionType::Deposit,
            'status' => TransactionStatus::Failed,
            'amount' => 50.00,
            'idempotency_key' => 'failed-tx',
        ]);

        $this->expectException(\Exception::class);

        $this->service->execute($transaction);
    }

    public function test_reversal_can_push_balance_negative_when_funds_were_spent(): void
    {
        // Recebe 100 e transfere 90 adiante; sobra 10.
        $recipient = Wallet::factory()->withBalance(0)->create();
        $third = Wallet::factory()->withBalance(0)->create();

        $incoming = (new DepositService())->execute($recipient, 100.00);
        (new TransferService())->execute($recipient, $third, 90.00);

        $this->assertEquals(10.00, $recipient->fresh()->balance);

        // Estorno do depósito de 100 leva o saldo para -90.
        $this->service->execute($incoming);

        $this->assertEquals(-90.00, $recipient->fresh()->balance);
    }
}
