<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post(
    'login',
    [UserController::class, 'login']
)->middleware('throttle:5,1');

Route::post(
    'register',
    [UserController::class, 'register']
)->middleware('throttle:3,1');

Route::get('/', function () {
        return view('register');
    })->name('register');


Route::get('/mfa-verify', [UserController::class, 'showMfaForm'])->name('mfa.verify');
Route::post('/mfa-verify', [UserController::class, 'verifyMfa'])->name('mfa.store');


Route::middleware(['auth'])->group(function () {
    
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/admin', function () {

    if (auth()->user()->rol !== 'admin') {
        abort(403);
    }

    return view('admin');

})->name('admin');
});

