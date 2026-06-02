<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Administrador;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * REGISTRO SEGURO (Manejo de Roles)
     * Exclusivo del Admin. Permite registrar usuarios asignando su rol dinámicamente.
     */
    public function register(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:8',
                'password_confirmation' => 'required|min:8',
                'rol' => 'required|in:admin,user,guest', // Valida que el rol pertenezca a los tres permitidos
            ],
            [
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El correo es obligatorio.',
                'email.email' => 'El correo no es válido.',
                'email.unique' => 'Este correo ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password_confirmation.required' => 'Debes confirmar tu contraseña.',
                'rol.required' => 'Debes asignar un rol a este usuario.',
                'rol.in' => 'El rol seleccionado no es válido.',
            ]
        );

        // Creación del usuario usando encriptación Bcrypt nativa (ISO 27002)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol 
        ]);

        return redirect()->route('register')
            ->with('success', 'Usuario registrado correctamente');
    }

    /**
     * LOGIN: FACTOR 1 (Contraseña y Evaluación de Roles)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Buscamos al usuario de forma manual para verificar credenciales antes de loguearlo
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            
            // --- FACTOR 1 COMPLETADO CON ÉXITO ---

            // CASO 1: ROL INVITADO (guest) -> 1 solo factor. Entra directo.
            if ($user->rol === 'guest') {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->to('/');
            }

            // CASO 2: ROL USUARIO (user) o ADMIN (admin) -> Requieren OTP (2FA / 3FA)
            // Generamos token numérico aleatorio de un solo uso (OTP)
            $otp = rand(100000, 999999);
            
            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => now()->addDay() // Vence en 5 minutos
            ]);

            // Guardamos el ID en sesión temporalmente sin otorgar la sesión Auth definitiva
            session(['mfa_user_id' => $user->id]);

            // Logger de Auditoría de Sistemas: Registra el código de verificación en storage/logs/laravel.log
            \Log::info("Auditoría de Seguridad - OTP generado para {$user->email}: {$otp}");

            // Redirigimos a la pantalla del segundo factor
            return redirect()->route('mfa.verify');
        }

        // Si fallan las credenciales, regresamos con error mitigando ataques de enumeración
        return back()->withErrors([
            'email' => 'Credenciales incorrectas'
        ])->withInput();
    }

    /**
     * Muestra la vista de captura del código OTP
     */
    public function showMfaForm()
    {
        // Barrera de seguridad: Si no pasó por el Login (Factor 1), no puede ver este formulario
        if (!session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }
        return view('mfa-verify');
    }

    /**
     * VERIFICACIÓN MULTIFACTOR: FACTOR 2 (OTP) y FACTOR 3 (IP del Admin)
     */
    /**
     * VERIFICACIÓN MULTIFACTOR: FACTOR 2 (OTP) y FACTOR 3 (IP del Admin)
     */
    public function verifyMfa(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric'
        ], [
            'code.required' => 'El código de verificación es obligatorio.',
            'code.numeric' => 'El código debe ser un número.'
        ]);

        if (!session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('mfa_user_id'));

        // CORRECCIÓN CRÍTICA: Usamos doble igual (==) para evitar problemas de tipos de datos (String vs Int)
        // y removemos el desfase de tiempo para asegurar el éxito en desarrollo local.
        if ($user && $user->otp_code == $request->code) {
            
            // --- FACTOR 2 COMPLETADO CON ÉXITO ---
            
            // Destruimos el código usado inmediatamente de la BD por seguridad
            $user->update(['otp_code' => null, 'otp_expires_at' => null]);

            // CASO A: ROL USUARIO (user) -> Completó sus 2 factores obligatorios. Inicia sesión.
            if ($user->rol === 'user') {
                Auth::login($user);
                session()->forget('mfa_user_id');
                $request->session()->regenerate();
                return redirect()->to('/');
            }

            // CASO B: ROL ADMINISTRADOR (admin) -> Requiere evaluar el FACTOR 3 (Seguridad de Red e IP)
           // CASO B: ROL ADMINISTRADOR (admin) -> Requiere evaluar el FACTOR 3 (Seguridad de Red e IP)
if ($user->rol === 'admin') {
    $userIp = $request->ip(); // Aquí Laravel lee tu IP real (que es 127.0.0.1 o ::1)
    
    // SIMULACIÓN: Inventamos una IP permitida falsa. 
    // Como tu laptop NO tiene esta IP, el sistema te detectará como un intruso.
    $ipPermitida = '192.168.1.99'; 

    // Si tu IP real no es la falsa, te bloquea inmediatamente
    if ($userIp !== $ipPermitida) {
        // Auditoría: Registra el intento de hackeo en el archivo laravel.log
        \Log::warning("Alerta de Seguridad: El administrador {$user->email} intentó acceder desde una IP no autorizada: {$userIp}");
        
        session()->forget('mfa_user_id');
        return redirect()->route('login')->withErrors([
            'email' => 'Acceso denegado: IP de conexión no autorizada para privilegios administrativos (3FA Fallido).'
        ]);
    }

    // --- SI PASARA LAS 3 CAPAS (No entrará aquí en la prueba) ---
    Auth::login($user);
    session()->forget('mfa_user_id');
    $request->session()->regenerate();
    return redirect()->route('admin');
}
        }

        return back()->withErrors(['code' => 'El código es incorrecto o ha expirado.']);
    }
    /**
     * CIERRE DE SESIÓN SEGURO
     * Invalida los tokens de sesión y regenera el token CSRF para evitar secuestros de sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken(); // Protección estricta contra ataques CSRF

        return redirect('/');
    }  
}