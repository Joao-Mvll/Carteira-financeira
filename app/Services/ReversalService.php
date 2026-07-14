<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Reversal;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Exceptions\TransactionNotReversibleException;

class ReversalService
{
    /**
     * Estorna uma transação concluída aplicando entradas inversas no livro-razão.
     *
     * Cada lançamento da transação original ganha um lançamento oposto
     * (crédito vira débito e vice-versa), restaurando o saldo movimentado.
     * O estorno pode deixar o saldo negativo caso o valor já tenha sido gasto.
     *
     * @return Transaction A transação de estorno criada.
     */
    public function execute(Transaction $original, ?string $reason = null, ?string $idempotencyKey = null): Transaction
    {
        return DB::transaction(function () use ($original, $reason, $idempotencyKey) {
            // Idempotência: uma transação só pode ser estornada uma vez.
            $existing = Reversal::where('original_transaction_id', $original->id)->first();

            if ($existing !== null) {
                return $existing->reversalTransaction;
            }

            if ($original->status !== TransactionStatus::Completed) {
                throw new TransactionNotReversibleException(
                    'Somente transações concluídas podem ser estornadas.'
                );
            }

            $entries = $original->ledgerEntries()->get();

            // Trava as carteiras envolvidas sempre na mesma ordem (menor id primeiro).
            $walletIds = $entries->pluck('wallet_id')->unique()->sort()->values();
            $lockedWallets = [];

            foreach ($walletIds as $walletId) {
                $lockedWallets[$walletId] = Wallet::lockForUpdate()->find($walletId);
            }

            $reversalTransaction = Transaction::create([
                'type' => TransactionType::Reversal,
                'status' => TransactionStatus::Completed,
                'amount' => $original->amount,
                'idempotency_key' => $idempotencyKey ?? (string) Str::uuid(),
                'description' => $reason,
            ]);

            foreach ($entries as $entry) {
                $wallet = $lockedWallets[$entry->wallet_id];

                $inverseDirection = $entry->direction === LedgerDirection::Credit
                    ? LedgerDirection::Debit
                    : LedgerDirection::Credit;

                $delta = $inverseDirection === LedgerDirection::Credit
                    ? $entry->amount
                    : -$entry->amount;

                $newBalance = $wallet->balance + $delta;

                $wallet->ledgerEntries()->create([
                    'transaction_id' => $reversalTransaction->id,
                    'direction' => $inverseDirection,
                    'amount' => $entry->amount,
                    'balance_after' => $newBalance,
                ]);

                $wallet->update(['balance' => $newBalance]);
            }

            $original->update(['status' => TransactionStatus::Reversed]);

            Reversal::create([
                'original_transaction_id' => $original->id,
                'reversal_transaction_id' => $reversalTransaction->id,
                'reason' => $reason,
            ]);

            return $reversalTransaction;
        });
    }
}
