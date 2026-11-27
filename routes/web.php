<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/contacts', function () {
        return view('contacts.index');
    })->name('contacts.index');

    Route::get('/contacts/deleted', function () {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('contacts.deleted');
    })->name('contacts.deleted');

});
