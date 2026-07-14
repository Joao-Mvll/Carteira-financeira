<?php

namespace App\Http\Controllers\Web;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $wallet = $request->user()->wallet;

        $walletTransactions = Transaction::whereHas(
            'ledgerEntries',
            fn ($query) => $query->where('wallet_id', $wallet->id)
        );

        $transactions = (clone $walletTransactions)
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.index', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'depositCount' => (clone $walletTransactions)->where('type', TransactionType::Deposit)->count(),
            'transferCount' => (clone $walletTransactions)->where('type', TransactionType::Transfer)->count(),
        ]);
    }
}
