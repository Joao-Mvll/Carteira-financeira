<div class="np-sidebar">
    <div class="np-sidebar-top">
        <div class="np-sidebar-brand">
            <span class="np-sidebar-brand-icon"><i class="bi bi-wallet2"></i></span>
            <button type="button" id="npSidebarToggle" class="np-sidebar-toggle"
                    aria-label="Recolher menu" title="Recolher menu">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        </div>

        <a href="{{ route('dashboard') }}" title="Dashboard" data-np-sidebar-tooltip
           class="np-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="np-nav-label">Dashboard</span>
        </a>

        <a href="{{ route('wallet.deposit') }}" title="Depositar" data-np-sidebar-tooltip
           class="np-nav-link {{ request()->routeIs('wallet.deposit') ? 'active' : '' }}">
            <i class="bi bi-save"></i>
            <span class="np-nav-label">Depositar</span>
        </a>

        <a href="{{ route('wallet.transfer') }}" title="Transferir" data-np-sidebar-tooltip
           class="np-nav-link {{ request()->routeIs('wallet.transfer') ? 'active' : '' }}">
            <i class="bi bi-arrow-up-right-circle-fill"></i>
            <span class="np-nav-label">Transferir</span>
        </a>

        <a href="{{ route('wallet.statement') }}" title="Extrato" data-np-sidebar-tooltip
           class="np-nav-link {{ request()->routeIs('wallet.statement') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i>
            <span class="np-nav-label">Extrato</span>
        </a>

        <a href="{{ route('profile.edit') }}" title="Perfil" data-np-sidebar-tooltip
           class="np-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i>
            <span class="np-nav-label">Perfil</span>
        </a>

    </div>

    <div class="np-sidebar-bottom">
        <div class="np-user-mini">
            <span class="np-avatar">
                @php
                    $parts = explode(' ', trim(auth()->user()->name ?? 'U'));
                    $initials = mb_strtoupper(mb_substr($parts[0] ?? 'U', 0, 1) . mb_substr($parts[count($parts) - 1] ?? '', 0, 1));
                @endphp
                {{ $initials }}
            </span>
            <div class="np-nav-label">
                <div class="np-user-mini-name">{{ auth()->user()->name }}</div>
                <div class="np-user-mini-email">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}"
              data-confirm
              data-confirm-title="Sair da conta"
              data-confirm-message="Deseja sair da sua conta?"
              data-confirm-variant="danger"
              data-confirm-label="Sair">
            @csrf
            <button type="submit" class="np-logout-btn" title="Sair" data-np-sidebar-tooltip>
                <i class="bi bi-box-arrow-right"></i>
                <span class="np-nav-label">Sair</span>
            </button>
        </form>
    </div>
</div>
