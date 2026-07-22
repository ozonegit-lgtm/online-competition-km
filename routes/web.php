<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'index'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'postLogin'])
        ->name('login.post');

});

/*
|--------------------------------------------------------------------------
| Authenticated User
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

});

/*
|--------------------------------------------------------------------------
| Super Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Super Admin'])->group(function () {

    // Route ของ Super Admin

});

/*
|--------------------------------------------------------------------------
| Competition Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Competition Admin'])->group(function () {

    // Route ของ Competition Admin

});

/*
|--------------------------------------------------------------------------
| Judge
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Judge'])->group(function () {

    // Route ของ Judge

});