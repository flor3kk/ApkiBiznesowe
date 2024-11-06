<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;

// Trasa dla hoteli
Route::apiResource('hotels', HotelController::class);

// Shallow nesting dla pokoi w hotelach
Route::resource('rooms', RoomController::class)->shallow();

