<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0,0,0,.1);
            text-align: center;
            width: 400px;
        }

        h1 {
            color: #333;
            margin-bottom: 15px;
        }

        .user-name {
            font-size: 22px;
            font-weight: bold;
            color: #007bff;
        }

        .role {
            color: #666;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .logout-btn:hover {
            opacity: .9;
        }
    </style>
</head>
<body>

    <div class="card">

        <h1>
            Bienvenido al Panel de Invitado
        </h1>

        <p>
            Hola,
            <span class="user-name">
                {{ auth()->user()->name }}
            </span>
        </p>

        <p class="role">
            Rol:
            {{ auth()->user()->rol }}
        </p>

        <form 
            action="{{ route('logout') }}" 
            method="POST"
            onsubmit="return confirmarLogout()"
        >
            @csrf

            <button
                type="submit"
                class="logout-btn"
            >
                Cerrar sesión
            </button>
        </form>

    </div>

    <script>
        function confirmarLogout() {

            return confirm(
                '¿Seguro que deseas cerrar sesión?'
            );
        }
    </script>

</body>
</html>