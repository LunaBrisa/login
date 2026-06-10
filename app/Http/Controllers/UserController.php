<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Administrador;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:8',
                'password_confirmation' => 'required|min:8'
            ],
            [
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El correo es obligatorio.',
                'email.email' => 'El correo no es válido.',
                'email.unique' => 'Este correo ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password_confirmation.required' => 'Debes confirmar tu contraseña.'
            ]
        );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('register')
            ->with('success', 'Usuario registrado correctamente');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required' 
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'g-recaptcha-response.required' => 'Por favor, completa el reCAPTCHA de seguridad.',
        ]);

        $response = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip()
        ]);

        if (!$response->json('success')) {
            return back()->withErrors([
                'email' => 'La verificación del reCAPTCHA ha fallado. Inténtalo de nuevo.'
            ])->withInput();
        }
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            
            if ($user->rol === 'guest') {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->to('/');
            }

            $otp = rand(100000, 999999);
            
            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => now()->addDay() 
            ]);

            session(['mfa_user_id' => $user->id]);

            \Log::info("Auditoría de Seguridad - OTP generado para {$user->email}: {$otp}");

            return redirect()->route('mfa.verify');
        }

        return back()->withErrors([
            'email' => 'Credenciales incorrectas'
        ])->withInput();
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
        ], [
            'code.required' => 'El código de verificación es obligatorio.',
            'code.numeric' => 'El código debe ser un número.'
        ]);

        if (!session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('mfa_user_id'));

        if ($user && $user->otp_code == $request->code) {
            
            $user->update(['otp_code' => null, 'otp_expires_at' => null]);

            if ($user->rol === 'user') {
                Auth::login($user);
                session()->forget('mfa_user_id');
                $request->session()->regenerate();
                return redirect()->to('/');
            }
if ($user->rol === 'admin') {
    $userIp = $request->ip(); 
    
    $ipPermitida = '127.0.0.1'; 
            if ($user->rol === 'admin') {
                $userIp = $request->ip(); 
                
                $ipPermitida = '127.0.0.1'; 

                if ($userIp !== $ipPermitida && $userIp !== '::1') {
                    \Log::warning("Alerta de Seguridad: El administrador {$user->email} intentó acceder desde una IP no autorizada: {$userIp}");
                    
                    session()->forget('mfa_user_id');
                    return redirect()->route('login')->withErrors([
                        'email' => 'Acceso denegado: IP de conexión no autorizada para privilegios administrativos.'
                    ]);
                }

                Auth::login($user);
                session()->forget('mfa_user_id');
                $request->session()->regenerate();
                return redirect()->route('admin');
            }
        }

        return back()->withErrors(['code' => 'El código es incorrecto o ha expirado.']);
    }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken(); 

        return redirect('/');
    }  
    }
