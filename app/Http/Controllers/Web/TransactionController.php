<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\ReversalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $wallet = auth()->user()->wallet;

        $transactions = $wallet
            ->ledgerEntries()
            ->with('transaction')
            ->latest()
            ->paginate(15);

        return view('wallet.statement', compact('transactions'));
    }

    public function reverse(Request $request, Transaction $transaction, ReversalService $reversalService): RedirectResponse
    {
        $walletId = $request->user()->wallet->id;

        // Só pode estornar uma transação que movimentou a própria carteira.
        $involvesUser = $transaction->ledgerEntries()
            ->where('wallet_id', $walletId)
            ->exists();

        if (! $involvesUser) {
            abort(403);
        }

        try {
            $reversalService->execute($transaction, 'Estorno solicitado pelo usuário');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transação estornada com sucesso.');
    }
}
