<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\Difficulty;
use App\Models\Question;
use Illuminate\Support\Facades\File;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\Storage;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::disk('private')->get('mixed_questions.json');
        $difficulties = json_decode($json);
        $difficulty_values = collect(Difficulty::cases())->map(fn ($v) => $v->value);

        foreach($difficulties as $difficulty => $questions) {
            foreach ($questions as $key => $question) {
                Question::updateOrCreate([
                        "question" => $question->question,
                    ], [
                        "options" => array_merge($question->wrong_answers, [$question->answer]),
                        "answer" => $question->answer,
                        "difficulty" => $difficulty_values[$difficulty - 1],
                        "is_active" => true
                    ]);
            }
        }
    }
}
