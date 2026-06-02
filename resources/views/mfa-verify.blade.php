<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación Multifactor | BH Uniformes</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #1a2845; --gold: #c9a227; --cream: #faf9f7; --border: #e0ddd6; }
        body { font-family: 'DM Sans', sans-serif; background: #f4f2ee; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .card { background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 25px 60px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; border: 0.5px solid var(--border); }
        .title { font-family: 'Playfair Display', serif; font-size: 24px; color: var(--navy); margin-bottom: 10px; }
        .text { font-size: 14px; color: #666; margin-bottom: 25px; line-height: 1.5; }
        .input-code { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 10px; font-size: 22px; text-align: center; letter-spacing: 5px; font-family: monospace; background: var(--cream); outline: none; box-sizing: border-box; }
        .input-code:focus { border-color: var(--gold); background: #fff; }
        .btn { width: 100%; padding: 14px; background: var(--navy); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; margin-top: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .btn:hover { background: #243660; }
        .error { color: #a32d2d; background: #fff5f5; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: left; border-left: 3px solid #e24b4a; }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="title">Verificación de Seguridad</h1>
        <p class="text">Tu rol administrativo requiere un segundo factor de autenticación. Introduce el código de 6 dígitos.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('mfa.store') }}" method="POST">
            @csrf
            <input type="text" name="code" class="input-code" placeholder="000000" maxlength="6" required autocomplete="off">
            <button type="submit" class="btn">Verificar Código</button>
        </form>
    </div>
</body>
</html>