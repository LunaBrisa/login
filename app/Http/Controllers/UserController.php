<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->merge([
                'name' => strip_tags(trim($request->name)),
                'apellido' => strip_tags(trim($request->apellido)),
                'email' => strtolower(trim($request->email)),
            ]);

            Log::debug('Datos sanitizados para registro', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            $request->validate([
                'name' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(20)->mixedCase()->numbers()->symbols()
                ],
                'password_confirmation' => 'required',
                'g-recaptcha-response' => 'required'
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'apellido.required' => 'El apellido es obligatorio.',
                'email.required' => 'El correo es obligatorio.',
                'email.email' => 'El correo no es válido.',
                'email.unique' => 'Este correo ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'g-recaptcha-response.required' => 'Completa el reCAPTCHA.'
            ]);

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip(),
            ]);

            $recaptchaData = $response->json();

            if (!($recaptchaData['success'] ?? false)) {
                Log::warning('Registro fallido por reCAPTCHA', [
                    'ip' => $request->ip(),
                    'email' => $request->email,
                    'errors' => $recaptchaData['error-codes'] ?? []
                ]);

                Log::channel('audit')->warning('REGISTRO_FALLIDO_RECAPTCHA', [
                    'que' => 'Registro bloqueado por reCAPTCHA',
                    'quien' => $request->email,
                    'cuando' => now()->toDateTimeString(),
                    'donde' => $request->ip(),
                    'descripcion' => 'El usuario no superó la verificación reCAPTCHA'
                ]);

                return back()
                    ->withErrors(['g-recaptcha-response' => 'Verifica el reCAPTCHA.'])
                    ->withInput();
            }

            $user = User::create([
                'name' => $request->name,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rol' => 'guest'
            ]);

            Log::info('Nuevo usuario registrado', [
                'email' => $user->email,
                'rol' => $user->rol,
                'ip' => $request->ip()
            ]);

            Log::channel('audit')->info('REGISTRO', [
                'que' => 'Registro de usuario guest',
                'quien' => $user->email,
                'cuando' => now()->toDateTimeString(),
                'donde' => $request->ip(),
                'rol' => $user->rol,
                'descripcion' => 'Nuevo usuario registrado correctamente'
            ]);

            return redirect()
                ->route('login')
                ->with('success', 'Usuario registrado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error en registro', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return back()
                ->withErrors(['email' => 'Ocurrió un error al registrar.'])
                ->withInput();
        }
    }

    public function login(Request $request)
    {
        try {
            $request->merge([
                'email' => strtolower(trim($request->email))
            ]);

            Log::debug('Datos sanitizados para login', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'g-recaptcha-response' => 'required'
            ], [
                'email.required' => 'El correo es obligatorio.',
                'email.email' => 'El correo no es válido.',
                'password.required' => 'La contraseña es obligatoria.',
                'g-recaptcha-response.required' => 'Completa el reCAPTCHA.'
            ]);

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip()
            ]);

            if (!$response->json('success')) {
                Log::warning('Login bloqueado por reCAPTCHA', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);

                Log::channel('audit')->warning('LOGIN_FALLIDO_RECAPTCHA', [
                    'que' => 'Login bloqueado por reCAPTCHA',
                    'quien' => $request->email,
                    'cuando' => now()->toDateTimeString(),
                    'donde' => $request->ip(),
                    'descripcion' => 'Falló la verificación reCAPTCHA'
                ]);

                return back()
                    ->withErrors(['g-recaptcha-response' => 'La verificación reCAPTCHA falló.'])
                    ->withInput();
            }

            Log::info('Intento de login', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'hora' => now()
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Credenciales incorrectas', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);

                Log::channel('audit')->warning('LOGIN_FALLIDO', [
                    'que' => 'Intento de login fallido',
                    'quien' => $request->email,
                    'cuando' => now()->toDateTimeString(),
                    'donde' => $request->ip(),
                    'descripcion' => 'Correo o contraseña incorrectos'
                ]);

                return back()
                    ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
                    ->withInput();
            }

            if ($user->rol === 'admin' || $user->rol === 'user') {
                $otp = random_int(100000, 999999);

                session([
                    'mfa_user_id' => $user->id,
                    'mfa_otp_hash' => Hash::make($otp),
                    'mfa_otp_expires_at' => now()->addMinutes(5),
                ]);

                Mail::to($user->email)->send(new OtpMail($otp));

                Log::info('OTP generado', [
                    'email' => $user->email,
                    'rol' => $user->rol,
                    'ip' => $request->ip()
                ]);

                Log::channel('audit')->info('OTP_GENERADO', [
                    'que' => 'Generación de código MFA',
                    'quien' => $user->email,
                    'cuando' => now()->toDateTimeString(),
                    'donde' => $request->ip(),
                    'rol' => $user->rol,
                    'descripcion' => 'Código MFA generado y enviado por correo'
                ]);

                return redirect()->route('mfa.verify');
            }

            Auth::login($user);
            $request->session()->regenerate();

            Log::info('Login exitoso sin MFA', [
                'email' => $user->email,
                'rol' => $user->rol,
                'ip' => $request->ip()
            ]);

            Log::channel('audit')->info('LOGIN_EXITOSO', [
                'que' => 'Inicio de sesión',
                'quien' => $user->email,
                'cuando' => now()->toDateTimeString(),
                'donde' => $request->ip(),
                'rol' => $user->rol,
                'descripcion' => 'Login guest sin MFA'
            ]);

            return redirect()->route('guest');

        } catch (\Exception $e) {
            Log::error('Error en login', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return back()
                ->withErrors(['email' => 'Ocurrió un error al iniciar sesión.']);
        }
    }

    public function showMfaForm()
    {
        if (!session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        return view('mfa-verify');
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric'
        ]);

        if (!session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('mfa_user_id'));

        if (
            !$user ||
            !Hash::check($request->code, session('mfa_otp_hash')) ||
            now()->greaterThan(session('mfa_otp_expires_at'))
        ) {
            Log::warning('MFA fallido', [
                'email' => $user?->email,
                'ip' => $request->ip()
            ]);

            Log::channel('audit')->warning('MFA_FALLIDO', [
                'que' => 'Verificación MFA fallida',
                'quien' => $user?->email,
                'cuando' => now()->toDateTimeString(),
                'donde' => $request->ip(),
                'descripcion' => 'Código MFA incorrecto o expirado'
            ]);

            return back()
                ->withErrors(['code' => 'Código incorrecto o expirado.']);
        }

        if ($user->rol === 'admin') {
            $ipPermitida = '127.0.0.1';

            if ($request->ip() !== $ipPermitida && $request->ip() !== '::1') {
                Log::error('Admin bloqueado por IP', [
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);

                Log::channel('audit')->warning('ADMIN_BLOQUEADO_IP', [
                    'que' => 'Bloqueo de administrador por IP',
                    'quien' => $user->email,
                    'cuando' => now()->toDateTimeString(),
                    'donde' => $request->ip(),
                    'rol' => $user->rol,
                    'descripcion' => 'Intento de acceso admin desde IP no autorizada'
                ]);

                session()->forget([
                    'mfa_user_id',
                    'mfa_otp_hash',
                    'mfa_otp_expires_at'
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'IP no autorizada.']);
            }
        }

        session()->forget([
            'mfa_user_id',
            'mfa_otp_hash',
            'mfa_otp_expires_at'
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        Log::info('Login MFA exitoso', [
            'email' => $user->email,
            'rol' => $user->rol,
            'ip' => $request->ip()
        ]);

        Log::channel('audit')->info('LOGIN_MFA_EXITOSO', [
            'que' => 'Inicio de sesión con MFA',
            'quien' => $user->email,
            'cuando' => now()->toDateTimeString(),
            'donde' => $request->ip(),
            'rol' => $user->rol,
            'descripcion' => 'MFA verificado correctamente'
        ]);

        if ($user->rol === 'admin') {
            return redirect()->route('admin');
        }

        if ($user->rol === 'user') {
            return redirect()->route('user');
        }

        return redirect()->route('guest');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        Log::info('Logout', [
            'email' => $user?->email,
            'rol' => $user?->rol,
            'ip' => $request->ip()
        ]);

        Log::channel('audit')->info('LOGOUT', [
            'que' => 'Cierre de sesión',
            'quien' => $user?->email,
            'cuando' => now()->toDateTimeString(),
            'donde' => $request->ip(),
            'rol' => $user?->rol,
            'descripcion' => 'El usuario cerró sesión'
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}