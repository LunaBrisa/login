<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | BH Uniformes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy:   #1a2845;
            --navy-2: #243660;
            --gold:   #c9a227;
            --gold-lt:#f5e9be;
            --cream:  #faf9f7;
            --border: #e0ddd6;
            --muted:  #888580;
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

        .header {
            position: fixed;
            top: 0; width: 100%;
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

        .logo span { color: var(--gold); }
        .logo img  { height: 44px; }

        .nav a {
            color: rgba(255,255,255,0.65);
            margin-left: 28px;
            text-decoration: none;
            font-size: 13px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            transition: color 0.2s;
        }

        .nav a:hover { color: #fff; }

        /* CARD — más ancha para el formulario de registro */
        .auth-card {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 960px;
            display: grid;
            grid-template-columns: 5fr 7fr;
            overflow: hidden;
            border: 0.5px solid var(--border);
        }

        .auth-panel {
            background: var(--navy);
            padding: 52px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-panel::before {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(201,162,39,0.1);
        }

        .auth-panel::after {
            content: '';
            position: absolute;
            bottom: -40px; left: -40px;
            width: 140px; height: 140px;
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

        .panel-brand span { color: var(--gold); }

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
            width: 36px; height: 2px;
            background: var(--gold);
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .panel-title {
            font-size: 21px;
            color: #fff;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .panel-sub {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            line-height: 1.75;
            position: relative;
            z-index: 1;
        }

        .panel-perks {
            margin-top: 36px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .perk {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12.5px;
            color: rgba(255,255,255,0.65);
        }

        .perk i {
            font-size: 14px;
            color: var(--gold);
            flex-shrink: 0;
        }

        /* FORMULARIO */
        .auth-form {
            padding: 44px 44px;
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
            margin-bottom: 28px;
        }

        .alert-error {
            background: #fff5f5;
            border-left: 3px solid #e24b4a;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #a32d2d;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .input-wrap .icon-left {
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
            padding: 11px 14px 11px 40px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--navy);
            background: var(--cream);
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }

        .input-wrap input:focus {
            border-color: var(--gold);
            background: #fff;
        }

        .input-wrap input.is-invalid { border-color: #e24b4a; }

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
            padding: 0;
            line-height: 1;
        }

        /* Barra de fortaleza de contraseña */
        .strength-bar {
            display: flex;
            gap: 4px;
            margin-top: 8px;
        }

        .strength-bar span {
            flex: 1;
            height: 3px;
            border-radius: 2px;
            background: var(--border);
            transition: background 0.3s;
        }

        .strength-bar span.active-1 { background: #e24b4a; }
        .strength-bar span.active-2 { background: #ef9f27; }
        .strength-bar span.active-3 { background: #639922; }
        .strength-bar span.active-4 { background: #1d9e75; }

        .btn-primary {
            width: 100%;
            padding: 13px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s;
            margin-top: 6px;
        }

        .btn-primary:hover { background: var(--navy-2); }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #b0ada8;
        }

        .form-footer a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
        }

        @media (max-width: 700px) {
            .auth-card { grid-template-columns: 1fr; }
            .auth-panel { display: none; }
            .auth-form { padding: 36px 24px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

    <header class="header">
        @if(file_exists(public_path('img/logo.png')))
            <a href="/"><img class="logo" src="{{ asset('img/logo.png') }}" alt="BH Uniformes"></a>
        @else
            <a href="/" class="logo">BH <span>Uniformes</span></a>
        @endif


    </header>

    <div class="auth-card">

        <!-- Panel izquierdo -->
        <div class="auth-panel">
            <div class="panel-brand">BH <span>Uniformes</span></div>
            <div class="panel-tag">Portal de gestión</div>
            <div class="panel-divider"></div>
            <div class="panel-title">Crea tu cuenta</div>
            <div class="panel-sub">Únete al sistema de gestión de BH Uniformes.</div>

            <div class="panel-perks">
                <div class="perk">
                    <i class="ti ti-clipboard-list" aria-hidden="true"></i>
                    Gestiona pedidos fácilmente
                </div>
                <div class="perk">
                    <i class="ti ti-users" aria-hidden="true"></i>
                    Administra tu cartera de clientes
                </div>
                <div class="perk">
                    <i class="ti ti-shirt" aria-hidden="true"></i>
                    Accede al catálogo completo
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="auth-form">
            <h1 class="form-heading">Crear cuenta</h1>
            <p class="form-sub">Completa los datos para registrarte</p>

            @if ($errors->any())
                <div class="alert-error">
                    <i class="ti ti-alert-circle" aria-hidden="true"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" novalidate>
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">Nombre</label>
                        <div class="input-wrap">
                            <i class="ti ti-user icon-left" aria-hidden="true"></i>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Juan"
                                autocomplete="given-name"
                                class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                                required>
                        </div>
                        @error('name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="apellido" class="form-label">Apellido</label>
                        <div class="input-wrap">
                            <i class="ti ti-user icon-left" aria-hidden="true"></i>
                            <input
                                type="text"
                                id="apellido"
                                name="apellido"
                                value="{{ old('apellido') }}"
                                placeholder="García"
                                autocomplete="family-name"
                                class="{{ $errors->has('apellido') ? 'is-invalid' : '' }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <div class="input-wrap">
                        <i class="ti ti-mail icon-left" aria-hidden="true"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="correo@ejemplo.com"
                            autocomplete="email"
                            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                            required>
                    </div>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group" style="display: none;">
                    <selecct id="rol" name="rol">
                        <option value="admin">Administrador</option>
                        <option value="user">Usuario</option>
                        <option value="guest">Invitado</option>
                    </select>
                </div>  

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-wrap">
                        <i class="ti ti-lock icon-left" aria-hidden="true"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password"
                            oninput="checkStrength(this.value)"
                            class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                            required>
                        <button type="button" class="toggle-eye" onclick="togglePassword('password', this)" aria-label="Mostrar contraseña">
                            <i class="ti ti-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="strength-bar">
                        <span id="s1"></span>
                        <span id="s2"></span>
                        <span id="s3"></span>
                        <span id="s4"></span>
                    </div>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                    <div class="input-wrap">
                        <i class="ti ti-lock-check icon-left" aria-hidden="true"></i>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Repite tu contraseña"
                            autocomplete="new-password"
                            required>
                        <button type="button" class="toggle-eye" onclick="togglePassword('password_confirmation', this)" aria-label="Mostrar contraseña">
                            <i class="ti ti-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Crear cuenta</button>
            </form>

            <p class="form-footer">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a>
            </p>
        </div>

    </div>

    <script>
        function togglePassword(fieldId, btn) {
            const input = document.getElementById(fieldId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ti ti-eye-off';
                btn.setAttribute('aria-label', 'Ocultar contraseña');
            } else {
                input.type = 'password';
                icon.className = 'ti ti-eye';
                btn.setAttribute('aria-label', 'Mostrar contraseña');
            }
        }

        function checkStrength(val) {
            const bars = [
                document.getElementById('s1'),
                document.getElementById('s2'),
                document.getElementById('s3'),
                document.getElementById('s4'),
            ];
            let score = 0;
            if (val.length >= 8)          score++;
            if (/[A-Z]/.test(val))         score++;
            if (/[0-9]/.test(val))         score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            bars.forEach((b, i) => {
                b.className = '';
                if (i < score) b.classList.add(`active-${score}`);
            });
        }
    </script>

   
</body>
</html>