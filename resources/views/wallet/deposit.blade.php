@extends('layout.app')

@section('title', 'Depositar')
@section('page-heading', 'Depositar')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow">
            <div class="card-header">Depositar na minha carteira</div>
            <div class="card-body">

                <form method="POST" action="{{ route('wallet.deposit.store') }}" novalidate
                      data-confirm
                      data-confirm-title="Confirmar depósito"
                      data-confirm-message="Depositar R$ {amount} na sua carteira?"
                      data-confirm-label="Depositar">
                    @csrf

                    <div class="mb-2">
                        <label class="form-label">Valor (R$)</label>
                        <input type="text" inputmode="decimal" data-money name="amount" id="amount"
                               value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror"
                               placeholder="0,00" autocomplete="off" required autofocus>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach ([10, 50, 100, 500] as $preset)
                            <button type="button" class="btn btn-outline-primary btn-sm np-quick-amount"
                                    data-amount="{{ $preset }}">R$ {{ $preset }}</button>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição <span class="text-muted">(opcional)</span></label>
                        <input type="text" name="description" maxlength="255"
                               value="{{ old('description') }}"
                               class="form-control @error('description') is-invalid @enderror"
                               placeholder="Ex.: aporte mensal">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
