<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ==========================================
// 1. RUTAS PÚBLICAS (Accesibles por cualquiera)
// ==========================================
Route::get('/', function () {
    return view('welcome');
})->name('/');

// Rutas de Autenticación Básica (Factor 1)
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('login', [UserController::class, 'login']);


// ==========================================
// 2. RUTAS DE VERIFICACIÓN MULTIFACTOR (MFA)
// ==========================================
// Estas rutas gestionan el Factor 2 (OTP) y Factor 3 (IP del Admin)
Route::get('/mfa-verify', [UserController::class, 'showMfaForm'])->name('mfa.verify');
Route::post('/mfa-verify', [UserController::class, 'verifyMfa'])->name('mfa.store');


// ==========================================
// 3. RUTAS PROTEGIDAS (Solo usuarios logueados)
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // Cierre de sesión seguro
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

    // Panel de administración seguro (Requiere haber pasado los 3 factores)
    Route::get('/admin', function () {
        return "<h1>Bienvenido al Panel de Administración Seguro (3 Factores verificados)</h1>";
    })->name('admin');

    // EXCLUSIVO PARA EL ADMINISTRADOR: Registro y asignación de roles
    // Si un 'user' o 'guest' intenta escribir /register, este filtro lo rebota a la raíz
    Route::get('/register', function () {
        if (auth()->user()->rol !== 'admin') {
            return redirect('/')->with('error', 'No tienes permisos para registrar usuarios.');
        }
        return view('register');
    })->name('register');

    Route::post('register', [UserController::class, 'register']);
});