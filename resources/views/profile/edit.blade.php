@extends('layout.app')

@section('title', 'Perfil')
@section('page-heading', 'Perfil')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow mb-4">
            <div class="card-header">Dados pessoais</div>
            <div class="card-body">

                <form method="POST" action="{{ route('profile.update') }}" novalidate>
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label" for="name">Nome completo</label>
                        <input type="text" name="name" id="name"
                               value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', $user->email) }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Salvar alterações
                    </button>
                </form>

            </div>
        </div>

        <div class="card shadow">
            <div class="card-header">Alterar senha</div>
            <div class="card-body">

                <form method="POST" action="{{ route('profile.password.update') }}" novalidate>
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label" for="current_password">Senha atual</label>
                        <input type="password" name="current_password" id="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Nova senha</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Mínimo de 8 caracteres, com letra maiúscula, minúscula e número.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirmar nova senha</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-lock"></i> Alterar senha
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection
