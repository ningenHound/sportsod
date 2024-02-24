<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', function () {
    return ['message' => 'SportsOD - sports on demand'];
});

Route::controller(VenueController::class)->group(function () {
    Route::get('/venue/{id}', 'read');
    Route::post('/venue/create', 'create');
    Route::put('/venue/{id}', 'update');
    Route::delete('/venue/{id}', 'delete');
    Route::get('/venue/{id}/fields', 'listFields');
    Route::get('/venues-all', 'listVenues');
});

Route::controller(FieldController::class)->group(function () {
    Route::get('/field/{id}', 'read');
    Route::post('/field/create', 'create');
    Route::put('/field/{id}', 'update');
    Route::delete('/field/{id}', 'delete');
    Route::get('/field/{id}/bookings', 'listBookingsByField');
});

Route::controller(BookingController::class)->group(function () {
    Route::get('/booking/{id}', 'read');
    Route::post('/booking/create', 'create');
    Route::put('/booking/{id}', 'update');
    Route::delete('/booking/{id}', 'delete');
    Route::get('/field/{id}/bookings', 'listBookingsByField');
    Route::post('/active-bookings', 'listActiveBookings');
});

Route::controller(UserController::class)->group(function () {
    Route::get('/user/{id}', 'read');
    Route::post('/user/create', 'create');
    Route::put('/user/{id}', 'update');
    Route::delete('/user/{id}', 'delete');
    Route::get('/user/{id}/bookings', 'listBookingsByUser');
    Route::post('/user/login', 'login');
});
