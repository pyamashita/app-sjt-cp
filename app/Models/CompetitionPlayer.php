<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionPlayer extends Model
{
    protected $fillable = [
        'competition_id',
        'player_id',
        'player_number',
    ];

    /**
     * 大会とのリレーション
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * 選手とのリレーション
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * 検索スコープ
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('player', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('prefecture', 'like', "%{$search}%")
              ->orWhere('affiliation', 'like', "%{$search}%");
        })->orWhere('player_number', 'like', "%{$search}%");
    }

    /**
     * 大会でフィルタリング
     */
    public function scopeByCompetition($query, $competitionId)
    {
        return $query->where('competition_id', $competitionId);
    }

    /**
     * 選手番号でソート
     */
    public function scopeOrderByPlayerNumber($query, $direction = 'asc')
    {
        return $query->orderBy('player_number', $direction);
    }

    /**
     * 選手名でソート
     */
    public function scopeOrderByPlayerName($query, $direction = 'asc')
    {
        return $query->join('players', 'competition_players.player_id', '=', 'players.id')
            ->orderBy('players.name', $direction)
            ->select('competition_players.*');
    }
}