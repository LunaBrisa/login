<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\AuditLog;

class UserController extends Controller
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(Request $request)
    {
        // Sanitización de datos
        $request->merge([
            'name' => strip_tags(trim($request->name)),
            'email' => strtolower(trim($request->email)),
        ]);

        // Validación
        $request->validate([
            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email',

            'password' => [
                'required',
                'confirmed',
                Password::min(20)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],

            'password_confirmation' => 'required',

            'g-recaptcha-response' => 'required'

        ], [
            'name.required' => 'El nombre es obligatorio.',

            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no es válido.',
            'email.unique' => 'Este correo ya está registrado.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',

            'g-recaptcha-response.required' =>
                'Por favor, completa el reCAPTCHA.',
        ]);

        // Verificar reCAPTCHA
        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip()
            ]
        );

        if (!$response->json('success')) {

            return back()
                ->withErrors([
                    'captcha' =>
                    'La verificación reCAPTCHA ha fallado.'
                ])
                ->withInput();
        }

        // Crear usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

            // guest se crea desde interfaz
            'rol' => 'guest'
        ]);

        // Log de desarrollo
        Log::info('Nuevo usuario registrado', [
            'email' => $user->email,
            'rol' => $user->rol,
            'ip' => $request->ip()
        ]);

        // Log de auditoría
        AuditLog::create([
            'user_id' => $user->id,
            'accion' => 'REGISTRO',
            'email' => $user->email,
            'ip' => $request->ip(),
            'descripcion' => 'Nuevo usuario registrado'
        ]);

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Usuario registrado correctamente.'
            );
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request)
    {
        // Sanitización
        $request->merge([
            'email' => strtolower(trim($request->email))
        ]);

        // Validación
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required'
        ], [

            'email.required' =>
                'El correo es obligatorio.',

            'email.email' =>
                'El correo no es válido.',

            'password.required' =>
                'La contraseña es obligatoria.',

            'g-recaptcha-response.required' =>
                'Completa el reCAPTCHA.'
        ]);

        // Log desarrollo
        Log::info('Intento de login', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'hora' => now()
        ]);

        // Validar captcha
        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip()
            ]
        );

        if (!$response->json('success')) {

            AuditLog::create([
                'accion' => 'LOGIN_FALLIDO',
                'email' => $request->email,
                'ip' => $request->ip(),
                'descripcion' =>
                    'Falló reCAPTCHA'
            ]);

            return back()
                ->withErrors([
                    'captcha' =>
                        'La verificación reCAPTCHA falló.'
                ])
                ->withInput();
        }

        // Buscar usuario
        $user = User::where(
            'email',
            $request->email
        )->first();

        // Verificar credenciales
        if (!$user || !Hash::check(
            $request->password,
            $user->password
        )) {

            AuditLog::create([
                'accion' => 'LOGIN_FALLIDO',
                'email' => $request->email,
                'ip' => $request->ip(),
                'descripcion' =>
                    'Credenciales incorrectas'
            ]);

            return back()
                ->withErrors([
                    'email' =>
                        'Correo o contraseña incorrectos.'
                ])
                ->withInput();
        }

        // Generar OTP
        $otp = rand(100000, 999999);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' =>
                now()->addMinutes(5)
        ]);

        session([
            'mfa_user_id' => $user->id
        ]);

        Log::info(
            "OTP generado para {$user->email}",
            [
                'otp' => $otp
            ]
        );

        AuditLog::create([
            'user_id' => $user->id,
            'accion' => 'OTP_GENERADO',
            'email' => $user->email,
            'ip' => $request->ip(),
            'descripcion' =>
                'Código MFA generado'
        ]);

        return redirect()
            ->route('mfa.verify');
    }

    /**
     * Mostrar formulario MFA
     */
    public function showMfaForm()
    {
        if (!session()->has('mfa_user_id')) {

            return redirect()
                ->route('login');
        }

        return view('mfa-verify');
    }

    /**
     * Verificar MFA
     */
    public function verifyMfa(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric'
        ], [

            'code.required' =>
                'El código es obligatorio.',

            'code.numeric' =>
                'El código debe ser numérico.'
        ]);

        if (!session()->has('mfa_user_id')) {

            return redirect()
                ->route('login');
        }

        $user = User::find(
            session('mfa_user_id')
        );

        if (
            !$user ||
            $user->otp_code != $request->code ||
            now()->greaterThan(
                $user->otp_expires_at
            )
        ) {

            AuditLog::create([
                'accion' => 'MFA_FALLIDO',
                'email' => $user?->email,
                'ip' => $request->ip(),
                'descripcion' =>
                    'Código MFA incorrecto o expirado'
            ]);

            return back()->withErrors([
                'code' =>
                    'El código es incorrecto o expiró.'
            ]);
        }

        // Restricción IP para admin
        if ($user->rol === 'admin') {

            $ipPermitida = '127.0.0.1';

            if (
                $request->ip() !== $ipPermitida &&
                $request->ip() !== '::1'
            ) {

                Log::warning(
                    "Admin {$user->email}
                    intentó entrar desde
                    IP no autorizada"
                );

                AuditLog::create([
                    'user_id' => $user->id,
                    'accion' => 'ADMIN_IP_DENEGADA',
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'descripcion' =>
                        'Intento admin desde IP no autorizada'
                ]);

                session()->forget(
                    'mfa_user_id'
                );

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' =>
                            'IP no autorizada.'
                    ]);
            }
        }

        // Limpiar OTP
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        // Crear sesión SOLO AQUÍ
        Auth::login($user);

        $request
            ->session()
            ->regenerate();

        session()->forget(
            'mfa_user_id'
        );

        // Auditoría login exitoso
        AuditLog::create([
            'user_id' => $user->id,
            'accion' => 'LOGIN_EXITOSO',
            'email' => $user->email,
            'ip' => $request->ip(),
            'descripcion' =>
                'Inicio de sesión correcto'
        ]);

        // Redirección por rol
        if ($user->rol === 'admin') {

            return redirect()
                ->route('admin');
        }

        return redirect('/');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id,
            'accion' => 'LOGOUT',
            'email' => $user?->email,
            'ip' => $request->ip(),
            'descripcion' =>
                'Cierre de sesión'
        ]);

        Auth::logout();

        $request->session()->invalidate();

        $request
            ->session()
            ->regenerateToken();

        return redirect('/login');
    }
}