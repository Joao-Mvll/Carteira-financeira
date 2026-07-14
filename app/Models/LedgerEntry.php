<?php

namespace App\Models;

use App\Enums\LedgerDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    protected $fillable = ['transaction_id', 'wallet_id', 'direction', 'amount', 'balance_after'];

    protected function casts(): array
    {
        return [
            'direction' => LedgerDirection::class,
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}