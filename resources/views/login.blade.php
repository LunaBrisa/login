<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | BH Uniformes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <!-- Google reCAPTCHA -->
    <script
        src="https://www.google.com/recaptcha/api.js"
        async
        defer>
    </script>

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --navy: #1a2845;
            --navy-2: #243660;
            --gold: #c9a227;
            --gold-lt: #f5e9be;
            --cream: #faf9f7;
            --border: #e0ddd6;
            --muted: #888580;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f4f2ee;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 100px 1rem 2rem;
        }

        /* HEADER */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background: var(--navy);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 5%;
            height: 64px;
            z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: #fff;
            letter-spacing: 0.02em;
            text-decoration: none;
        }

        .logo span {
            color: var(--gold);
        }

        .logo img {
            height: 44px;
        }

        /* CARD */
        .auth-card {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            border: 0.5px solid var(--border);
        }

        /* PANEL IZQUIERDO */
        .auth-panel {
            background: var(--navy);
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-panel::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(201,162,39,0.1);
        }

        .auth-panel::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(201,162,39,0.06);
        }

        .panel-brand {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            color: #fff;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .panel-brand span {
            color: var(--gold);
        }

        .panel-tag {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: rgba(255,255,255,0.4);
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .panel-divider {
            width: 36px;
            height: 2px;
            background: var(--gold);
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .panel-title {
            font-size: 22px;
            color: #fff;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .panel-sub {
            font-size: 13.5px;
            color: rgba(255,255,255,0.5);
            line-height: 1.75;
            position: relative;
            z-index: 1;
        }

        /* FORM */
        .auth-form {
            padding: 48px 44px;
        }

        .form-heading {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--navy);
            margin-bottom: 4px;
        }

        .form-sub {
            font-size: 13.5px;
            color: var(--muted);
            margin-bottom: 32px;
        }

        /* ALERT */
        .alert-error {
            background: #fff5f5;
            border-left: 3px solid #e24b4a;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #a32d2d;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* GROUPS */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            color: #c0bdb7;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--navy);
            background: var(--cream);
            outline: none;
            transition: border-color .2s, background .2s;
        }

        .input-wrap input:focus {
            border-color: var(--gold);
            background: #fff;
        }

        .is-invalid {
            border-color: #e24b4a !important;
        }

        .field-error {
            font-size: 11.5px;
            color: #a32d2d;
            margin-top: 5px;
        }

        .toggle-eye {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 15px;
            color: #c0bdb7;
            background: none;
            border: none;
        }

        /* BUTTON */
        .btn-primary {
            width: 100%;
            padding: 13px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background .2s;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background: var(--navy-2);
        }

        .form-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: #b0ada8;
        }

        .form-footer a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
        }

        @media (max-width: 680px) {
            .auth-card {
                grid-template-columns: 1fr;
            }

            .auth-panel {
                display: none;
            }

            .auth-form {
                padding: 36px 28px;
            }
        }
    </style>
</head>

<body>

    <header class="header">
        @if(file_exists(public_path('img/logo.png')))
            <a href="/">
                <img
                    class="logo"
                    src="{{ asset('img/logo.png') }}"
                    alt="BH Uniformes">
            </a>
        @else
            <a href="/" class="logo">
                BH <span>Uniformes</span>
            </a>
        @endif
    </header>

    <div class="auth-card">

        <!-- PANEL IZQUIERDO -->
        <div class="auth-panel">
            <div class="panel-brand">
                BH <span>Uniformes</span>
            </div>

            <div class="panel-tag">
                Portal de gestión
            </div>

            <div class="panel-divider"></div>

            <div class="panel-title">
                Bienvenido de vuelta
            </div>

            <div class="panel-sub">
                Accede a tu cuenta para gestionar pedidos,
                clientes y catálogo de uniformes.
            </div>
        </div>

        <!-- FORM -->
        <div class="auth-form">

            <h1 class="form-heading">
                Iniciar sesión
            </h1>

            <p class="form-sub">
                Ingresa tus credenciales para continuar
            </p>

            @if(session('success'))
                <div class="alert-error"
                    style="background:#edfdf3;
                    border-left-color:#22c55e;
                    color:#166534;">

                    <i class="ti ti-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error">
                    <i class="ti ti-alert-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                action="{{ route('login.store') }}"
                method="POST"
                novalidate>

                @csrf

                <!-- EMAIL -->
                <div class="form-group">
                    <label
                        for="email"
                        class="form-label">

                        Correo electrónico
                    </label>

                    <div class="input-wrap">

                        <i class="ti ti-mail icon-left"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="correo@ejemplo.com"
                            autocomplete="email"
                            class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
                    </div>

                    @error('email')
                        <p class="field-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label
                        for="password"
                        class="form-label">

                        Contraseña
                    </label>

                    <div class="input-wrap">

                        <i class="ti ti-lock icon-left"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="{{ $errors->has('password') ? 'is-invalid' : '' }}">

                        <button
                            type="button"
                            class="toggle-eye"
                            onclick="togglePassword('password', this)"
                            aria-label="Mostrar contraseña">

                            <i class="ti ti-eye"></i>
                        </button>
                    </div>

                    @error('password')
                        <p class="field-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- RECAPTCHA -->
                <div
                    style="
                        display:flex;
                        justify-content:center;
                        margin-bottom:20px;">

                    <div
                        class="g-recaptcha"
                        data-sitekey="{{ config('services.recaptcha.site_key') }}">
                    </div>
                </div>

                @error('g-recaptcha-response')
                    <p
                        class="field-error"
                        style="text-align:center;">

                        {{ $message }}
                    </p>
                @enderror

                <!-- BUTTON -->
                <button
                    type="submit"
                    class="btn-primary">

                    Iniciar sesión
                </button>

            </form>

            <p class="form-footer">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}">
                    Regístrate aquí
                </a>
            </p>

        </div>
    </div>

    <script>
        function togglePassword(fieldId, btn) {

            const input =
                document.getElementById(fieldId);

            const icon =
                btn.querySelector('i');

            if (input.type === 'password') {

                input.type = 'text';

                icon.className =
                    'ti ti-eye-off';

                btn.setAttribute(
                    'aria-label',
                    'Ocultar contraseña'
                );

            } else {

                input.type = 'password';

                icon.className =
                    'ti ti-eye';

                btn.setAttribute(
                    'aria-label',
                    'Mostrar contraseña'
                );
            }
        }
    </script>

</body>
</html>