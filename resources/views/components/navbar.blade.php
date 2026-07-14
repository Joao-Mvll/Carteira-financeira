<nav class="navbar navbar-expand-lg bg-white shadow-sm">

    <div class="container-fluid">

        <span class="navbar-brand fw-bold">
            Carteira Financeira
        </span>

        <div class="ms-auto">

            <span class="me-3">

                <i class="bi bi-person-circle"></i>

                {{ auth()->user()->name ?? 'Visitante' }}

            </span>

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Logout
                </button>
            </form>

        </div>

    </div>

</nav>