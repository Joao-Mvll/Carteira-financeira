<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepositService
{
    public function execute(Wallet $wallet, float $amount, ?string $idempotencyKey = null): Transaction
    {
        // Idempotência: se a chave já foi usada, devolve a transação existente
        // sem aplicar o saldo novamente.
        if ($idempotencyKey !== null) {
            $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        try {
            return DB::transaction(function () use ($wallet, $amount, $idempotencyKey) {
                $lockedWallet = Wallet::lockForUpdate()->find($wallet->id);

                $transaction = Transaction::create([
                    'type' => TransactionType::Deposit,
                    'status' => TransactionStatus::Completed,
                    'amount' => $amount,
                    'idempotency_key' => $idempotencyKey ?? (string) Str::uuid(),
                ]);

                $newBalance = $lockedWallet->balance + $amount;

                $lockedWallet->ledgerEntries()->create([
                    'transaction_id' => $transaction->id,
                    'direction' => LedgerDirection::Credit,
                    'amount' => $amount,
                    'balance_after' => $newBalance,
                ]);

                $lockedWallet->update(['balance' => $newBalance]);

                return $transaction;
            });
        } catch (UniqueConstraintViolationException $e) {
            // Corrida: outra requisição com a mesma chave chegou primeiro.
            if ($idempotencyKey !== null) {
                $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();

                if ($existing !== null) {
                    return $existing;
                }
            }

            throw $e;
        }
    }
}
