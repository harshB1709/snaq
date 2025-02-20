<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'score'
    ];

    /**
     * Get the player that owns the Game
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function gameQuestions(): HasMany
    {
        return $this->hasMany(GameQuestion::class);
    }

    public function questions() {
        return $this->belongsToMany(Question::class, 'game_questions')->withPivot('score');
    }
}
