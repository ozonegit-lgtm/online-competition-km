<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController as DashboardRedirectController;
use App\Http\Controllers\SuperAdmin\DashboardController
    as SuperAdminDashboardController;
use App\Http\Controllers\CompetitionAdmin\DashboardController
    as CompetitionAdminDashboardController;
use App\Http\Controllers\Judge\DashboardController
as JudgeDashboardController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use App\Http\Controllers\CompetitionAdmin\CompetitionController;
use App\Http\Controllers\SuperAdmin\CompetitionTemplateController;
use App\Http\Controllers\SuperAdmin\CompetitionCategoryController;



/*
|--------------------------------------------------------------------------
| หน้าแรก
|--------------------------------------------------------------------------
*/

Route::get('/', function () {return redirect()->route('login');});

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'postLogin'])->name('login.post');

});

/*
|--------------------------------------------------------------------------
| Authenticated User
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
     * รับผู้ใช้หลัง Login แล้วส่งไป Dashboard ตาม Role
     */
    Route::get('/dashboard', DashboardRedirectController::class)->middleware('role:Super Admin,Competition Admin,Judge')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});

/*
|--------------------------------------------------------------------------
| Super Admin
|--------------------------------------------------------------------------
*/

    Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'role:Super Admin'])->group(function () {
            Route::get('/dashboard', [SuperAdminDashboardController::class,'index',])->name('dashboard');
            Route::get('/create-user', [UserManagementController::class,'create',])->name('createUser');
            Route::post('/store', [UserManagementController::class,'store',])->name('storeUser');
            Route::get('/show/{id}', [UserManagementController::class,'show',])->name('showUser');
            Route::get('/edit/{id}', [UserManagementController::class,'edit',])->name('editeUser');
            Route::put('/update/{id}', [UserManagementController::class,'update',])->name('updateUser');
            Route::delete('/destroy/{id}', [UserManagementController::class,'destroy',])->name('deleteUser');
            Route::resource('templates', CompetitionTemplateController::class);
            Route::resource('categories', CompetitionCategoryController::class)->parameters(['categories' => 'competitionCategory',]);
        });

/*
|--------------------------------------------------------------------------
| Competition Admin
|--------------------------------------------------------------------------
*/

Route::prefix('competition-admin')->name('competition-admin.')->middleware(['auth', 'role:Competition Admin'])->group(function () {
        Route::get('/dashboard', [CompetitionAdminDashboardController::class,'index',])->name('dashboard');
        Route::resource('competitions',CompetitionController::class);
    });

/*
|--------------------------------------------------------------------------
| Judge
|--------------------------------------------------------------------------
*/

Route::prefix('judge')->name('judge.')->middleware(['auth', 'role:Judge'])->group(function () {
        Route::get('/dashboard', [JudgeDashboardController::class,'index',])->name('dashboard');
    });