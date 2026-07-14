<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Models\User;
use App\Services\DepositService;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet;

        return response()->json([
            'balance' => (float) $wallet->balance,
        ]);
    }

    public function deposit(DepositRequest $request, DepositService $depositService): JsonResponse
    {
        $wallet = $request->user()->wallet;

        $transaction = $depositService->execute(
            $wallet,
            (float) $request->validated('amount'),
            description: $request->validated('description'),
        );

        return response()->json([
            'message' => 'Depósito realizado com sucesso.',
            'balance' => (float) $wallet->fresh()->balance,
            'transaction' => new TransactionResource($transaction),
        ], 201);
    }

    public function transfer(TransferRequest $request, TransferService $transferService): JsonResponse
    {
        $data = $request->validated();

        $from = $request->user()->wallet;
        $to = User::where('email', $data['email'])->first()->wallet;

        // Saldo insuficiente lança InsufficientBalanceException (renderizada como 422 JSON).
        $transaction = $transferService->execute(
            $from,
            $to,
            (float) $data['amount'],
            description: $data['description'] ?? null,
        );

        return response()->json([
            'message' => 'Transferência realizada com sucesso.',
            'balance' => (float) $from->fresh()->balance,
            'transaction' => new TransactionResource($transaction),
        ], 201);
    }
}
