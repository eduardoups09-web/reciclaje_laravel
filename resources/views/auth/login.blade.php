<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center" style="margin-top:8vh;">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-recycle text-success" style="font-size:3rem;"></i>
                        <h4 class="mt-2 mb-0">{{ config('app.name') }}</h4>
                        <small class="text-muted">Ingresa tus credenciales</small>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                    @endif

                    <form method="post" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Usuario o correo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="login" value="{{ old('login') }}" class="form-control" autofocus required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Recordarme</label>
                        </div>
                        <button class="btn btn-success w-100"><i class="bi bi-box-arrow-in-right"></i> Entrar</button>
                    </form>
                </div>
            </div>
            <p class="text-center text-muted small mt-3">Sistema sobre base de datos <code>reciclaje</code></p>
        </div>
    </div>
</div>
</body>
</html>
