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
    public function gamePage(Request $request, Event $event, Player $player) {
        $request->session()->forget(['questions', 'points_scored', 'current_index', 'speedTimeout']);
        if($request->user())
            $player->game()?->delete();

        if (!$request->hasValidSignature() && !$request->user()) {
            abort(401);
        }

        if($player->game) {
            abort(400, 'Sorry, you have already played the game. This link isn\'t valid anymore');
        }

        if(!$request->user() && !is_null($player->invite_expires_at) && $player->invite_expires_at < now()) {
            abort(400, 'Sorry, this link has expired.');
        }

        $initial_speed_timeout = 400;

        session([
            'player_id' => $player->id,
            'speedTimeout' => $initial_speed_timeout
        ]);

        return Inertia::render('Game', [
            'totalQuestions' => config('app.total_questions'),
            'speedTimeout' => $initial_speed_timeout
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
                    ->limit(config('app.total_questions')/count($difficulties))
                    ->get()
            );
        }

        $game = Game::create([
            'player_id' => $player->id,
            'score' => 0
        ]);

        $game->questions()->syncWithPivotValues($questions->map(fn ($question) => $question->id)->toArray(), ['score' => 0]);

        $questions = $questions->map(function($question, $k) use($difficulties) {
            return [
                'id' => $question->id,
                'question' => $question->question,
                'options' => $this->createOptionsArray(
                    array_unique(array_merge($question->options, [$question->answer])),
                    $k == 0 ? [[1,1]] : []
                ),
                'answer' => $question->answer,
                'points' => array_search($question->difficulty, $difficulties) + 1,
                'is_completed' => false
            ];
        })->shuffle()->toArray();

        session([
            'questions' => $questions,
            'points_scored' => 0,
            'current_index' => 0,
            'speedTimeout' => session('speedTimeout')
        ]);

        return response()->json([
            'question' => $questions[0]['question'],
            'options' => $questions[0]['options'],
            'speed' => session('speedTimeout'),
        ]);
    }

    public function gameAction(Request $request, Event $event) {
        function incrementIndex(&$current_index, &$question_change, &$game_over, &$speed_timeout) {
            $current_index++;
            $question_change = $current_index <= (config('app.total_questions') - 1);
            $game_over = $current_index > (config('app.total_questions') - 1);
            $speed_timeout -= 20;
        }

        $action = $request->get('action', 'hitWall');
        $max_timer = config('app.timer_seconds');

        if(in_array($action, ['eatFood', 'hitWall', 'hitSelf'])) {
            $question_change = false;
            $game_over = false;
            $questions = session('questions', []);
            $current_index = session('current_index', null);
            $points_scored = session('points_scored', 0);
            $speed_timeout = session('speedTimeout');
            $game_over_message = null;

            switch ($action) {
                case 'eatFood':
                    $color = $request->get('color', '');
                    $question = &$questions[$current_index];
                    $selected_answer = collect($question['options'])
                        ->where(fn ($o) => $o['color'] === $color)
                        ->first();
                    $earned = ($selected_answer['value'] === $question['answer']) ? $question['points'] : (-1);
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
                    incrementIndex($current_index, $question_change, $game_over, $speed_timeout);
                    if($game_over)
                        $game_over_message = 'You have answered all the questions. Please wait for the results.';
                    break;
                case 'hitWall':
                    $game_over = true;
                    $game_over_message = 'Game Over! You hit the wall.';
                    break;
                case 'hitSelf':
                    $game_over = true;
                    $game_over_message = 'Game Over! You collided with yourself.';
                    break;
            }

            $curr_ques = null;

            if(!$game_over) {
                session([
                    'questions' => $questions,
                    'points_scored' => $points_scored,
                    'current_index' => $current_index,
                    'speedTimeout' => $speed_timeout
                ]);

                $curr_ques = $questions[$current_index];
            }
            else {
                $request->session()->forget(['questions', 'points_scored', 'current_index', 'speedTimeout']);
            }

            return response()->json([
                'points' => $points_scored,
                'question' => $curr_ques['question'] ?? null,
                'options' => $curr_ques['options'] ?? null,
                'speedTimeout' => $speed_timeout,
                'gameOver' => $game_over,
                'gameOverMessage' => $game_over_message,
                'question_change' => $question_change
            ]);
        }
        abort(404);
    }

    public function getNextQuestion(Request $request) {
        $sess = $request->session()->all();
        $question = $this->resolveQuestion($sess['generated'] + 1);
        $sess['generated']++;
        if($request->has('pos') && $request->get('pos')) {
            $pos = $request->get('pos');
            $q_no = $sess['answered'];
            if($sess['correct'][$q_no][0] == $pos[0] && $sess['correct'][$q_no][1] == $pos[1]) {
                $sess['score']++;
            }
            else {
                $sess['score']--;
            }
            logger($sess['score']);
            $sess['answered']++;
        }
        array_push($sess['correct'], $question['correct_position']);
        array_push($sess['avoid'][$question['difficulty']], $question['index']);
        session($sess);

        return response()->json([
            'question' => [
                'question' => $question['question'],
                'options' => $question['options']
            ],
            'score' => $sess['score']
        ]);
    }

    private function getRandomQuestion(int $difficulty = 1, array $avoid = []) {
        $questions = Storage::disk('private')->get('mixed_questions.json');
        $questions = collect(json_decode($questions, true))[$difficulty] ?? [];
        $q_index = collect()->range(0, count($questions) - 1)->diff($avoid[$difficulty] ?? collect([]))->random();
        return [
            'index' => $q_index,
            'question' => $questions[$q_index]
        ];
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
        ]);

        return $options_array;
    }
}
