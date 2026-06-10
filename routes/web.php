<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('login', [UserController::class, 'login']);

Route::get('/register', function () {
        return view('register');
    })->name('register');

Route::post('register', [UserController::class, 'register']);

Route::get('/mfa-verify', [UserController::class, 'showMfaForm'])->name('mfa.verify');
Route::post('/mfa-verify', [UserController::class, 'verifyMfa'])->name('mfa.store');


Route::middleware(['auth'])->group(function () {
    
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

    Route::get('/admin', function () {
        return "<h1>Bienvenido al Panel de Administración Seguro (3 Factores verificados)</h1>";
    })->name('admin');
});