<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Services\DepositService;
use App\Services\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function deposit(): View
    {
        return view('wallet.deposit');
    }

    public function transfer(): View
    {
        return view('wallet.transfer');
    }

    public function storeDeposit(DepositRequest $request, DepositService $depositService): RedirectResponse
    {
        $depositService->execute($request->user()->wallet, (float) $request->validated('amount'));

        return redirect()->route('dashboard')
            ->with('success', 'Depósito realizado com sucesso.');
    }

    public function storeTransfer(TransferRequest $request, TransferService $transferService): RedirectResponse
    {
        $data = $request->validated();

        $from = $request->user()->wallet;
        $to = User::where('email', $data['email'])->first()->wallet;

        // Saldo insuficiente lança InsufficientBalanceException, que se auto-renderiza.
        $transferService->execute($from, $to, (float) $data['amount']);

        return redirect()->route('dashboard')
            ->with('success', 'Transferência realizada com sucesso.');
    }
}
