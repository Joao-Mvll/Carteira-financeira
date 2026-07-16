@extends('layout.app')

@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')

@section('content')

<style>
    .np-balance-card {
        background: linear-gradient(160deg, #0f1b2d 0%, #0a1420 60%, #0d2b28 100%);
        color: #fff;
        border-radius: 18px;
        padding: 1.8rem 2rem;
        margin-bottom: 1.5rem;
    }
    .np-balance-label { color: #94a3b8; font-size: .9rem; margin-bottom: .3rem; }
    .np-balance-amount { font-size: 2.4rem; font-weight: 700; margin-bottom: .9rem; }
    .np-balance-eye {
        background: rgba(255,255,255,.12);
        border: none;
        color: #e2e8f0;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    .np-balance-eye:hover { background: rgba(255,255,255,.16); color: #fff; }
    .np-badge-active {
        background: rgba(34,197,94,.15);
        color: #4ade80;
        border-radius: 20px;
        padding: .3rem .8rem;
        font-size: .8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }
    .np-badge-active .dot { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; }
    .np-updated-note { color: #64748b; font-size: .85rem; margin-left: .8rem; }

    .np-action-card {
        background: #fff;
        border: 1px solid var(--np-border);
        border-radius: 14px;
        padding: 1.4rem 1rem;
        text-align: center;
        text-decoration: none;
        display: block;
        transition: border-color .15s;
    }
    .np-action-card:hover { border-color: var(--np-blue); }
    .np-action-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto .7rem;
        font-size: 1.2rem;
    }
    .np-action-label { color: var(--np-text); font-weight: 600; font-size: .92rem; }

    .np-metric-card {
        background: #fff;
        border: 1px solid var(--np-border);
        border-radius: 14px;
        padding: 1.3rem 1.4rem;
        height: 100%;
    }
    .np-metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .8rem;
    }
    .np-metric-label { color: var(--np-text-muted); font-size: .88rem; font-weight: 500; }
    .np-metric-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
    }
    .np-metric-value { font-size: 1.5rem; font-weight: 700; color: var(--np-text); margin-bottom: .3rem; }
    .np-metric-change { font-size: .82rem; font-weight: 600; }

    .np-tx-card {
        background: #fff;
        border: 1px solid var(--np-border);
        border-radius: 14px;
        overflow: hidden;
    }
    .np-tx-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid var(--np-border);
    }
    .np-tx-header h6 { margin: 0; font-weight: 700; color: var(--np-text); }
    .np-tx-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.4rem;
        border-bottom: 1px solid var(--np-border);
    }
    .np-tx-row:last-child { border-bottom: none; }
    .np-tx-left { display: flex; align-items: center; gap: .9rem; }
    .np-tx-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .np-tx-title { font-weight: 600; color: var(--np-text); font-size: .92rem; }
    .np-tx-subtitle { color: var(--np-text-muted); font-size: .82rem; }
    .np-tx-right { text-align: right; }
    .np-tx-amount { font-weight: 700; font-size: .95rem; }
    .np-tx-date { color: var(--np-text-muted); font-size: .78rem; margin-top: .2rem; }
    .np-status-badge {
        background: var(--np-green-light);
        color: #15803d;
        border-radius: 20px;
        padding: .2rem .7rem;
        font-size: .75rem;
        font-weight: 600;
        display: inline-block;
        margin-top: .2rem;
    }
    .np-status-badge.failed, .np-status-badge.reversed {
        background: var(--np-red-light);
        color: #b91c1c;
    }
</style>

{{-- Card de saldo --}}
<div class="np-balance-card">
    <div class="np-balance-label">Saldo disponível</div>
    <div class="d-flex align-items-center gap-2 mb-2">
        <div class="np-balance-amount mb-0" id="npBalanceAmount"
             data-np-hideable>R$ {{ number_format($wallet->balance ?? 0, 2, ',', '.') }}</div>
        <button type="button" id="npBalanceToggle" class="np-balance-eye" aria-label="Ocultar valores" title="Ocultar valores">
            <i class="bi bi-eye"></i>
        </button>
    </div>
    <span class="np-badge-active"><span class="dot"></span> Conta ativa</span>
    <span class="np-updated-note">Atualizado agora</span>
</div>

{{-- Ações rápidas --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <a href="{{ route('wallet.deposit') }}" class="np-action-card">
            <span class="np-action-icon" style="background:var(--np-blue-light);color:var(--np-blue);">
                <i class="bi bi-arrow-down"></i>
            </span>
            <div class="np-action-label">Depositar</div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('wallet.transfer') }}" class="np-action-card">
            <span class="np-action-icon" style="background:#f5f3ff;color:#7c3aed;">
                <i class="bi bi-send"></i>
            </span>
            <div class="np-action-label">Transferir</div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('wallet.statement') }}" class="np-action-card">
            <span class="np-action-icon" style="background:#fffbeb;color:#d97706;">
                <i class="bi bi-file-earmark-text"></i>
            </span>
            <div class="np-action-label">Extrato</div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="np-metric-card">
            <div class="np-metric-top">
                <span class="np-metric-label">Entradas</span>
                <span class="np-metric-icon" style="background:var(--np-green-light);color:#16a34a;">
                    <i class="bi bi-graph-up-arrow"></i>
                </span>
            </div>
            <div class="np-metric-value" data-np-hideable>R$ {{ number_format($entradas, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="np-metric-card">
            <div class="np-metric-top">
                <span class="np-metric-label">Saídas</span>
                <span class="np-metric-icon" style="background:var(--np-red-light);color:#dc2626;">
                    <i class="bi bi-graph-down-arrow"></i>
                </span>
            </div>
            <div class="np-metric-value" data-np-hideable>R$ {{ number_format($saidas, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="np-metric-card">
            <div class="np-metric-top">
                <span class="np-metric-label">Transações</span>
                <span class="np-metric-icon" style="background:var(--np-blue-light);color:var(--np-blue);">
                    <i class="bi bi-bar-chart-line"></i>
                </span>
            </div>
            <div class="np-metric-value" data-np-hideable="•••">{{ $transactionsThisMonthCount }}</div>
        </div>
    </div>
</div>

{{-- Últimas transações --}}
<div class="np-tx-card">
    <div class="np-tx-header">
        <h6>Últimas transações</h6>
        <button type="submit" class="btn btn-primary">
            <a href="{{ route('wallet.statement') }}" class="text-decoration-none text-reset" style="font-size:.85rem;">
            Ver todas <i class="bi bi-chevron-right"></i>
            </a>    
        </button>
    </div>

    @forelse($transactions as $transaction)
        @php
            // O lançamento (LedgerEntry) desta wallet específica é a fonte
            // de verdade sobre direção — não o "type" da transação.
            $entry = $transaction->ledgerEntries->first();
            $isCredit = $entry && $entry->direction->value === 'credit';

            $label = match(true) {
                $transaction->type->value === 'deposit' => 'Depósito recebido',
                $transaction->type->value === 'transfer' && $isCredit => 'Transferência recebida',
                $transaction->type->value === 'transfer' && !$isCredit => 'Transferência enviada',
                $transaction->type->value === 'reversal' => 'Estorno',
                default => ucfirst($transaction->type->value),
            };
        @endphp
        <div class="np-tx-row">
            <div class="np-tx-left">
                <span class="np-tx-icon" style="background: {{ $isCredit ? 'var(--np-green-light)' : 'var(--np-red-light)' }}; color: {{ $isCredit ? '#16a34a' : '#dc2626' }};">
                    <i class="bi {{ $isCredit ? 'bi-arrow-down' : 'bi-arrow-up-right' }}"></i>
                </span>
                <div>
                    <div class="np-tx-title">{{ $label }}</div>
                    <div class="np-tx-subtitle">{{ $transaction->status->label() }}</div>
                </div>
            </div>
            <div class="np-tx-right">
                <div class="np-tx-amount" data-np-hideable style="color: {{ $isCredit ? '#16a34a' : 'var(--np-text)' }};">
                    {{ $isCredit ? '+' : '-' }}R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                </div>
                <div class="np-tx-date">{{ $transaction->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
    @empty
        <div class="np-tx-row flex-column justify-content-center text-center py-5">
            <i class="bi bi-receipt d-block text-muted" style="font-size:2.2rem;"></i>
            <p class="fw-semibold mb-1 mt-2">Nenhuma movimentação ainda</p>
            <p class="text-muted mb-3">Suas últimas transações aparecerão aqui.</p>
            <a href="{{ route('wallet.deposit') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Fazer meu primeiro depósito
            </a>
        </div>
    @endforelse
</div>

@endsection