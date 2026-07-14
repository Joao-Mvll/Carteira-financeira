@extends('layout.app')

@section('title', 'Extrato')

@section('content')

@php
    $typeLabel = fn ($type) => match ($type) {
        'deposit' => 'Depósito',
        'transfer' => 'Transferência',
        'reversal' => 'Estorno',
        default => ucfirst($type),
    };
    $statusLabel = fn ($status) => match ($status) {
        'completed' => 'Concluída',
        'pending' => 'Pendente',
        'failed' => 'Falhou',
        'reversed' => 'Estornada',
        default => ucfirst($status),
    };
@endphp

<div class="card shadow">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span>Extrato da carteira</span>

        <form method="GET" action="{{ route('wallet.statement') }}" class="d-flex" style="max-width: 320px;">
            <div class="input-group input-group-sm">
                <input type="text" name="q" value="{{ $search }}" class="form-control"
                       placeholder="Pesquisar (descrição, tipo, valor)">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
                @if($search !== '')
                    <a href="{{ route('wallet.statement') }}" class="btn btn-outline-secondary" title="Limpar">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
    <div class="card-body">

        @if($search !== '')
            <p class="text-muted small mb-3">
                Resultados para <strong>"{{ $search }}"</strong> — {{ $transactions->total() }} movimento(s).
            </p>
        @endif

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Movimento</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th>Saldo após</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($transactions as $entry)
                    @php($transaction = $entry->transaction)
                    @php($isCredit = $entry->direction->value === 'credit')
                    <tr>
                        <td>{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $typeLabel($transaction->type->value) }}</td>
                        <td>
                            @if($isCredit)
                                <span class="badge bg-success">+ Crédito</span>
                            @else
                                <span class="badge bg-danger">- Débito</span>
                            @endif
                        </td>
                        <td class="text-muted">
                            {{ \Illuminate\Support\Str::limit($transaction->description, 30) ?: '—' }}
                        </td>
                        <td>R$ {{ number_format($entry->amount, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($entry->balance_after, 2, ',', '.') }}</td>
                        <td>{{ $statusLabel($transaction->status->value) }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#txModal{{ $entry->id }}">
                                <i class="bi bi-eye"></i> Detalhes
                            </button>

                            @if($transaction->status->value === 'completed' && $transaction->type->value !== 'reversal')
                                <form method="POST" action="{{ route('wallet.reverse', $transaction) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Confirmar estorno desta transação?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-arrow-counterclockwise"></i> Estornar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal de detalhes da transação --}}
                    <div class="modal fade" id="txModal{{ $entry->id }}" tabindex="-1"
                         aria-labelledby="txModalLabel{{ $entry->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="txModalLabel{{ $entry->id }}">
                                        {{ $typeLabel($transaction->type->value) }}
                                        @if($isCredit)
                                            <span class="badge bg-success ms-1">Crédito</span>
                                        @else
                                            <span class="badge bg-danger ms-1">Débito</span>
                                        @endif
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <dl class="row mb-0">
                                        <dt class="col-5 text-muted">Transação nº</dt>
                                        <dd class="col-7">#{{ $transaction->id }}</dd>

                                        <dt class="col-5 text-muted">Data / hora</dt>
                                        <dd class="col-7">{{ $entry->created_at->format('d/m/Y H:i:s') }}</dd>

                                        <dt class="col-5 text-muted">Valor</dt>
                                        <dd class="col-7 fw-semibold {{ $isCredit ? 'text-success' : 'text-danger' }}">
                                            {{ $isCredit ? '+' : '-' }} R$ {{ number_format($entry->amount, 2, ',', '.') }}
                                        </dd>

                                        <dt class="col-5 text-muted">Saldo após</dt>
                                        <dd class="col-7">R$ {{ number_format($entry->balance_after, 2, ',', '.') }}</dd>

                                        <dt class="col-5 text-muted">Status</dt>
                                        <dd class="col-7">{{ $statusLabel($transaction->status->value) }}</dd>

                                        <dt class="col-5 text-muted">Descrição</dt>
                                        <dd class="col-7">{{ $transaction->description ?: '—' }}</dd>
                                    </dl>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                    @if($transaction->status->value === 'completed' && $transaction->type->value !== 'reversal')
                                        <form method="POST" action="{{ route('wallet.reverse', $transaction) }}"
                                              onsubmit="return confirm('Confirmar estorno desta transação?')">
                                            @csrf
                                            <button class="btn btn-warning">
                                                <i class="bi bi-arrow-counterclockwise"></i> Estornar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Nenhuma movimentação encontrada.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}

    </div>
</div>

@endsection
