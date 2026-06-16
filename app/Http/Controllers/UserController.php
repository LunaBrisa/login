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
     * REGISTRO
     */
    public function register(Request $request)
    {
        try {

            // Sanitización
            $request->merge([
                'name' => strip_tags(trim($request->name)),
                'apellido' => strip_tags(trim($request->apellido)),
                'email' => strtolower(trim($request->email)),
            ]);

            Log::debug('Datos sanitizados para registro', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            // Validación
            $request->validate([
                'name' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
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
                'name.required' =>
                    'El nombre es obligatorio.',

                'apellido.required' =>
                    'El apellido es obligatorio.',

                'email.required' =>
                    'El correo es obligatorio.',

                'email.email' =>
                    'El correo no es válido.',

                'email.unique' =>
                    'Este correo ya está registrado.',

                'password.required' =>
                    'La contraseña es obligatoria.',

                'password.confirmed' =>
                    'Las contraseñas no coinciden.',

                'g-recaptcha-response.required' =>
                    'Completa el reCAPTCHA.'
            ]);

            // Verificar reCAPTCHA
            $response = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret' =>
                        config('services.recaptcha.secret_key'),

                    'response' =>
                        $request->input(
                            'g-recaptcha-response'
                        ),

                    'remoteip' =>
                        $request->ip(),
                ]
            );

            $recaptchaData = $response->json();

            if (!($recaptchaData['success'] ?? false)) {

                Log::warning(
                    'Registro fallido por reCAPTCHA',
                    [
                        'ip' => $request->ip(),
                        'email' => $request->email,
                        'errors' =>
                            $recaptchaData['error-codes']
                            ?? []
                    ]
                );

                return back()
                    ->withErrors([
                        'g-recaptcha-response' =>
                            'Verifica el reCAPTCHA.'
                    ])
                    ->withInput();
            }

            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => Hash::make(
                    $request->password
                ),
                'rol' => 'guest'
            ]);

            Log::info(
                'Nuevo usuario registrado',
                [
                    'email' => $user->email,
                    'rol' => $user->rol,
                    'ip' => $request->ip()
                ]
            );

            // Auditoría
            AuditLog::create([
                'user_id' => $user->id,
                'accion' => 'REGISTRO',
                'email' => $user->email,
                'ip' => $request->ip(),
                'descripcion' =>
                    'Nuevo usuario registrado'
            ]);

            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Usuario registrado correctamente.'
                );

        } catch (\Exception $e) {

            Log::error(
                'Error en registro',
                [
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'error' => $e->getMessage()
                ]
            );

            return back()
                ->withErrors([
                    'email' =>
                        'Ocurrió un error al registrar.'
                ])
                ->withInput();
        }
    }

    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        try {

            // Sanitización
            $request->merge([
                'email' =>
                    strtolower(
                        trim($request->email)
                    )
            ]);

            Log::debug(
                'Datos sanitizados para login',
                [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]
            );

            // Validación
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'g-recaptcha-response' =>
                    'required'

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

            // Verificar reCAPTCHA
            $response = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret' =>
                        config(
                            'services.recaptcha.secret_key'
                        ),

                    'response' =>
                        $request->input(
                            'g-recaptcha-response'
                        ),

                    'remoteip' =>
                        $request->ip()
                ]
            );

            if (
                !$response->json('success')
            ) {

                Log::warning(
                    'Login bloqueado por reCAPTCHA',
                    [
                        'email' => $request->email,
                        'ip' => $request->ip()
                    ]
                );

                AuditLog::create([
                    'accion' =>
                        'LOGIN_FALLIDO',
                    'email' =>
                        $request->email,
                    'ip' =>
                        $request->ip(),
                    'descripcion' =>
                        'Falló reCAPTCHA'
                ]);

                return back()
                    ->withErrors([
                        'g-recaptcha-response' =>
                            'La verificación reCAPTCHA falló.'
                    ])
                    ->withInput();
            }

            Log::info(
                'Intento de login',
                [
                    'email' =>
                        $request->email,
                    'ip' =>
                        $request->ip(),
                    'hora' =>
                        now()
                ]
            );

            // Buscar usuario
            $user = User::where(
                'email',
                $request->email
            )->first();

            if (!$user) {

                Log::warning(
                    'Correo inexistente',
                    [
                        'email' =>
                            $request->email,
                        'ip' =>
                            $request->ip()
                    ]
                );
            }

            // Verificar credenciales
            if (
                !$user ||
                !Hash::check(
                    $request->password,
                    $user->password
                )
            ) {

                Log::warning(
                    'Credenciales incorrectas',
                    [
                        'email' =>
                            $request->email,
                        'ip' =>
                            $request->ip()
                    ]
                );

                AuditLog::create([
                    'accion' =>
                        'LOGIN_FALLIDO',
                    'email' =>
                        $request->email,
                    'ip' =>
                        $request->ip(),
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

            /**
             * MFA SOLO ADMIN Y USER
             */
            if (
                $user->rol === 'admin' ||
                $user->rol === 'user'
            ) {

                $otp = rand(
                    100000,
                    999999
                );

                $user->update([
                    'otp_code' => $otp,
                    'otp_expires_at' =>
                        now()->addMinutes(5)
                ]);

                session([
                    'mfa_user_id' =>
                        $user->id
                ]);

                Log::info(
                    'OTP generado',
                    [
                        'email' =>
                            $user->email,
                        'rol' =>
                            $user->rol,
                        'ip' =>
                            $request->ip(),
                        'otp' =>
                            $otp
                    ]
                );

                AuditLog::create([
                    'user_id' =>
                        $user->id,
                    'accion' =>
                        'OTP_GENERADO',
                    'email' =>
                        $user->email,
                    'ip' =>
                        $request->ip(),
                    'descripcion' =>
                        'Código MFA generado'
                ]);

                return redirect()
                    ->route('mfa.verify');
            }

            /**
             * GUEST ENTRA DIRECTO
             */
            Auth::login($user);

            $request
                ->session()
                ->regenerate();

            Log::info(
                'Login exitoso sin MFA',
                [
                    'email' =>
                        $user->email,
                    'rol' =>
                        $user->rol,
                    'ip' =>
                        $request->ip()
                ]
            );

            AuditLog::create([
                'user_id' =>
                    $user->id,
                'accion' =>
                    'LOGIN_EXITOSO',
                'email' =>
                    $user->email,
                'ip' =>
                    $request->ip(),
                'descripcion' =>
                    'Login guest sin MFA'
            ]);

            return redirect()
                ->route('guest');

        } catch (\Exception $e) {

            Log::error(
                'Error en login',
                [
                    'email' =>
                        $request->email,
                    'ip' =>
                        $request->ip(),
                    'error' =>
                        $e->getMessage()
                ]
            );

            return back()
                ->withErrors([
                    'email' =>
                        'Ocurrió un error al iniciar sesión.'
                ]);
        }
    }

    /**
     * FORM MFA
     */
    public function showMfaForm()
    {
        if (
            !session()->has(
                'mfa_user_id'
            )
        ) {
            return redirect()
                ->route('login');
        }

        return view(
            'mfa-verify'
        );
    }

    /**
     * VERIFICAR MFA
     */
    public function verifyMfa(
        Request $request
    ) {
        $request->validate([
            'code' =>
                'required|numeric'
        ]);

        if (
            !session()->has(
                'mfa_user_id'
            )
        ) {
            return redirect()
                ->route('login');
        }

        $user = User::find(
            session(
                'mfa_user_id'
            )
        );

        if (
            !$user ||
            $user->otp_code
                != $request->code ||
            now()->greaterThan(
                $user->otp_expires_at
            )
        ) {

            Log::warning(
                'MFA fallido',
                [
                    'email' =>
                        $user?->email,
                    'ip' =>
                        $request->ip()
                ]
            );

            return back()
                ->withErrors([
                    'code' =>
                        'Código incorrecto o expirado.'
                ]);
        }

        // Restricción admin IP
        if (
            $user->rol === 'admin'
        ) {

            $ipPermitida =
                '172.0.0.1';

            if (
                $request->ip()
                    !== $ipPermitida &&
                $request->ip()
                    !== '::1'
            ) {

                Log::error(
                    'Admin bloqueado por IP',
                    [
                        'email' =>
                            $user->email,
                        'ip' =>
                            $request->ip()
                    ]
                );

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

        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        Auth::login($user);

        $request
            ->session()
            ->regenerate();

        session()->forget(
            'mfa_user_id'
        );

        Log::info(
            'Login MFA exitoso',
            [
                'email' =>
                    $user->email,
                'rol' =>
                    $user->rol,
                'ip' =>
                    $request->ip()
            ]
        );

        if (
            $user->rol === 'admin'
        ) {
            return redirect()
                ->route('admin');
        }

        if (
            $user->rol === 'user'
        ) {
            return redirect()
                ->route('user');
        }

        return redirect()
            ->route('guest');
    }

    /**
     * LOGOUT
     */
    public function logout(
        Request $request
    ) {
        $user = Auth::user();

        Log::info(
            'Logout',
            [
                'email' =>
                    $user?->email,
                'rol' =>
                    $user?->rol,
                'ip' =>
                    $request->ip()
            ]
        );

        AuditLog::create([
            'user_id' =>
                $user?->id,
            'accion' =>
                'LOGOUT',
            'email' =>
                $user?->email,
            'ip' =>
                $request->ip(),
            'descripcion' =>
                'Cierre de sesión'
        ]);

        Auth::logout();

        $request
            ->session()
            ->invalidate();

        $request
            ->session()
            ->regenerateToken();

        return redirect('/login');
    }
}