<?php

namespace App\Http\Controllers\Web;

use App\Enums\LedgerDirection;
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

        // Carrega, para cada transação, apenas o lançamento (LedgerEntry)
        // que pertence a ESTA carteira — é ele que diz se, do ponto de
        // vista deste usuário, a operação foi crédito ou débito.
        $transactions = (clone $walletTransactions)
            ->with(['ledgerEntries' => fn ($q) => $q->where('wallet_id', $wallet->id)])
            ->latest()
            ->limit(10)
            ->get();

        $entradas = $this->sumDirection($wallet->id, LedgerDirection::Credit, now()->month, now()->year);
        $saidas = $this->sumDirection($wallet->id, LedgerDirection::Debit, now()->month, now()->year);

        $entradasMesPassado = $this->sumDirection(
            $wallet->id, LedgerDirection::Credit, now()->subMonth()->month, now()->subMonth()->year
        );
        $saidasMesPassado = $this->sumDirection(
            $wallet->id, LedgerDirection::Debit, now()->subMonth()->month, now()->subMonth()->year
        );

        return view('dashboard.index', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'depositCount' => (clone $walletTransactions)->where('type', TransactionType::Deposit)->count(),
            'transferCount' => (clone $walletTransactions)->where('type', TransactionType::Transfer)->count(),
            'entradas' => $entradas,
            'saidas' => $saidas,
            'entradasChange' => $this->percentChange($entradas, $entradasMesPassado),
            'saidasChange' => $this->percentChange($saidas, $saidasMesPassado),
            'transactionsThisMonthCount' => (clone $walletTransactions)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ]);
    }

    private function sumDirection(int $walletId, LedgerDirection $direction, int $month, int $year): float
    {
        return (float) \App\Models\LedgerEntry::where('wallet_id', $walletId)
            ->where('direction', $direction)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');
    }

    /**
     * Retorna null quando não há base de comparação honesta
     * (mês anterior sem movimentação) — a view mostra "—" nesse caso,
     * em vez de inventar uma porcentagem.
     */
    private function percentChange(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return (($current - $previous) / $previous) * 100;
    }
}