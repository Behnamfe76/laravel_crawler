<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\JobController;
use App\Http\Controllers\v1\CrawlerApiController;
use App\Http\Controllers\SeleniumDriverController;


Route::prefix('/v1')->group(function () {
    Route::prefix('/get-categories')->group(callback: function () {
        Route::post('/testing', [CrawlerApiController::class, 'getCategoriesTesting']);
        Route::post('/crawling', [CrawlerApiController::class, 'getCategoriesCrawling']);
    });


    Route::prefix('/monitoring')->group(callback: function () {
        Route::post('/check-job-status', [JobController::class, 'checkJobStatus']);
        Route::post('/check-seleniums-status', [SeleniumDriverController::class, 'checkSeleniumsStatus']);
    });

});
