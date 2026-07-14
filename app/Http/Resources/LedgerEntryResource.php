<?php

namespace App\Http\Resources;

use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LedgerEntry
 */
class LedgerEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'transaction_id' => $this->transaction_id,
            'type' => $this->transaction->type->value,
            'status' => $this->transaction->status->value,
            'direction' => $this->direction->value,
            'amount' => (float) $this->amount,
            'balance_after' => (float) $this->balance_after,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
