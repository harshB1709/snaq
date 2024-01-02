<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Storage;

class HomeController extends Controller
{
    public function welcome(Request $request) {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function game(Request $request) {
        session()->flush();
        $question = $this->resolveQuestion(1);
        $session = [
            'correct' => [$question['correct_position']],
            'avoid' => [
                1 => [$question['index']],
                2 => [],
                3 => []
            ],
            'answered' => 0,
            'generated' => 1,
            'score' => 0
        ];

        session($session);

        return Inertia::render('Game', [
            'firstQuestion' => [
                'question' => $question['question'],
                'options' => $question['options']
            ]
        ]);
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
        $questions = Storage::disk('private')->get('questions.json');
        $questions = collect(json_decode($questions, true))[$difficulty] ?? [];
        $q_index = collect()->range(0, count($questions) - 1)->diff($avoid[$difficulty] ?? collect([]))->random();
        return [
            'index' => $q_index,
            'question' => $questions[$q_index]
        ];
    }

    private function getCoordinates(array $avoid = [[1,1]]) {
        $gridSize = 20;
        $allCoordinates = collect(range(0, $gridSize - 1))->crossJoin(range(0, $gridSize - 1))->filter(fn ($item) => !in_array($item, $avoid));
        $shuffledCoordinates = $allCoordinates->shuffle();
        $selectedCoordinates = $shuffledCoordinates->take(4);

        return $selectedCoordinates;
    }

    private function resolveQuestion(int $question_no) {
        $section_limit = 30;
        $current_difficulty = (floor(($question_no - 1) / $section_limit) + 1) % 3;
        $current_difficulty = $current_difficulty !== 0 ? $current_difficulty : 3;
        $question = $this->getRandomQuestion($current_difficulty);
        $positions = $this->getCoordinates();
        $options = array_merge($question['question']['wrong_answers'], [$question['question']['answer']]);
        $options = collect($options)->map(fn($item, $key) => [
            'value' => $item,
            'position' => $positions[$key]
        ]);

        return [
            'difficulty' => $current_difficulty,
            'question' => $question['question']['question'],
            'index' => $question['index'],
            'correct_position' => $options[3]['position'],
            'options' => $options->shuffle()->values()->toArray()
        ];
    }
}
