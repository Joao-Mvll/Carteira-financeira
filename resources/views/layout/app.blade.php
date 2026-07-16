<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        if (localStorage.getItem('np_sidebar_collapsed') === '1') {
            document.documentElement.classList.add('np-sidebar-collapsed');
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --np-dark: #0f1b2d;
            --np-blue: #2563eb;
            --np-blue-light: #eff6ff;
            --np-light: #f8fafc;
            --np-text: #0f172a;
            --np-text-muted: #64748b;
            --np-border: #e2e8f0;
            --np-green: #22c55e;
            --np-green-light: #ecfdf5;
            --np-red: #ef4444;
            --np-red-light: #fef2f2;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--np-light);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            margin: 0;
        }

        .np-sidebar {
            width: 260px;
            height: 100vh;
            position: sticky;
            top: 0;

            background: var(--np-dark);

            display: flex;
            flex-direction: column;
            justify-content: space-between;

            flex-shrink: 0;
            overflow-y: auto;
        }

        .np-sidebar-top { padding: 1.5rem 1.25rem; }

        .np-sidebar-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            color: #fff;
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 2rem;
        }

        .np-sidebar-brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--np-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .np-nav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            color: #94a3b8;
            text-decoration: none;
            padding: .7rem .9rem;
            border-radius: 10px;
            font-size: .92rem;
            font-weight: 500;
            margin-bottom: .3rem;
        }

        .np-nav-link:hover { background: rgba(255,255,255,.05); color: #fff; }

        .np-nav-link.active { background: var(--np-blue); color: #fff; }

        .np-nav-link.disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .np-nav-link.disabled:hover { background: none; color: #94a3b8; }

        .np-sidebar-bottom {
            padding: 1.1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .np-user-mini {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: .8rem;
        }

        .np-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--np-blue);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .np-user-mini-name { color: #fff; font-size: .88rem; font-weight: 600; line-height: 1.2; }
        .np-user-mini-email { color: #64748b; font-size: .76rem; }

        .np-logout-btn {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #94a3b8;
            background: none;
            border: none;
            font-size: .85rem;
            font-weight: 500;
            padding: 0;
        }

        .np-logout-btn:hover { color: #fff; }

        /* Sidebar recolhível — a classe é aplicada no <html> pelo script
           anti-flash no <head> e alternada pelo módulo sidebar do app.js */
        .np-sidebar { transition: width .15s ease; }

        .np-sidebar-toggle {
            background: none;
            border: none;
            color: #94a3b8;
            margin-left: auto;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .np-sidebar-toggle:hover { background: rgba(255,255,255,.08); color: #fff; }

        html.np-sidebar-collapsed .np-sidebar { width: 78px; }
        html.np-sidebar-collapsed .np-sidebar-top,
        html.np-sidebar-collapsed .np-sidebar-bottom { padding-left: .7rem; padding-right: .7rem; }
        html.np-sidebar-collapsed .np-sidebar-brand { flex-direction: column; gap: .8rem; }
        html.np-sidebar-collapsed .np-sidebar-toggle { margin-left: 0; }
        html.np-sidebar-collapsed .np-nav-label { display: none; }
        html.np-sidebar-collapsed .np-nav-link { justify-content: center; padding: .7rem .5rem; }
        html.np-sidebar-collapsed .np-user-mini { justify-content: center; }
        html.np-sidebar-collapsed .np-logout-btn { justify-content: center; width: 100%; }

        .np-content { flex: 1; min-width: 0; }

        .np-topbar {
            background: #fff;
            border-bottom: 1px solid var(--np-border);
            padding: 1.1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .np-topbar h4 { margin: 0; font-weight: 700; color: var(--np-text); }

        .np-topbar-right { display: flex; align-items: center; gap: 1.2rem; }

        .np-bell {
            color: var(--np-text-muted);
            font-size: 1.2rem;
            position: relative;
        }

        .np-bell .dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--np-blue);
        }

        .np-topbar-user { display: flex; align-items: center; gap: .6rem; }
        .np-topbar-user-name { font-size: .88rem; font-weight: 600; color: var(--np-text); line-height: 1.2; }
        .np-topbar-user-role { font-size: .76rem; color: var(--np-text-muted); }

        /* Texto longo com clamp de 3 linhas + "expandir" (ver [data-np-expand] no app.js) */
        .np-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }
        .np-clamp.np-expanded { display: block; overflow: visible; }
    </style>
</head>
<body>
<div class="d-flex">
    @include('components.sidebar')
    <div class="np-content">
        @include('components.navbar')
        <div class="container-fluid px-4 py-4">
            @include('components.alerts')
            @yield('content')
        </div>
    </div>
</div>
@include('components.confirm-modal')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}?v=1"></script>
</body>
</html>