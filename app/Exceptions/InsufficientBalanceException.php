<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(string $message = 'Saldo insuficiente para completar a operação.')
    {
        parent::__construct($message);
    }

    /**
     * Deixa o próprio erro decidir como responder em cada camada.
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withInput()->with('error', $this->getMessage());
    }
}
