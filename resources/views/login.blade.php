<!DOCTYPE html>
<html lang="es">
<head>
@livewireStyles
<meta charset="UTF-8">
<title>Login | BH Uniformes</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;}

:root{
    --azul-marino:#1a2845;
    --azul-secundario:#2c4a7c;
    --amarillo:#ffd700;
}

/* BODY */
body{
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(135deg,var(--azul-marino),var(--azul-secundario));
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* HEADER */
.header{
    position:fixed;
    top:0;
    width:100%;
    background:linear-gradient(to right,#000 20%, var(--azul-marino));
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 5%;
}

.logo img{height:65px;}

.nav a{
    color:white;
    margin-left:20px;
    text-decoration:none;
}

/* CONTENEDOR */
.login-container{
    margin-top:100px;
    background:#fff;
    border-radius:25px;
    box-shadow:0 25px 60px rgba(0,0,0,0.3);
    max-width:900px;
    width:100%;
    display:grid;
    grid-template-columns:1fr 1fr;
    overflow:hidden;
}

/* IZQUIERDA */
.login-info{
    background:linear-gradient(135deg,var(--azul-marino),var(--azul-secundario));
    color:white;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
    padding:50px;
}

.login-info h2{
    font-size:2.4rem;
    margin-bottom:15px;
}

.login-info p{
    font-size:1.2rem;
    opacity:.9;
    max-width:280px;
}

/* DERECHA */
.login-form{
    padding:50px;
}

.subtitle{
    margin-bottom:25px;
    color:#666;
}

/* INPUTS */
.form-group{margin-bottom:20px;}

.input-wrapper{
    position:relative;
}

.input-wrapper input{
    width:100%;
    padding:12px 45px 12px 40px;
    border-radius:10px;
    border:2px solid #ddd;
}

.input-icon{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
}

.toggle-password{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
}

/* BOTÓN */
.btn{
    width:100%;
    padding:15px;
    border:none;
    border-radius:10px;
    background:var(--amarillo);
    font-weight:bold;
    cursor:pointer;
}

/* ALERTAS */
.alert{
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
}

.error{
    background:#ffebee;
    color:#c62828;
}

/* RESPONSIVE */
@media(max-width:768px){
    .login-container{
        grid-template-columns:1fr;
    }
    .login-info{display:none;}
}
</style>
</head>

<body>

@auth
<script>
    window.location.href = "/admin";
</script>
@endauth

<header class="header">
    <div class="logo">
        <img src="{{ asset('img/logo.png') }}">
    </div>

    <nav class="nav">
        <a href="/">Inicio</a>
        <a href="/catalogo">Catálogo</a>
        <a href="/nosotros">Nosotros</a>

        @guest
            <a href="/login">Login</a>
        @endguest
    </nav>
</header>

<div class="login-container">

    <!-- IZQUIERDA -->
    <div class="login-info">
        <h2>BH Uniformes</h2>
        <p>Inicia sesión para gestionar tu sistema</p>
    </div>

    <!-- DERECHA -->
    <div class="login-form">
        <h1>Login</h1>
        <p class="subtitle">Accede a tu cuenta</p>

        @if ($errors->any())
        <div class="alert error">
            {{ $errors->first('email') }}
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Correo</label>
                <div class="input-wrapper">
                    <span class="input-icon">📧</span>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password" required>
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
            </div>

            <button class="btn">Iniciar sesión</button>
        </form>
    </div>

</div>

<script>
function togglePassword(){
    const input = document.getElementById("password");
    const icon = document.querySelector(".toggle-password");

    if(input.type === "password"){
        input.type = "text";
        icon.textContent = "🙈";
    }else{
        input.type = "password";
        icon.textContent = "👁️";
    }
}
</script>
@livewireScripts
</body>
</html>