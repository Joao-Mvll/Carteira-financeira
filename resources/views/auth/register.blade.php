@extends('layout.guest')

@section('title', 'Cadastro')

@section('content')

    <h5 class="mb-4 text-center">Criar conta</h5>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="form-control" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirmar senha</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
    </form>

    <p class="text-center mt-3 mb-0">
        Já tem conta? <a href="{{ route('login') }}">Entrar</a>
    </p>

@endsection
