<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class TransactionNotReversibleException extends RuntimeException
{
    public function __construct(string $message = 'Esta transação não pode ser estornada.')
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

        return back()->with('error', $this->getMessage());
    }
}