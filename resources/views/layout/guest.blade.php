<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Wallet')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #084298);
            min-height: 100vh;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">

<div class="container" style="max-width: 420px;">

    <div class="text-center text-white mb-4">
        <h3><i class="bi bi-wallet2"></i> Carteira Financeira</h3>
    </div>

    <div class="card shadow">
        <div class="card-body p-4">

            @include('components.alerts')

            @yield('content')

        </div>
    </div>

</div>

</body>

</html>
