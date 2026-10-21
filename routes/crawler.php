<?php

use App\Http\Controllers\CrawlTaskController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->prefix('crawler')->name('crawler.')->group(function () {
    Route::get('/', [CrawlTaskController::class, 'index'])->name('index');
    Route::get('/create', [CrawlTaskController::class, 'create'])->name('create');
    Route::post('/', [CrawlTaskController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('store');
    Route::post('/preview', [CrawlTaskController::class, 'preview'])
        ->middleware('throttle:20,1')
        ->name('preview');

    Route::get('/{task}', [CrawlTaskController::class, 'show'])->whereNumber('task')->name('show');
    Route::post('/{task}/cancel', [CrawlTaskController::class, 'cancel'])->whereNumber('task')->name('cancel');
    Route::get('/{task}/download', [CrawlTaskController::class, 'download'])->whereNumber('task')->name('download');
    Route::delete('/{task}', [CrawlTaskController::class, 'destroy'])->whereNumber('task')->name('destroy');
});