@extends('layout.app')

@section('title', 'Depositar')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow">
            <div class="card-header">Depositar na minha carteira</div>
            <div class="card-body">

                <form method="POST" action="{{ route('wallet.deposit.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Valor (R$)</label>
                        <input type="number" step="0.01" min="0.01" name="amount"
                               value="{{ old('amount') }}" class="form-control" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Depositar
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection
