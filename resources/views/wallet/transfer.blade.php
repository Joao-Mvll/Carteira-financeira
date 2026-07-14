@extends('layout.app')

@section('title', 'Transferir')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow">
            <div class="card-header">Transferir para outro usuário</div>
            <div class="card-body">

                <form method="POST" action="{{ route('wallet.transfer.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">E-mail do destinatário</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Valor (R$)</label>
                        <input type="number" step="0.01" min="0.01" name="amount"
                               value="{{ old('amount') }}" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-left-right"></i> Transferir
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection
