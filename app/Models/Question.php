<?php

namespace App\Models;

use App\Casts\Json;
use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'options',
        'answer',
        'difficulty',
        'is_active'
    ];

    protected $casts = [
        "options" => Json::class,
        "difficulty" => Difficulty::class,
        "is_active" => 'boolean'
    ];

}
