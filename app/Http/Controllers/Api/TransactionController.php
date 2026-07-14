<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LedgerEntryResource;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\ReversalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $entries = $request->user()->wallet
            ->ledgerEntries()
            ->with('transaction')
            ->latest()
            ->paginate(15);

        return LedgerEntryResource::collection($entries);
    }

    public function reverse(Request $request, Transaction $transaction, ReversalService $reversalService): JsonResponse
    {
        $walletId = $request->user()->wallet->id;

        $involvesUser = $transaction->ledgerEntries()
            ->where('wallet_id', $walletId)
            ->exists();

        abort_unless($involvesUser, 403, 'Você não pode estornar esta transação.');

        $reversal = $reversalService->execute($transaction, 'Estorno solicitado via API');

        return response()->json([
            'message' => 'Transação estornada com sucesso.',
            'transaction' => new TransactionResource($reversal),
        ], 201);
    }
}
