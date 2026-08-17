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
use App\Http\Controllers\SuperAdmin\CompetitionTemplateFormFieldController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\CompetitionAdmin\RubricController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompetitionJudgeController;
use App\Http\Controllers\Judge\JudgingRoomController;
use App\Http\Controllers\CompetitionAdmin\JudgingSessionController;
use App\Http\Controllers\JudgeAssignmentController;
use App\Http\Controllers\KnowledgeManagementController;




/*
|--------------------------------------------------------------------------
| หน้าแรก
|--------------------------------------------------------------------------
*/
Route::get('/', [KnowledgeManagementController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Public Submission
|--------------------------------------------------------------------------
| ผู้เข้าร่วมไม่ต้องเข้าสู่ระบบ
*/

Route::resource('competitions.submissions',SubmissionController::class)->only(['create','store',]);
Route::get('/submissions/{submission:submission_code}/success',[SubmissionController::class, 'success'])->name('submissions.success');
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

    Route::get('/profile',[ProfileController::class, 'edit'])->middleware('role:Competition Admin,Judge')->name('profile.edit');
    Route::put('/profile',[ProfileController::class, 'update'])->middleware('role:Competition Admin,Judge')->name('profile.update');

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
            Route::get('/templates/{template}/form-fields/create',[CompetitionTemplateFormFieldController::class, 'create'])->name('templates.form-fields.create');
            Route::post('/templates/{template}/form-fields',[CompetitionTemplateFormFieldController::class, 'store'])->name('templates.form-fields.store');
            Route::get('/templates/{template}/form-fields/edit',[CompetitionTemplateFormFieldController::class, 'edit'])->name('templates.form-fields.edit');
            Route::put('/templates/{template}/form-fields',[CompetitionTemplateFormFieldController::class, 'update'])->name('templates.form-fields.update');    
            Route::get('/competitions-judges',[CompetitionJudgeController::class, 'competitions'])->name('competitions.judges.list');
            Route::get('/competitions/{competition}/judges',[CompetitionJudgeController::class, 'index'])->name('competitions.judges.index');
            Route::put('/competitions/{competition}/judges',[CompetitionJudgeController::class, 'sync'])->name('competitions.judges.sync');
            Route::delete('/competitions/{competition}/judges/{judge}',[CompetitionJudgeController::class, 'destroy'])->name('competitions.judges.destroy');

        });

/*
|--------------------------------------------------------------------------
| Competition Admin
|--------------------------------------------------------------------------
*/

Route::prefix('competition-admin')->name('competition-admin.')->middleware(['auth', 'role:Competition Admin'])->group(function () {
        Route::get('/dashboard', [CompetitionAdminDashboardController::class,'index',])->name('dashboard');
        Route::resource('competitions',CompetitionController::class);
        Route::get('/submissions',[CompetitionController::class, 'submissions'])->name('submissions.index');
        Route::resource('competitions.rubrics',RubricController::class)->only(['index','store','update','destroy']);
        Route::get('/judging-rooms',[JudgingSessionController::class, 'index'])->name('judging-rooms.index');
        Route::get('/competitions/{competition}/judging-room',[JudgingSessionController::class, 'show'])->name('competitions.judging-room.show');
        Route::post('/competitions/{competition}/judging-room/start',[JudgingSessionController::class, 'start'])->name('competitions.judging-room.start');
        Route::post('/competitions/{competition}/judging-room/pause',[JudgingSessionController::class, 'pause'])->name('competitions.judging-room.pause');
        Route::post('/competitions/{competition}/judging-room/resume',[JudgingSessionController::class, 'resume'])->name('competitions.judging-room.resume');
        Route::put('/competitions/{competition}/judging-room/submission',[JudgingSessionController::class, 'selectSubmission'])->name('competitions.judging-room.submission');
        Route::post('/competitions/{competition}/judging-room/end',[JudgingSessionController::class, 'end'])->name('competitions.judging-room.end');
        Route::post('/competitions/{competition}/judging-room/close',[JudgingSessionController::class, 'close'])->name('competitions.judging-room.close');
    });

/*
|--------------------------------------------------------------------------
| Judge
|--------------------------------------------------------------------------
*/

Route::prefix('judge')->name('judge.')->middleware(['auth', 'role:Judge'])->group(function () {
        Route::get('/dashboard',[JudgeDashboardController::class, 'index'])->name('dashboard');
        Route::post('/assignments/{assignment}/accept',[JudgeAssignmentController::class, 'accept'])->name('assignments.accept');
        Route::post('/assignments/{assignment}/decline',[JudgeAssignmentController::class, 'decline'])->name('assignments.decline');
        Route::get('/judging-rooms',[JudgingRoomController::class, 'index'])->name('judging-rooms.index');
        Route::get('/judging-rooms/{session}',[JudgingRoomController::class, 'show'])->name('judging-rooms.show');
        Route::get('/judging-rooms/{session}/state',[JudgingRoomController::class, 'state'])->name('judging-rooms.state');
        Route::post('/judging-rooms/{session}/scores/draft',[JudgingRoomController::class, 'saveDraft'])->name('judging-rooms.scores.draft');
        Route::post('/judging-rooms/{session}/scores/submit',[JudgingRoomController::class, 'submit'])->name('judging-rooms.scores.submit');
    });