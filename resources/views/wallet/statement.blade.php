@extends('layout.app')

@section('title', 'Extrato')
@section('page-heading', 'Extrato')

@section('content')

<div class="card shadow">
    <div class="card-header">Extrato da carteira</div>
    <div class="card-body">

        {{-- Filtros --}}
        <form method="GET" action="{{ route('wallet.statement') }}" class="row g-2 align-items-end mb-3">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1" for="filterQ">Pesquisar</label>
                <input type="text" name="q" id="filterQ" value="{{ $search }}"
                       class="form-control form-control-sm" placeholder="Descrição ou valor">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="filterType">Tipo</label>
                <select name="type" id="filterType" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($types as $typeOption)
                        <option value="{{ $typeOption->value }}" @selected($type === $typeOption)>
                            {{ $typeOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="filterDirection">Movimento</label>
                <select name="direction" id="filterDirection" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="credit" @selected($direction?->value === 'credit')>Entrada</option>
                    <option value="debit" @selected($direction?->value === 'debit')>Saída</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="filterFrom">De</label>
                <input type="date" name="date_from" id="filterFrom" value="{{ $dateFrom }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="filterTo">Até</label>
                <input type="date" name="date_to" id="filterTo" value="{{ $dateTo }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button class="btn btn-sm btn-primary" type="submit" title="Filtrar" data-np-tooltip aria-label="Filtrar">
                    <i class="bi bi-funnel"></i>
                </button>
                @if($hasFilters)
                    <a href="{{ route('wallet.statement') }}" class="btn btn-sm btn-outline-secondary"
                       title="Limpar filtros" data-np-tooltip aria-label="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>

        @if($hasFilters)
            <p class="text-muted small mb-3">
                {{ $transactions->total() }} movimento(s) encontrado(s) para os filtros aplicados.
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
                        <td>{{ $transaction->type->label() }}</td>
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
                        <td>{{ $transaction->status->label() }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#txModal{{ $entry->id }}"
                                    title="Detalhes" data-np-tooltip aria-label="Detalhes">
                                <i class="bi bi-eye"></i>
                            </button>

                            @if($transaction->status->value === 'completed' && $transaction->type->value !== 'reversal')
                                <form method="POST" action="{{ route('wallet.reverse', $transaction) }}"
                                      class="d-inline"
                                      data-confirm
                                      data-confirm-title="Confirmar estorno"
                                      data-confirm-message="Estornar a transação de R$ {{ number_format($entry->amount, 2, ',', '.') }}?"
                                      data-confirm-variant="danger"
                                      data-confirm-label="Estornar">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger"
                                            title="Estornar" data-np-tooltip aria-label="Estornar">
                                        <i class="bi bi-arrow-counterclockwise"></i>
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
                                        {{ $transaction->type->label() }}
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
                                        <dd class="col-7">{{ $transaction->status->label() }}</dd>

                                        <dt class="col-5 text-muted">Descrição</dt>
                                        <dd class="col-7">
                                            @if($transaction->description && mb_strlen($transaction->description) > 120)
                                                <span class="np-clamp" id="txDesc{{ $entry->id }}">{{ $transaction->description }}</span>
                                                <a href="#" class="np-link small" data-np-expand="txDesc{{ $entry->id }}">expandir</a>
                                            @else
                                                {{ $transaction->description ?: '—' }}
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                                    @if($transaction->status->value === 'completed' && $transaction->type->value !== 'reversal')
                                        <form method="POST" action="{{ route('wallet.reverse', $transaction) }}"
                                              data-confirm
                                              data-confirm-title="Confirmar estorno"
                                              data-confirm-message="Estornar a transação de R$ {{ number_format($entry->amount, 2, ',', '.') }}?"
                                              data-confirm-variant="danger"
                                              data-confirm-label="Estornar">
                                            @csrf
                                            <button class="btn btn-danger">
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
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox d-block text-muted" style="font-size:2.2rem;"></i>
                            @if($hasFilters)
                                <p class="fw-semibold mb-1 mt-2">Nenhum resultado para os filtros aplicados</p>
                                <a href="{{ route('wallet.statement') }}" class="np-link">Limpar filtros</a>
                            @else
                                <p class="fw-semibold mb-1 mt-2">Nenhuma movimentação ainda</p>
                                <p class="text-muted mb-3">Seu extrato aparecerá aqui após a primeira operação.</p>
                                <a href="{{ route('wallet.deposit') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle"></i> Fazer um depósito
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}

    </div>
</div>

@endsection
