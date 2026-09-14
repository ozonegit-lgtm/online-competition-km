<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController as DashboardRedirectController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\CompetitionAdmin\DashboardController as CompetitionAdminDashboardController;
use App\Http\Controllers\Judge\DashboardController as JudgeDashboardController;
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
use App\Http\Controllers\KnowledgeItemFileController;
use App\Http\Controllers\KnowledgePageAssetController;
use App\Http\Controllers\ContactMessageController as PublicContactMessageController;
use App\Http\Controllers\SubmissionFileController;
use App\Http\Controllers\CompetitionAdmin\ResultController;
use App\Http\Controllers\CompetitionAdmin\KmSubmissionController;
use App\Http\Controllers\CompetitionAdmin\KnowledgeItemController;
use App\Http\Controllers\SuperAdmin\KnowledgeItemController as SuperAdminKnowledgeItemController;
use App\Http\Controllers\SuperAdmin\KmSubmissionController as SuperAdminKmSubmissionController;
use App\Http\Controllers\SuperAdmin\EbookController;
use App\Http\Controllers\SuperAdmin\KnowledgePageSettingController;
use App\Http\Controllers\SuperAdmin\KnowledgePageNavItemController;
use App\Http\Controllers\SuperAdmin\KnowledgeCategoryController;
use App\Http\Controllers\SuperAdmin\ContactMessageController as SuperAdminContactMessageController;

/*
|--------------------------------------------------------------------------
| หน้าแรก
|--------------------------------------------------------------------------
*/

Route::get('/', [KnowledgeManagementController::class, 'home'])->name('home');

Route::get('/knowledge', [KnowledgeManagementController::class, 'index'])
    ->name('knowledge.index');

Route::get('/knowledge/{knowledgeItem}', [KnowledgeManagementController::class, 'show'])
    ->whereNumber('knowledgeItem')
    ->name('knowledge.show');

Route::post('/knowledge/contact', [PublicContactMessageController::class, 'store'])
    ->middleware('throttle:public-contact')
    ->name('knowledge.contact.store');

Route::get('/knowledge/assets/{asset}', [KnowledgePageAssetController::class, 'show'])
    ->whereIn('asset', ['logo', 'hero', 'about', 'footer-logo'])
    ->name('knowledge.assets.show');

Route::get('/submission-files/{submissionFile}', [SubmissionFileController::class, 'show'])
    ->name('submission-files.show');

Route::get('/submission-files/{submissionFile}/download', [SubmissionFileController::class, 'download'])
    ->name('submission-files.download');

Route::get('/knowledge-items/{knowledgeItem}/cover', [KnowledgeItemFileController::class, 'cover'])
    ->name('knowledge-items.cover');

Route::get('/knowledge-items/{knowledgeItem}/attachment', [KnowledgeItemFileController::class, 'attachment'])
    ->name('knowledge-items.attachment');

Route::get('/knowledge-items/{knowledgeItem}/attachment/inline', [KnowledgeItemFileController::class, 'inline'])
    ->name('knowledge-items.attachment.inline');

/*
|--------------------------------------------------------------------------
| Public Submission
|--------------------------------------------------------------------------
| ผู้เข้าร่วมไม่ต้องเข้าสู่ระบบ
*/

Route::get('/competitions/{competition}/submissions/create', [SubmissionController::class, 'create'])
    ->name('competitions.submissions.create');

Route::post('/competitions/{competition}/submissions', [SubmissionController::class, 'store'])
    ->middleware('throttle:public-submissions')
    ->name('competitions.submissions.store');

Route::get('/submissions/{submission:submission_code}/success', [SubmissionController::class, 'success'])
    ->name('submissions.success');

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
    Route::get('/dashboard', DashboardRedirectController::class)
        ->middleware('role:Super Admin,Competition Admin,Judge')
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('role:Competition Admin,Judge')
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->middleware('role:Competition Admin,Judge')
        ->name('profile.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Super Admin
|--------------------------------------------------------------------------
*/

Route::prefix('superadmin')
    ->name('superadmin.')
    ->middleware(['auth', 'role:Super Admin'])
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/create-user', [UserManagementController::class, 'create'])->name('createUser');
        Route::post('/store', [UserManagementController::class, 'store'])->name('storeUser');
        Route::get('/show/{id}', [UserManagementController::class, 'show'])->name('showUser');
        Route::get('/edit/{id}', [UserManagementController::class, 'edit'])->name('editeUser');
        Route::put('/update/{id}', [UserManagementController::class, 'update'])->name('updateUser');
        Route::delete('/destroy/{id}', [UserManagementController::class, 'destroy'])->name('deleteUser');

        Route::resource('templates', CompetitionTemplateController::class);

        Route::resource('categories', CompetitionCategoryController::class)
            ->parameters(['categories' => 'competitionCategory']);

        Route::get('/templates/{template}/form-fields/create', [CompetitionTemplateFormFieldController::class, 'create'])
            ->name('templates.form-fields.create');

        Route::post('/templates/{template}/form-fields', [CompetitionTemplateFormFieldController::class, 'store'])
            ->name('templates.form-fields.store');

        Route::get('/templates/{template}/form-fields/edit', [CompetitionTemplateFormFieldController::class, 'edit'])
            ->name('templates.form-fields.edit');

        Route::put('/templates/{template}/form-fields', [CompetitionTemplateFormFieldController::class, 'update'])
            ->name('templates.form-fields.update');

        Route::get('/competitions-judges', [CompetitionJudgeController::class, 'competitions'])
            ->name('competitions.judges.list');

        Route::get('/competitions/{competition}/judges', [CompetitionJudgeController::class, 'index'])
            ->name('competitions.judges.index');

        Route::put('/competitions/{competition}/judges', [CompetitionJudgeController::class, 'sync'])
            ->name('competitions.judges.sync');

        Route::delete('/competitions/{competition}/judges/{judge}', [CompetitionJudgeController::class, 'destroy'])
            ->name('competitions.judges.destroy');

        Route::get('/km', [SuperAdminKnowledgeItemController::class, 'index'])->name('km.index');
        Route::get('/km/create', [SuperAdminKnowledgeItemController::class, 'create'])->name('km.create');
        Route::post('/km', [SuperAdminKnowledgeItemController::class, 'store'])->name('km.store');
        Route::get('/km/{knowledgeItem}', [SuperAdminKnowledgeItemController::class, 'show'])->name('km.show');
        Route::get('/km/{knowledgeItem}/edit', [SuperAdminKnowledgeItemController::class, 'edit'])->name('km.edit');
        Route::put('/km/{knowledgeItem}', [SuperAdminKnowledgeItemController::class, 'update'])->name('km.update');
        Route::delete('/km/{knowledgeItem}', [SuperAdminKnowledgeItemController::class, 'destroy'])->name('km.destroy');

        Route::post('/km/{knowledgeItem}/publish', [SuperAdminKnowledgeItemController::class, 'publish'])
            ->name('km.publish');

        Route::delete('/km/{knowledgeItem}/publish', [SuperAdminKnowledgeItemController::class, 'unpublish'])
            ->name('km.unpublish');

        Route::post('/km/{knowledgeItem}/feature', [SuperAdminKnowledgeItemController::class, 'feature'])
            ->name('km.feature');

        Route::delete('/km/{knowledgeItem}/feature', [SuperAdminKnowledgeItemController::class, 'unfeature'])
            ->name('km.unfeature');

        Route::post('/submissions/{submission}/km/publish', [SuperAdminKmSubmissionController::class, 'publish'])
            ->name('submissions.km.publish');

        Route::delete('/submissions/{submission}/km/publish', [SuperAdminKmSubmissionController::class, 'unpublish'])
            ->name('submissions.km.unpublish');

        Route::get('/knowledge-page', [KnowledgePageSettingController::class, 'edit'])
            ->name('knowledge-page.settings.edit');
        Route::put('/knowledge-page', [KnowledgePageSettingController::class, 'update'])
            ->name('knowledge-page.settings.update');

        Route::get('/knowledge-page/nav-items', [KnowledgePageNavItemController::class, 'index'])
            ->name('knowledge-page.nav-items.index');
        Route::post('/knowledge-page/nav-items', [KnowledgePageNavItemController::class, 'store'])
            ->name('knowledge-page.nav-items.store');
        Route::put('/knowledge-page/nav-items/reorder', [KnowledgePageNavItemController::class, 'reorder'])
            ->name('knowledge-page.nav-items.reorder');
        Route::put('/knowledge-page/nav-items/{navItem}', [KnowledgePageNavItemController::class, 'update'])
            ->name('knowledge-page.nav-items.update');
        Route::patch('/knowledge-page/nav-items/{navItem}/visibility', [KnowledgePageNavItemController::class, 'visibility'])
            ->name('knowledge-page.nav-items.visibility');
        Route::delete('/knowledge-page/nav-items/{navItem}', [KnowledgePageNavItemController::class, 'destroy'])
            ->name('knowledge-page.nav-items.destroy');

        Route::put('/knowledge-page/books/reorder', [EbookController::class, 'reorder'])
            ->name('knowledge-page.books.reorder');
        Route::post('/knowledge-page/books/{ebook}/publish', [EbookController::class, 'publish'])
            ->whereNumber('ebook')->name('knowledge-page.books.publish');
        Route::delete('/knowledge-page/books/{ebook}/publish', [EbookController::class, 'hide'])
            ->whereNumber('ebook')->name('knowledge-page.books.hide');
        Route::resource('/knowledge-page/books', EbookController::class)
            ->parameters(['books' => 'ebook'])
            ->names([
                'index' => 'knowledge-page.books.index',
                'create' => 'knowledge-page.books.create',
                'store' => 'knowledge-page.books.store',
                'show' => 'knowledge-page.books.show',
                'edit' => 'knowledge-page.books.edit',
                'update' => 'knowledge-page.books.update',
                'destroy' => 'knowledge-page.books.destroy',
            ]);

        Route::get('/knowledge-page/categories', [KnowledgeCategoryController::class, 'index'])
            ->name('knowledge-page.categories.index');
        Route::post('/knowledge-page/categories', [KnowledgeCategoryController::class, 'store'])
            ->name('knowledge-page.categories.store');
        Route::put('/knowledge-page/categories/{knowledgeCategory}', [KnowledgeCategoryController::class, 'update'])
            ->name('knowledge-page.categories.update');
        Route::delete('/knowledge-page/categories/{knowledgeCategory}', [KnowledgeCategoryController::class, 'destroy'])
            ->name('knowledge-page.categories.destroy');

        Route::get('/contact-messages', [SuperAdminContactMessageController::class, 'index'])
            ->name('contact-messages.index');
        Route::get('/contact-messages/{contactMessage}', [SuperAdminContactMessageController::class, 'show'])
            ->name('contact-messages.show');
        Route::patch('/contact-messages/{contactMessage}/status', [SuperAdminContactMessageController::class, 'status'])
            ->name('contact-messages.status');
    });

/*
|--------------------------------------------------------------------------
| Competition Admin
|--------------------------------------------------------------------------
*/

Route::prefix('competition-admin')
    ->name('competition-admin.')
    ->middleware(['auth', 'role:Competition Admin'])
    ->group(function () {
        Route::get('/dashboard', [CompetitionAdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('competitions', CompetitionController::class);

        Route::get('/submissions', [CompetitionController::class, 'submissions'])
            ->name('submissions.index');

        Route::resource('competitions.rubrics', RubricController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('/judging-rooms', [JudgingSessionController::class, 'index'])
            ->name('judging-rooms.index');

        Route::get('/competitions/{competition}/judging-room', [JudgingSessionController::class, 'show'])
            ->name('competitions.judging-room.show');

        Route::post('/competitions/{competition}/judging-room/start', [JudgingSessionController::class, 'start'])
            ->name('competitions.judging-room.start');

        Route::post('/competitions/{competition}/judging-room/pause', [JudgingSessionController::class, 'pause'])
            ->name('competitions.judging-room.pause');

        Route::post('/competitions/{competition}/judging-room/resume', [JudgingSessionController::class, 'resume'])
            ->name('competitions.judging-room.resume');

        Route::put('/competitions/{competition}/judging-room/submission', [JudgingSessionController::class, 'selectSubmission'])
            ->name('competitions.judging-room.submission');

        Route::put('/competitions/{competition}/judging-room/state', [JudgingSessionController::class, 'updatePresentationState'])
            ->name('competitions.judging-room.state');

        Route::post('/competitions/{competition}/judging-room/end', [JudgingSessionController::class, 'end'])
            ->name('competitions.judging-room.end');

        Route::post('/competitions/{competition}/judging-room/close', [JudgingSessionController::class, 'close'])
            ->name('competitions.judging-room.close');

        Route::get('/results', [ResultController::class, 'competitions'])->name('results.index');

        Route::get('/competitions/{competition}/results', [ResultController::class, 'index'])
            ->name('competitions.results.index');

        Route::post('/competitions/{competition}/results/publish', [ResultController::class, 'publish'])
            ->name('competitions.results.publish');

        Route::delete('/competitions/{competition}/results/publish', [ResultController::class, 'unpublish'])
            ->name('competitions.results.unpublish');

        Route::get('/km/submissions', [KmSubmissionController::class, 'index'])
            ->name('km.submissions.index');

        Route::get('/km', [KnowledgeItemController::class, 'index'])->name('km.index');
        Route::get('/km/create', [KnowledgeItemController::class, 'create'])->name('km.create');
        Route::post('/km', [KnowledgeItemController::class, 'store'])->name('km.store');
        Route::get('/km/{knowledgeItem}', [KnowledgeItemController::class, 'show'])->name('km.show');
        Route::get('/km/{knowledgeItem}/edit', [KnowledgeItemController::class, 'edit'])->name('km.edit');
        Route::put('/km/{knowledgeItem}', [KnowledgeItemController::class, 'update'])->name('km.update');
        Route::delete('/km/{knowledgeItem}', [KnowledgeItemController::class, 'destroy'])->name('km.destroy');

        Route::post('/km/{knowledgeItem}/publish', [KnowledgeItemController::class, 'publish'])
            ->name('km.publish');

        Route::delete('/km/{knowledgeItem}/publish', [KnowledgeItemController::class, 'unpublish'])
            ->name('km.unpublish');

        Route::post('/submissions/{submission}/km/publish', [KmSubmissionController::class, 'publish'])
            ->name('submissions.km.publish');

        Route::delete('/submissions/{submission}/km/publish', [KmSubmissionController::class, 'unpublish'])
            ->name('submissions.km.unpublish');
    });

/*
|--------------------------------------------------------------------------
| Judge
|--------------------------------------------------------------------------
*/

Route::prefix('judge')
    ->name('judge.')
    ->middleware(['auth', 'role:Judge'])
    ->group(function () {
        Route::get('/dashboard', [JudgeDashboardController::class, 'index'])->name('dashboard');

        Route::post('/assignments/{assignment}/accept', [JudgeAssignmentController::class, 'accept'])
            ->name('assignments.accept');

        Route::post('/assignments/{assignment}/decline', [JudgeAssignmentController::class, 'decline'])
            ->name('assignments.decline');

        Route::get('/judging-rooms', [JudgingRoomController::class, 'index'])
            ->name('judging-rooms.index');

        Route::get('/judging-rooms/{session}', [JudgingRoomController::class, 'show'])
            ->name('judging-rooms.show');

        Route::get('/judging-rooms/{session}/state', [JudgingRoomController::class, 'state'])
            ->name('judging-rooms.state');

        // Route::post('/judging-rooms/{session}/scores/draft', [JudgingRoomController::class, 'saveDraft'])
        //     ->name('judging-rooms.scores.draft');

        Route::post('/judging-rooms/{session}/scores/submit', [JudgingRoomController::class, 'submit'])
            ->name('judging-rooms.scores.submit');
    });
