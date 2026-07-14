<div class="np-sidebar">
    <div class="np-sidebar-top">
        <div class="np-sidebar-brand">
            <span class="np-sidebar-brand-icon"><i class="bi bi-wallet2"></i></span>
        </div>

        <a href="{{ route('dashboard') }}"
           class="np-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            Dashboard
        </a>

        <a href="{{ route('wallet.deposit') }}"
           class="np-nav-link {{ request()->routeIs('wallet.deposit') ? 'active' : '' }}">
            <i class="bi bi-save"></i>
            Depositar
        </a>

        <a href="{{ route('wallet.transfer') }}"
           class="np-nav-link {{ request()->routeIs('wallet.transfer') ? 'active' : '' }}">
            <i class="bi bi-arrow-up-right-circle-fill"></i>
            Transferir
        </a>

        <a href="{{ route('wallet.statement') }}"
           class="np-nav-link {{ request()->routeIs('wallet.statement') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i>
            Extrato
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
            <div>
                <div class="np-user-mini-name">{{ auth()->user()->name }}</div>
                <div class="np-user-mini-email">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="np-logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                Sair
            </button>
        </form>
    </div>
</div>