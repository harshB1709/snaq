<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Event;

class HomeController extends Controller
{
    public function welcome(Request $request) {
        $event = Event::where('end_date', '>=', today())->first();
        if($event)
            return redirect(route('home', ['event' => $event]));
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function home(Request $request, Event $event) {
        $self_registration = $event->appSettings()->where([
            'key' => 'player_registration'
        ])->first() ?? [
            'value' => true,
            'message' => ''
        ];

        return Inertia::render('Event/Home', [
            'registrationSetting' => $self_registration
        ]);
    }
}
