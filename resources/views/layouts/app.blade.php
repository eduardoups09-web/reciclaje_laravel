<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Inicio') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f5f7f5; }
        .card { border:none; border-radius:.6rem; }
        main { min-height:70vh; }
        .form-label { font-weight:500; margin-bottom:.2rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="bi bi-recycle"></i> {{ config('app.name') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('movimientos.*') ? 'active' : '' }}" href="{{ route('movimientos.index') }}">
                        <i class="bi bi-table"></i> Movimientos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('operaciones.*', 'produccion.*', 'calidad.*', 'mpimport.*', 'mpnacional.*', 'insumos.*', 'movimiento-detalle.*') ? 'active' : '' }}" href="{{ route('operaciones.index') }}">
                        <i class="bi bi-gear"></i> Operaciones
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('bodega.*', 'saldos.*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-box2-heart"></i> Inventario
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request()->routeIs('bodega.*') ? 'active' : '' }}" href="{{ route('bodega.index') }}"><i class="bi bi-truck"></i> Bodega / Despachos</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('saldos.*') ? 'active' : '' }}" href="{{ route('saldos.index') }}"><i class="bi bi-clipboard-data"></i> Saldos</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('reportes-gerenciales.*', 'pablo.*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-graph-up"></i> Reportes Gerenciales
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request()->routeIs('reportes-gerenciales.*') ? 'active' : '' }}" href="{{ route('reportes-gerenciales.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> Roberto</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('pablo.*') ? 'active' : '' }}" href="{{ route('pablo.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> Pablo</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container-fluid px-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('contenido')
</main>

<footer class="text-center text-muted py-4 mt-5 small">
    {{ config('app.name') }} &copy; {{ date('Y') }} · Base de datos: <code>reciclaje</code>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
