<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\GameController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'welcome']);
Route::get('/game', [HomeController::class, 'game']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::prefix("{event:slug}")->group(function () {
    // Route::get('/', [HomeController::class, 'home'])->name('home');
    // Route::get('/leaderboard', [PlayerController::class, 'leaderboard'])->middleware(['app.setting:show_leaderboard'])->name('leaderboard');

    Route::middleware(['app.setting:app_status'])->group(function() {
        // Route::get('/register', [PlayerController::class, 'home'])->middleware(['app.setting:player_registration', 'device_allowed'])->name('player-register');
        Route::get('/{player}/game', [GameController::class, 'gamePage'])->middleware(['device_allowed'])->name('game');

        // Route::middleware(['player.identified'])->group(function() {
        //     Route::post('/start-game', [PlayerController::class, 'startGame'])->name('start-game');
        //     Route::post('/game-action', [PlayerController::class, 'gameAction'])->middleware(['game.ongoing'])->name('game-action');
        // });
    });
});