@extends('layout.guest')

@section('title', 'Entrar')

@section('content')

    <h5 class="mb-4 text-center">Entrar</h5>

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Lembrar de mim</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>

    <p class="text-center mt-3 mb-0">
        Não tem conta? <a href="{{ route('register') }}">Cadastre-se</a>
    </p>

@endsection
