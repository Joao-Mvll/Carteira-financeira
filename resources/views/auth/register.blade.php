@extends('layout.guest')

@section('title', 'Criar conta')

@section('content')

    <h1>Criar conta</h1>
    <p class="np-form-lead">Preencha os dados abaixo para começar.</p>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label">Nome completo</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="João da Silva" required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="joao@email.com" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Crie uma senha segura" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Mínimo de 8 caracteres, com letra maiúscula, minúscula e número.</div>
        </div>

        <div class="mb-4">
            <label class="form-label">Confirmar senha</label>
            <input type="password" name="password_confirmation"
                   class="form-control" placeholder="Repita a senha" required>
        </div>

        <button type="submit" class="np-btn">
            Criar conta grátis <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <p class="text-center mt-4 mb-0" style="color:var(--np-text-muted);">
        Já tem conta? <a href="{{ route('login') }}" class="np-link">Entrar</a>
    </p>

@endsection