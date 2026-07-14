@extends('layout.app')

@section('title', 'Extrato')

@section('content')

<div class="card shadow">
    <div class="card-header">Extrato da carteira</div>
    <div class="card-body">

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Movimento</th>
                    <th>Valor</th>
                    <th>Saldo após</th>
                    <th>Status</th>
                    <th class="text-end">Ação</th>
                </tr>
            </thead>
            <tbody>
            @forelse($transactions as $entry)
                @php($transaction = $entry->transaction)
                <tr>
                    <td>{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($transaction->type->value) }}</td>
                    <td>
                        @if($entry->direction->value === 'credit')
                            <span class="badge bg-success">+ Crédito</span>
                        @else
                            <span class="badge bg-danger">- Débito</span>
                        @endif
                    </td>
                    <td>R$ {{ number_format($entry->amount, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($entry->balance_after, 2, ',', '.') }}</td>
                    <td>{{ ucfirst($transaction->status->value) }}</td>
                    <td class="text-end">
                        @if($transaction->status->value === 'completed' && $transaction->type->value !== 'reversal')
                            <form method="POST" action="{{ route('wallet.reverse', $transaction) }}"
                                  onsubmit="return confirm('Confirmar estorno desta transação?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-arrow-counterclockwise"></i> Estornar
                                </button>
                            </form>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Nenhuma movimentação encontrada.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $transactions->links() }}

    </div>
</div>

@endsection
