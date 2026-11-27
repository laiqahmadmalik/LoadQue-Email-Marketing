
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactController;

// Authenticated user information
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public authentication route
Route::post('/v1/login', [AuthController::class, 'login']);

// Protected API routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

// Authentication
Route::post('/logout', [AuthController::class, 'logout']);

// Contact management
Route::get('/contacts', [ContactController::class, 'index']);
Route::get('/contacts/{id}', [ContactController::class, 'show'])->whereNumber('id');
Route::get('/contacts/deleted', [ContactController::class, 'deleted']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::put('/contacts/{id}', [ContactController::class, 'update']);
Route::post('/contacts/{id}/restore', [ContactController::class, 'restore']);
Route::delete('/contacts/{id}/permanent', [ContactController::class, 'forceDestroy']);
Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

});
