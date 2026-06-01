<?php

use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;

// ------------Non-Authenticated-------------//

Route::middleware('guest')->group(function () {
    // ------------Register-------------//
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    // ------------Login-------------//
    Route::get('/login', [AuthenticateController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticateController::class, 'store']); // 可以不命名路由，因为登录成功后会重定向到home页面，不需要使用路由名称来生成URL


    // ------------Reset Password 重置密码路由-------------//

    Route::get('/forgot-password', [ResetPasswordController::class, 'requestPass'])->name('password.request');
    Route::post('/forgot-password', [ResetPasswordController::class, 'sendEmail'])->name('password.email');

    //带地址栏参数的路由，把token参数放在URL中，email参数放在查询字符串中
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'resetHandler'])->name('password.update');     


    });
    
// ------------Authenticated-------------//
Route::middleware('auth')->group(function () {
        // ------------------- Logout -------------------//
        Route::post('/logout', [AuthenticateController::class, 'destroy'])->name('logout');

        // ------------------- 1st Email Verification Notice -------------------//
        // Route::get('/email/verify', function () {
        //     return view('auth.verify-email');
        // })->middleware('auth')->name('verification.notice');
        Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');

        // ------------------- 2nd Email Verification handler -------------------//
        
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'handler'])->middleware('signed')->name('verification.verify');

        // ------------------- 3rd Email Verification handler -------------------//
        Route::post('/email/verification-notification',[EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');



        //------------------- Password Confirmation -------------------//
        Route::get('/confirm-password', [ConfirmPasswordController::class, 'create'])->name('password.confirm');
        Route::post('/confirm-password', [ConfirmPasswordController::class, 'store'])->middleware('throttle:6,1');
});