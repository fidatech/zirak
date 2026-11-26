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
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/courses/{course}/buy', [CourseController::class, 'purchase'])->name('courses.purchase');

    Route::scopeBindings()->group(function () {
        Route::get('/courses/{course}/chapters/{chapter}/{tab?}', [ChapterController::class, 'show'])
            ->where('tab', 'learn|practice|test')->name('chapters.show');
        Route::post('/courses/{course}/chapters/{chapter}/lessons/{lesson}/complete', [ChapterController::class, 'completeLesson'])->name('lessons.complete');
        Route::post('/courses/{course}/chapters/{chapter}/practice', [ChapterController::class, 'answer'])->name('practice.answer');
        Route::post('/courses/{course}/chapters/{chapter}/test', [ChapterController::class, 'submitTest'])->name('test.submit');
    });

    Route::get('/report', ReportController::class)->name('report');
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/payments/return', [PaymentController::class, 'return'])->name('payments.return');
    Route::get('/payments/fake/{number}', [PaymentController::class, 'fake'])->name('payments.fake');
    Route::post('/payments/fake/{number}', [PaymentController::class, 'fakePay'])->name('payments.fake.pay');
});

Route::post('/webhooks/hesabpay', [PaymentController::class, 'hesabpayWebhook'])->name('webhooks.hesabpay');
