<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService
{
    public function execute(Wallet $from, Wallet $to, float $amount, ?string $idempotencyKey = null, ?string $description = null): Transaction
    {
        // Idempotência: se a chave já foi usada, devolve a transação existente
        // sem mover saldo novamente.
        if ($idempotencyKey !== null) {
            $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        try {
            return DB::transaction(function () use ($from, $to, $amount, $idempotencyKey, $description) {
                // Trava sempre na mesma ordem (menor id primeiro) para evitar deadlock
                [$first, $second] = $from->id < $to->id ? [$from, $to] : [$to, $from];

                $lockedFirst = Wallet::lockForUpdate()->find($first->id);
                $lockedSecond = Wallet::lockForUpdate()->find($second->id);

                // Depois de travar, reidentifica quem é origem e quem é destino
                $lockedFrom = $lockedFirst->id === $from->id ? $lockedFirst : $lockedSecond;
                $lockedTo = $lockedFirst->id === $to->id ? $lockedFirst : $lockedSecond;

                // Valida saldo (bloqueia inclusive carteira com saldo negativo)
                if ($lockedFrom->balance < $amount) {
                    throw new InsufficientBalanceException('Saldo insuficiente para realizar a transferência.');
                }

                $transaction = Transaction::create([
                    'type' => TransactionType::Transfer,
                    'status' => TransactionStatus::Completed,
                    'amount' => $amount,
                    'idempotency_key' => $idempotencyKey ?? (string) Str::uuid(),
                    'description' => $description,
                ]);

                // Débito na carteira origem
                $newFromBalance = $lockedFrom->balance - $amount;
                $lockedFrom->ledgerEntries()->create([
                    'transaction_id' => $transaction->id,
                    'direction' => LedgerDirection::Debit,
                    'amount' => $amount,
                    'balance_after' => $newFromBalance,
                ]);
                $lockedFrom->update(['balance' => $newFromBalance]);

                // Crédito na carteira destino
                $newToBalance = $lockedTo->balance + $amount;
                $lockedTo->ledgerEntries()->create([
                    'transaction_id' => $transaction->id,
                    'direction' => LedgerDirection::Credit,
                    'amount' => $amount,
                    'balance_after' => $newToBalance,
                ]);
                $lockedTo->update(['balance' => $newToBalance]);

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
