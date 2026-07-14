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
                               class="form-control @error('email') is-invalid @enderror" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Valor (R$)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                               value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach ([10, 50, 100, 500] as $preset)
                            <button type="button" class="btn btn-outline-secondary btn-sm np-quick-amount"
                                    data-amount="{{ $preset }}">R$ {{ $preset }}</button>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição <span class="text-muted">(opcional)</span></label>
                        <input type="text" name="description" maxlength="255"
                               value="{{ old('description') }}"
                               class="form-control @error('description') is-invalid @enderror"
                               placeholder="Ex.: pagamento do aluguel">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-left-right"></i> Transferir
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

<script>
    document.querySelectorAll('.np-quick-amount').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById('amount');
            input.value = parseFloat(btn.dataset.amount).toFixed(2);
            input.focus();
        });
    });
</script>

@endsection
