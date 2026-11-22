<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::post('/v1/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post(
    '/v1/logout',
    [AuthController::class, 'logout']
);

//Contact Route
Route::get('/v1/contacts', [ContactController::class, 'index']);

Route::post('/v1/contacts', [ContactController::class, 'store']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
});