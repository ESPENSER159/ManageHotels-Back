<?php

use App\Http\Controllers\HotelController;
use App\Http\Controllers\HotelRoomController;
use Illuminate\Support\Facades\Route;

Route::apiResource('hotels', HotelController::class);

Route::prefix('hotels/{hotel}')->group(function () {
    Route::apiResource('rooms', HotelRoomController::class)->except(['show']);
    Route::get('rooms/{room}', [HotelRoomController::class, 'show']);
});