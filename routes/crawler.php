<?php

use App\Http\Controllers\CrawlTaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('crawler')->name('crawler.')->group(function () {
    Route::get('/create', [CrawlTaskController::class, 'create'])->name('create');
    Route::post('/preview', [CrawlTaskController::class, 'preview'])
        ->middleware('throttle:20,1')
        ->name('preview');

    // 后续步骤再加：index / store / show / cancel / download
});
