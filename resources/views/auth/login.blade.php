@extends('layout.guest')

@section('title', 'Entrar')

@section('content')

    <h1>Bom te ver!</h1>
    <p class="np-form-lead">Entre na sua conta para continuar.</p>

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="joao@email.com" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Mínimo 8 caracteres" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Lembrar de mim
                </label>
            </div>

            {{-- Recuperação de senha: fora do escopo deste desafio --}}
            <a href="#" class="np-link" title="Não implementado neste desafio">
                Esqueci a senha
            </a>
        </div>

        <button type="submit" class="np-btn">
            Entrar na conta <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <p class="text-center mt-4 mb-0" style="color:var(--np-text-muted);">
        Não tem conta? <a href="{{ route('register') }}" class="np-link">Criar conta agora!</a>
    </p>

@endsection