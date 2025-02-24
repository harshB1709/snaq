<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\Player;
use App\Notifications\GameInvite;

class PlayerController extends Controller
{
    public function home(Request $request, Event $event) {
        return Inertia::render('Event/Register', [
            'inviteValidityMins' => config('app.game.invite_validity_mins')
        ]);
    }

    public function register(Request $request, Event $event) {
        $request->validate([
            'name' => 'required|string|max:100',
            'display_name' => 'string|max:60|nullable',
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('players', 'email')->where(fn ($query) => $query->where('event_id', $event->id))
            ],
            'phone' => 'required|numeric|digits:10|nullable'
        ]);

        $player = Player::create([
            'event_id' => $event->id,
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'display_name' => $request->get('display_name', null),
            'phone' => $request->get('phone') ?? ''
        ]);

        $player->notify(new GameInvite());

        return response()->json([
            'status' => 'success',
            'message' => 'Player Registered Successfully'
        ]);
    }
}
