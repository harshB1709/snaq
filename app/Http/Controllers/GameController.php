<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\Difficulty;
use App\Models\Event;
use App\Models\Game;
use App\Models\Player;
use App\Models\Question;
use Inertia\Inertia;
use Storage;

class GameController extends Controller
{
    private array $points_distribution;
    private int $time_bonus;

    public function __construct() {
        $this->points_distribution = config('app.game.points_distribution');
        $this->time_bonus = config('app.game.time_bonus');
    }

    public function gamePage(Request $request, Event $event, Player $player) {
        $request->session()->forget(['questions', 'points_scored', 'current_index', 'speedTimeout', 'started_at', 'lives']);
        if($player->event_id !== $event->id) {
            abort(400, 'Sorry, this link isn\'t valid');
        }

        $allow_replay = false;
        $app_settings = $event->appSettings->keyBy('key')->toArray();
        if($app_settings && array_key_exists('allow_replay', $app_settings))
            $allow_replay = $app_settings['allow_replay'] ?? false;

        if($allow_replay || $request->user()) {
            Game::where('player_id', session('player_id'))->delete();
        }

        if (!$request->hasValidSignature() && !$request->user()) {
            abort(401);
        }

        if($player->game) {
            abort(400, 'Sorry, you have already played the game. This link isn\'t valid anymore');
        }

        if(!$request->user() && !is_null($player->invite_expires_at) && $player->invite_expires_at < now()) {
            abort(400, 'Sorry, this link has expired.');
        }

        $initial_speed_timeout = config('app.game.initial_speed_timeout');

        session([
            'player_id' => $player->id,
            'speedTimeout' => $initial_speed_timeout
        ]);

        return Inertia::render('Game', [
            'totalQuestions' => config('app.game.total_questions'),
            'speedTimeout' => $initial_speed_timeout,
            'cooldownTime' => config('app.game.cooldown_time'),
            'lives' => config('app.game.lives'),
            'thresholdScore' => config('app.game.threshold_score')
        ]);
    }

    public function startGame(Request $request, Event $event)
    {
        $diff_cases = Difficulty::cases();
        $difficulties = collect($diff_cases)->map(fn ($d) => $d->value)->toArray();
        $player = Player::findOrFail(session('player_id'));

        if($player->game && !$request->user())
            abort(403);

        $questions = collect();

        foreach ($diff_cases as $difficulty) {
            $questions = $questions->merge(
                Question::inRandomOrder()
                    ->where([
                        ['difficulty', $difficulty],
                        ['is_active', true]
                    ])
                    ->limit(config('app.game.total_questions')/count($difficulties))
                    ->get()
            );
        }

        $game = Game::create([
            'player_id' => $player->id,
            'score' => 0
        ]);

        $questions = $questions->map(function($question, $k) use($difficulties) {

            return [
                'id' => $question->id,
                'question' => $question->question,
                'options' => $this->createOptionsArray(
                    array_unique(array_merge($question->options, [$question->answer])),
                    $k == 0 ? [[1,1]] : []
                ),
                'answer' => $question->answer,
                'points' => $this->points_distribution[$question->difficulty->value] ?? [
                    'correct' => 0,
                    'incorrect' => 0
                ],
                'is_completed' => false
            ];
        })->shuffle();

        $game->questions()->syncWithPivotValues($questions->map(fn ($question) => $question['id'])->toArray(), ['score' => 0]);

        $questions = $questions->toArray();

        session([
            'questions' => $questions,
            'points_scored' => 0,
            'current_index' => 0,
            'speedTimeout' => session('speedTimeout') ?? config('app.game.initial_speed_timeout'),
            'started_at' => now()->addSeconds(3)->timestamp,
            'lives' => config('app.game.lives')
        ]);

        return response()->json([
            'question' => $questions[0]['question'],
            'options' => $questions[0]['options'],
            'speed' => session('speedTimeout'),
        ]);
    }

    public function gameAction(Request $request, Event $event) {
        $action = $request->get('action', 'hitWall');
        $max_timer = config('app.game.timer_seconds');

        if(in_array($action, ['eatFood', 'hitWall', 'hitSelf'])) {
            $question_change = false;
            $game_over = false;
            $questions = session('questions', []);
            $current_index = session('current_index', null);
            $points_scored = session('points_scored', 0);
            $speed_timeout = session('speedTimeout');
            $started_at = session('started_at');
            $lives = session('lives');
            $game_over_message = null;
            $bonus_points = 0;

            switch ($action) {
                case 'eatFood':
                    $color = $request->get('color', '');
                    $question = &$questions[$current_index];
                    $selected_answer = collect($question['options'])
                        ->where(fn ($o) => $o['color'] === $color)
                        ->first();
                    $time_elapsed = max(1, now()->timestamp - $started_at - config('app.game.delay_seconds') - (config('app.game.cooldown_time')/1000));
                    $bonus_points = max(0, config('app.game.bonus_threshold_seconds') - $time_elapsed) * $this->time_bonus;
                    $earned = ($selected_answer['value'] === $question['answer']) ? ($question['points']['correct'] + $bonus_points) : (-1 * $question['points']['incorrect']);
                    $points_scored += $earned;
                    $game = Game::where('player_id', session('player_id'))->first();
                    $game->score = $points_scored;
                    $game->save();
                    $game
                        ->gameQuestions()
                        ->where('question_id', $question['id'])
                        ->update([
                            'score' => $earned
                        ]);
                    $this->incrementIndex($current_index, $question_change, $game_over, $speed_timeout, $started_at);
                    if($game_over)
                        $game_over_message = 'You have answered all the questions.';
                    break;
                case 'hitWall':
                    $lives--;
                    if($lives <= 0) {
                        $game_over = true;
                        $game_over_message = 'Game Over! You hit the wall.';
                    }
                    break;
                case 'hitSelf':
                    $lives--;
                    if($lives <= 0) {
                        $game_over = true;
                        $game_over_message = 'Game Over! You collided with yourself.';
                    }
                    break;
            }

            $curr_ques = null;

            if(!$game_over) {
                session([
                    'points_scored' => $points_scored,
                    'current_index' => $current_index,
                    'speedTimeout' => $speed_timeout,
                    'started_at' => $started_at,
                    'lives' => $lives
                ]);

                $curr_ques = $questions[$current_index];
            }
            else {
                $player = Player::find(session('player_id'));
                if($player) {
                    $player->games_count++;
                    $player->save();
                }
                $request->session()->forget(['questions', 'points_scored', 'current_index', 'speedTimeout', 'started_at']);
            }

            return response()->json([
                'points' => $points_scored,
                'bonus_points' => $bonus_points,
                'question' => $curr_ques['question'] ?? null,
                'options' => $curr_ques['options'] ?? null,
                'speedTimeout' => $speed_timeout,
                'gameOver' => $game_over,
                'gameOverMessage' => $game_over_message,
                'question_change' => $question_change,
                'lives' => $lives
            ]);
        }
        abort(404);
    }

    private function incrementIndex(&$current_index, &$question_change, &$game_over, &$speed_timeout, &$started_at) {
        $current_index++;
        $question_change = $current_index <= (config('app.game.total_questions') - 1);
        $game_over = $current_index > (config('app.game.total_questions') - 1);
        $speed_timeout -= config('app.game.speed_timeout_difference');
        $started_at = now()->timestamp;
    }

    private function getCoordinates(array $avoid = [[1,1]], int $quantity = 4) {
        $gridSize = 20;
        $allCoordinates = collect(range(0, $gridSize - 1))->crossJoin(range(0, $gridSize - 1))->filter(fn ($item) => !in_array($item, $avoid));
        $shuffledCoordinates = $allCoordinates->shuffle();
        $selectedCoordinates = $shuffledCoordinates->take($quantity);

        return $selectedCoordinates;
    }

    private function createOptionsArray($options, array $avoid = []) {
        $colors = ['#D81159', '#218380', '#22007C', '#481D24'];
        $colors = collect($colors)->shuffle();
        $positions = $this->getCoordinates(quantity: count($options), avoid: $avoid);
        
        $options_array = collect($options)->map(fn($item, $key) => [
            'value' => $item,
            'position' => $positions[$key],
            'color' => $colors[$key]
        ])->shuffle();

        return $options_array;
    }

    public function leaderboard(Event $event, Request $request) {
        $games = Game::withWhereHas('player', fn ($query) => $query->where('event_id', $event->id))->select('id','event_id','name','display_name')
                    ->select('id', 'player_id', 'score')
                    ->orderByDesc('score')
                    ->paginate(25);

        $games->onEachSide(0)->links();

        return Inertia::render('Event/Leaderboard', [
            'games' => $games,
        ]);
    }
}
