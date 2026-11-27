<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeachController;
use App\Http\Controllers\TelegramAuthController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TutorController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/courses', [CatalogController::class, 'index'])->name('catalog');
Route::get('/subjects/{category}', [CatalogController::class, 'index'])->name('catalog.category');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/verify/{code?}', [CertificateController::class, 'verify'])->name('certificates.verify');
Route::view('/offline', 'offline')->name('offline');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'sendCode'])->name('login.send')->middleware('throttle:10,1');
    Route::get('/login/code', [AuthController::class, 'showCode'])->name('login.code');
    Route::post('/login/code', [AuthController::class, 'verify'])->name('login.verify')->middleware('throttle:10,1');
    Route::get('/login/telegram', [TelegramAuthController::class, 'login'])->name('login.telegram')->middleware('throttle:10,1');
    Route::get('/login/telegram/status', [TelegramAuthController::class, 'loginStatus'])->name('login.telegram.status')->middleware('throttle:60,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/become-a-teacher', [TeachController::class, 'apply'])->name('teach.apply');
    Route::post('/become-a-teacher', [TeachController::class, 'submit'])->middleware('throttle:5,60')->name('teach.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/courses/{course}/buy', [CourseController::class, 'purchase'])->name('courses.purchase');

    Route::scopeBindings()->group(function () {
        Route::get('/courses/{course}/chapters/{chapter}/{tab?}', [ChapterController::class, 'show'])
            ->where('tab', 'learn|practice|test')->name('chapters.show');
        Route::post('/courses/{course}/chapters/{chapter}/lessons/{lesson}/complete', [ChapterController::class, 'completeLesson'])->name('lessons.complete');
        Route::post('/courses/{course}/chapters/{chapter}/practice', [ChapterController::class, 'answer'])->name('practice.answer');
        Route::post('/courses/{course}/chapters/{chapter}/test', [ChapterController::class, 'submitTest'])->name('test.submit');
    });

    Route::get('/courses/{course}/tutor', [TutorController::class, 'history'])->name('tutor.history');
    Route::post('/courses/{course}/tutor', [TutorController::class, 'ask'])->name('tutor.ask')->middleware('throttle:8,1');
    Route::post('/tutor/{message}/escalate', [TutorController::class, 'escalate'])->name('tutor.escalate');
    Route::post('/tutor/{message}/rate', [TutorController::class, 'rate'])->name('tutor.rate');

    Route::get('/report', ReportController::class)->name('report');
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/telegram', [TelegramAuthController::class, 'link'])->name('profile.telegram')->middleware('throttle:10,1');
    Route::get('/profile/telegram/status', [TelegramAuthController::class, 'linkStatus'])->name('profile.telegram.status')->middleware('throttle:60,1');
    Route::delete('/profile/telegram', [TelegramAuthController::class, 'unlink'])->name('profile.telegram.unlink');
    Route::put('/profile/telegram/reminders', [TelegramAuthController::class, 'reminders'])->name('profile.telegram.reminders');

    Route::get('/payments/return', [PaymentController::class, 'return'])->name('payments.return');
    Route::get('/payments/fake/{number}', [PaymentController::class, 'fake'])->name('payments.fake');
    Route::post('/payments/fake/{number}', [PaymentController::class, 'fakePay'])->name('payments.fake.pay');
});

Route::post('/webhooks/telegram', TelegramWebhookController::class)->name('webhooks.telegram');
Route::post('/webhooks/hesabpay', [PaymentController::class, 'hesabpayWebhook'])->name('webhooks.hesabpay');

Route::get('/teachers/{teacher}', [TeachController::class, 'show'])->whereNumber('teacher')->name('teachers.show');
