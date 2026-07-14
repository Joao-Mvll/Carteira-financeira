<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Wallet')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>

        body{
            background:#f4f6f9;
        }

        .sidebar{
            width:240px;
            min-height:100vh;
            background:#212529;
        }

        .sidebar a{
            color:#ced4da;
            text-decoration:none;
            display:block;
            padding:14px 20px;
        }

        .sidebar a:hover{
            background:#343a40;
            color:white;
        }

        .content{
            flex:1;
        }

        .card-balance{
            background:linear-gradient(135deg,#0d6efd,#084298);
            color:white;
        }

    </style>

</head>

<body>

<div class="d-flex">

    @include('components.sidebar')

    <div class="content">

        @include('components.navbar')

        <div class="container py-4">

            @include('components.alerts')

            @yield('content')

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>