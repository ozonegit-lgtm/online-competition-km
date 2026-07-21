<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'index'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'postLogin'])
        ->name('login.post');

});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

});