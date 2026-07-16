<?php

namespace App\Http\Controllers\Web;

use App\Enums\LedgerDirection;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\ReversalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $wallet = auth()->user()->wallet;

        $search = trim((string) $request->query('q', ''));
        $type = TransactionType::tryFrom((string) $request->query('type', ''));
        $direction = LedgerDirection::tryFrom((string) $request->query('direction', ''));
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo = $this->parseDate($request->query('date_to'));

        $transactions = $wallet
            ->ledgerEntries()
            ->with('transaction')
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('transaction', function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($query) use ($type) {
                $query->whereHas('transaction', fn ($q) => $q->where('type', $type->value));
            })
            ->when($direction, fn ($query) => $query->where('direction', $direction->value))
            ->when($dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('wallet.statement', [
            'transactions' => $transactions,
            'search' => $search,
            'type' => $type,
            'direction' => $direction,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'types' => TransactionType::cases(),
            'hasFilters' => $search !== '' || $type || $direction || $dateFrom || $dateTo,
        ]);
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

        $reversalService->execute($transaction, 'Estorno solicitado pelo usuário');

        return back()->with('success', 'Transação estornada com sucesso.');
    }

    /**
     * Datas de filtro inválidas são ignoradas em vez de gerar erro.
     */
    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
