<?php

namespace App\Http\Controllers;

use App\Models\GuidePage;
use App\Models\Player;
use Illuminate\Http\Request;

class PublicGuideController extends Controller
{
    public function show(Request $request, $competitionId)
    {
        // 有効なガイドページを取得
        $guidePage = GuidePage::with(['competition', 'sections.groups.items.resource', 'sections.groups.items.collection'])
            ->where('competition_id', $competitionId)
            ->where('is_active', true)
            ->first();
            
        if (!$guidePage) {
            abort(404, 'ガイドページが見つかりません');
        }

        // 実際の運用では、IPアドレスベースでの選手識別
        // 今回はクエリパラメータで対応（デモ用）
        $emulatePlayerId = $request->get('player_id');
        $emulatePlayer = null;
        
        if ($emulatePlayerId) {
            $emulatePlayer = Player::select('players.*', 'competition_players.player_number')
                ->leftJoin('competition_players', function ($join) use ($competitionId) {
                    $join->on('players.id', '=', 'competition_players.player_id')
                         ->where('competition_players.competition_id', '=', $competitionId);
                })
                ->where('players.id', $emulatePlayerId)
                ->first();
        }
        
        // 選手管理されているコレクションがあるかチェック
        $hasPlayerManagedCollections = false;
        foreach ($guidePage->sections as $section) {
            foreach ($section->groups as $group) {
                foreach ($group->items as $item) {
                    if ($item->type === 'collection' && $item->collection && $item->collection->is_player_managed) {
                        $hasPlayerManagedCollections = true;
                        break 3;
                    }
                }
            }
        }
        
        // 選手選択用の一覧（デバッグ・デモ用）
        $availablePlayers = collect();
        if ($hasPlayerManagedCollections) {
            $availablePlayers = Player::select('players.*', 'competition_players.player_number')
                ->join('competition_players', 'players.id', '=', 'competition_players.player_id')
                ->where('competition_players.competition_id', $competitionId)
                ->orderBy('competition_players.player_number')
                ->orderBy('players.name')
                ->get();
        }
        
        return view('guide.public', compact('guidePage', 'emulatePlayer', 'hasPlayerManagedCollections', 'availablePlayers'));
    }
}
