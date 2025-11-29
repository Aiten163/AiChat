<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::middleware('web')->group(function () {
    Route::post('/support', [ReportController::class, 'store'])->name('api.support.store');
});
