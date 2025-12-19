<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminRoomController;

Route::get('/', function () {
    return view('welcome');
});

// WEB ADMIN (bukan API)
Route::prefix('admin')
    ->middleware(['auth', 'isAdmin'])
    ->group(function () {
        Route::resource('rooms', AdminRoomController::class, ['as' => 'admin']);
    });
