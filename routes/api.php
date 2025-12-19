<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Schema;

Route::get('/check-db', function () {
    return [
        'users_table' => Schema::hasTable('users'),
        'migrations_table' => Schema::hasTable('migrations'),
    ];
});


/*
|--------------------------------------------------------------------------
| TEST
|--------------------------------------------------------------------------
*/
Route::get('/test', function () {
    return ['message' => 'API berjalan'];
});

/*
|--------------------------------------------------------------------------
| PUBLIC AUTH
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // AUTH
    Route::get('/me', fn (Request $req) => $req->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // ROOMS (USER)
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/{room}', [RoomController::class, 'show']);

    // BOOKINGS (USER)
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    // CANCEL BOOKING
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| PUBLIC BOOKINGS (UNTUK CALENDAR / VIEW)
|--------------------------------------------------------------------------
*/
Route::get('/rooms/{id}/bookings', [BookingController::class, 'bookingsByRoomPublic']);

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'is_admin'])
    ->prefix('admin')
    ->group(function () {

        // ROOMS (ADMIN)
        Route::get('/rooms', [RoomController::class, 'index']);
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::get('/rooms/{room}', [RoomController::class, 'show']);
        Route::put('/rooms/{room}', [RoomController::class, 'update']);
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);

        // BOOKINGS (ADMIN)
        Route::get('/bookings', [BookingController::class, 'adminIndex']);
        Route::delete('/bookings/{id}', [BookingController::class, 'adminDelete']);

        Route::post('/bookings/{id}/approve', [BookingController::class, 'approveBooking']);
        Route::post('/bookings/{id}/reject', [BookingController::class, 'rejectBooking']);

        // BOOKING HISTORY
        Route::get('/booking-history', [BookingController::class, 'history']);

        // BOOKINGS BY ROOM
        Route::get('/rooms/{room_id}/bookings', [BookingController::class, 'bookingsByRoom']);

        // PENDING COUNT
        Route::get('/bookings/pending-count', [BookingController::class, 'pendingCount']);

        // NOTIFICATIONS
        Route::get('/notifications', [BookingController::class, 'adminNotifications']);
        Route::post('/notifications/clear', [BookingController::class, 'clearNotifications']);

        // ACTIVE BOOKINGS (OPTIONAL / LEGACY)
        Route::get('/bookings/active', function () {
            return \App\Models\Booking::with('room')
                ->where('status', 'approved')
                ->get();
        });
    });
