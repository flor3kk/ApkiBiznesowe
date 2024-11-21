<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ActorController;

Route::apiResource('movies', MovieController::class);
Route::apiResource('actors', ActorController::class);
Route::get('/movies/{movie}/actors', [MovieController::class, 'getMovieCast']);
Route::post('/movies/{movie}/actors', [MovieController::class, 'addActorToMovie']);
Route::delete('/movies/{movie}/actors/{actor}', [MovieController::class, 'removeActorFromMovie']);

