<?php

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\Admin;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\BomController;

// Route::get('/', function () {
//     dd('Laravel 正常运行'); // 浏览器能看到这句话就代表框架没问题
// });
// echo 'Hello World'; // 这行代码不会被执行，因为上面已经有了一个返回响应的语句
Route::inertia('/', 'Home')->name('home');
Route::inertia('/v1', 'verifiedPages/V1')->middleware('verified')->name('approvedbtn1');
Route::inertia('/v2', 'verifiedPages/V2')->middleware('verified')->name('approvedbtn2');

// Route::inertia('/dashboard', 'Dashboard')->middleware(['auth', 'verified'])->name('dashboard');
// Route::inertia('/profile', 'Profile/Edit')->middleware(['auth', 'password.confirm'])->name('profile.edit');

// Admin Routes*************DashboardController************ProfileController*****************
//User Profile Routes, 本组对DashboardController和ProfileController两个控制器的方法进行路由
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'updateInfo'])->name('profile.info');
    Route::put('/profile', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Listing Routes
Route::get('/', [ListingController::class, 'index'])->name('home');
Route::resource('listing', ListingController::class)->except('index');

// Admin Routes*************AdminController******************
//Admin Routes, 此时还可以通过直接在地址栏输入/admin访问到admin面板，所以接下来要加中间件进行控制。
// Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
// // 优化添加middleware保护机制,进行保护后如果地址栏访问会返回404
// route::middleware(['auth','verified'])->group(function(){
//     Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
// });
//再次优化路由写法，本组仅对单个控制器AdminController的方法进行路由，可能以把控制器提出来简化写法如下，此时只能保护未能录用户不能通过地址栏访问
// route::middleware(['auth', 'verified'])
//         ->controller(AdminController::class)
//         ->group(function () {
//             Route::get('/admin', 'index')->name('admin.index');
//         });
//创建自定义中间件Admin后在middleware后面参数上追加Admin中间件类
route::middleware(['auth', 'verified', Admin::class])
    ->controller(AdminController::class)
    ->group(function () {
        Route::get('/admin', 'index')->name('admin.index');
        Route::put('/admin/{user}/role', 'role')->name('admin.role');
        Route::get('/users/{user}', 'show')->name('user.show');
        Route::get('/admin/details/{user}', 'details')->name('admin.details');
        Route::put('/listing/{listing}/approveListing', 'approveListing')->name('admin.approveListing');
    });

//EXCERCISE Routes  ******************exerciseController******************
Route::get('/exercise', [ExerciseController::class, 'index'])->name('exercise.index');
Route::get('/exercise/dragDarkLight', [ExerciseController::class, 'dragDarkLight'])->name('exercise.dragDarkLight');
//多页共用一个控制器方法，参数为页码，在控制器方法中根据页码返回不同的视图
Route::get('/exercise/{page}', [ExerciseController::class, 'showPage'])->name('exercise.showPage');

//exercise控制器上传图片的路由
Route::patch('/exercise/storeImages', [ExerciseController::class, 'storeImages'])->name('exercise.storeImages');


//BOM Routes  ******************BomController******************
Route::get('/bom/edit/{versionId}', [BomController::class, 'edit'])->name('bom.edit');
Route::post('/bom/save/{versionId}', [BomController::class, 'save'])->name('bom.save');
Route::post('/bom/copy/{versionId}', [BomController::class, 'copyVersion'])->name('bom.copy');


//Auth Routes,引入的独立文件
require __DIR__.'/auth.php';

