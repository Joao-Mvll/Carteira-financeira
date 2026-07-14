<div class="np-topbar">
    <h4>@yield('page-heading', 'Painel')</h4>

    <div class="np-topbar-right">

        <div class="np-topbar-user">
            <span class="np-avatar">
                @php
                    $parts = explode(' ', trim(auth()->user()->name ?? 'U'));
                    $initials = mb_strtoupper(mb_substr($parts[0] ?? 'U', 0, 1) . mb_substr($parts[count($parts) - 1] ?? '', 0, 1));
                @endphp
                {{ $initials }}
            </span>
            <div>
                <div class="np-topbar-user-name">{{ auth()->user()->name }}</div>
            </div>
        </div>
    </div>
</div>