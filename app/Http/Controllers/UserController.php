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
        'email' => 'required|email',
        'password' => 'required|confirmed|min:8',
        'password_confirmation' => 'required|min:8',
        'rol' => 'required',
        ],
        [
        'name.required' => 'El nombre es obligatorio.',

        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'El correo no es válido.',

        'password.required' => 'La contraseña es obligatoria.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]
        );

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'rol' => '$request->rol'
        ]);

        return redirect()->route('register')
    ->with('success', 'Usuario registrado correctamente');
    }

   
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

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {

        $request->session()->regenerate(); 

        return redirect()->route('/');
    }

    return back()->withErrors([
        'email' => 'Credenciales incorrectas'
    ])->withInput();
}
  public function logout(Request $request)
{
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
}  
}
