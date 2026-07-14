@extends('layout.app')

@section('title','Dashboard')

@section('content')

<div class="row">

    <div class="col-md-4">

        <div class="card card-balance shadow">

            <div class="card-body">

                <h6>Saldo Atual</h6>

                <h2>

                    R$ {{ number_format($wallet->balance ?? 0,2,',','.') }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card shadow">

            <div class="card-body">

                <h6>Transferências</h6>

                <h2>

                    {{ $transferCount ?? 0 }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card shadow">

            <div class="card-body">

                <h6>Depósitos</h6>

                <h2>

                    {{ $depositCount ?? 0 }}

                </h2>

            </div>

        </div>

    </div>

</div>

<div class="card mt-4 shadow">

    <div class="card-header">

        Últimas movimentações

    </div>

    <div class="card-body">

        <table class="table table-striped">

            <thead>

            <tr>

                <th>Tipo</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Data</th>

            </tr>

            </thead>

           <tbody>

@if(isset($transactions) && $transactions->count())

    @foreach($transactions as $transaction)

        <tr>
            <td>{{ ucfirst($transaction->type->value) }}</td>
            <td>R$ {{ number_format($transaction->amount,2,',','.') }}</td>
            <td>{{ ucfirst($transaction->status->value) }}</td>
            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
        </tr>

    @endforeach

@else

    <tr>
        <td colspan="4" class="text-center">
            Nenhuma movimentação encontrada.
        </td>
    </tr>

@endif

</tbody>

        </table>

    </div>

</div>

@endsection