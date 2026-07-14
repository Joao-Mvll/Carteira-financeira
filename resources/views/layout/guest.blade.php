<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NovoPay')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --np-dark: #0f1b2d;
            --np-dark-2: #0a1420;
            --np-blue: #2563eb;
            --np-blue-hover: #1d4ed8;
            --np-light: #f8fafc;
            --np-text: #0f172a;
            --np-text-muted: #64748b;
            --np-border: #e2e8f0;
            --np-green: #22c55e;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        .np-split {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ---- Painel esquerdo (escuro) ---- */
        .np-left {
            flex: 1 1 50%;
            background: linear-gradient(160deg, var(--np-dark) 0%, var(--np-dark-2) 55%, #0d2b28 100%);
            color: #fff;
            padding: 3rem 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
        }

        .np-brand {
            display: flex;
            align-items: center;
            gap: .65rem;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .np-brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--np-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .np-headline {
            font-size: 2.6rem;
            font-weight: 700;
            line-height: 1.15;
            margin-bottom: 1rem;
        }

        .np-subtitle {
            color: #94a3b8;
            font-size: 1.05rem;
            max-width: 460px;
            margin-bottom: 2.5rem;
            line-height: 1.5;
        }

        .np-cards {
            display: flex;
            gap: 1rem;
            max-width: 560px;
        }

        .np-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 1.3rem .8rem;
            text-align: center;
        }

        .np-card i {
            color: #60a5fa;
            font-size: 1.4rem;
            display: block;
            margin-bottom: .6rem;
        }

        .np-card span {
            font-size: .85rem;
            font-weight: 600;
        }

        .np-footer-note {
            color: #64748b;
            font-size: .9rem;
        }

        /* ---- Painel direito (claro / formulário) ---- */
        .np-right {
            flex: 1 1 50%;
            background: var(--np-light);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            min-height: 100vh;
        }

        .np-form-wrap {
            width: 100%;
            max-width: 400px;
        }

        .np-form-wrap h1 {
            color: var(--np-text);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: .35rem;
        }

        .np-form-lead {
            color: var(--np-text-muted);
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .np-form-wrap label {
            color: var(--np-text);
            font-weight: 600;
            font-size: .9rem;
            margin-bottom: .4rem;
            display: inline-block;
        }

        .np-form-wrap .form-control {
            border: 1px solid var(--np-border);
            border-radius: 12px;
            padding: .75rem 1rem;
            font-size: .95rem;
        }

        .np-form-wrap .form-control:focus {
            border-color: var(--np-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .np-btn {
            background: var(--np-blue);
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: .85rem;
            font-weight: 600;
            width: 100%;
            font-size: .95rem;
            transition: background .15s;
        }

        .np-btn:hover { background: var(--np-blue-hover); color: #fff; }

        .np-link {
            color: var(--np-blue);
            font-weight: 600;
            text-decoration: none;
        }

        .np-link:hover { text-decoration: underline; }

        @media (max-width: 860px) {
            .np-left { display: none; }
            .np-right { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
<div class="np-split">

    <div class="np-left">
        <div class="np-brand">
            <span class="np-brand-icon"><i class="bi bi-wallet2"></i></span>
            NovoPay
        </div>

        <div>
            <h2 class="np-headline">Seu dinheiro,<br>no controle certo.</h2>
            <p class="np-subtitle">
                Gerencie transferências, pagamentos e investimentos em uma
                plataforma segura e sempre disponível.
            </p>

            <div class="np-cards">
                <div class="np-card">
                    <i class="bi bi-shield-check"></i>
                    <span>Segurança</span>
                </div>
                <div class="np-card">
                    <i class="bi bi-clock"></i>
                    <span>24h / 7 dias</span>
                </div>
                <div class="np-card">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Rendimento</span>
                </div>
            </div>
        </div>

        <div class="np-footer-note">
        </div>
    </div>

    <div class="np-right">
        <div class="np-form-wrap">
            @include('components.alerts')
            @yield('content')
        </div>
    </div>

</div>
</body>
</html>