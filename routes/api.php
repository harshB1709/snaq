<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['web']], function () {
    Route::get('/next-question', [HomeController::class, 'getNextQuestion']);
});

Route::prefix("{event:slug}")->group(function () {
    Route::post('/register', [PlayerController::class, 'register'])->middleware(['app.setting:player_registration'])->name('register-api');
});