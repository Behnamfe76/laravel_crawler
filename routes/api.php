<?php

use App\Http\Controllers\v1\CrawlerApiController;
use Illuminate\Support\Facades\Route;


Route::prefix('/v1')->group(function () {
    Route::prefix('/get-categories')->group(callback: function () {
        Route::post('/testing', [CrawlerApiController::class, 'getCategoriesTesting']);
        Route::post('/crawling', [CrawlerApiController::class, 'getCategoriesCrawling']);
    });
});
