<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --np-dark: #0f1b2d;
            --np-blue: #2563eb;
            --np-blue-hover: #1d4ed8;
            --np-blue-light: #eff6ff;
            --np-light: #f8fafc;
            --np-text: #0f172a;
            --np-text-muted: #64748b;
            --np-border: #e2e8f0;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100vh;
            background: var(--np-light);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        .np-error-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            padding: 2rem 1.5rem;
        }

        .np-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            color: var(--np-text);
            font-size: 1.15rem;
            font-weight: 700;
        }

        .np-brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--np-blue);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
        }

        .np-error-card {
            background: #fff;
            border: 1px solid var(--np-border);
            border-radius: 16px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 440px;
            text-align: center;
        }

        .np-error-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--np-blue-light);
            color: var(--np-blue);
            font-size: 1.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .np-error-code {
            color: var(--np-text-muted);
            font-size: .85rem;
            font-weight: 700;
            letter-spacing: .12em;
            margin-bottom: .35rem;
        }

        .np-error-card h1 {
            color: var(--np-text);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: .6rem;
        }

        .np-error-card p {
            color: var(--np-text-muted);
            font-size: .95rem;
            line-height: 1.55;
            margin-bottom: 1.75rem;
        }

        .np-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: var(--np-blue);
            color: #fff;
            border-radius: 12px;
            padding: .75rem 1.5rem;
            font-weight: 600;
            font-size: .95rem;
            text-decoration: none;
            transition: background .15s;
        }

        .np-btn:hover { background: var(--np-blue-hover); color: #fff; }
    </style>
</head>
<body>
<div class="np-error-page">


    <div class="np-error-card">
        <span class="np-error-icon"><i class="bi @yield('icon')"></i></span>
        <div class="np-error-code">ERRO @yield('code')</div>
        <h1>@yield('headline')</h1>
        <p>@yield('message')</p>

        @auth
            <a href="{{ route('dashboard') }}" class="np-btn">
                <i class="bi bi-arrow-left"></i> Voltar ao painel
            </a>
        @else
            <a href="{{ route('login') }}" class="np-btn">
                <i class="bi bi-arrow-left"></i> Ir para o login
            </a>
        @endauth
    </div>

</div>
</body>
</html>
