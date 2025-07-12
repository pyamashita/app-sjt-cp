<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionContent;
use App\Models\Player;
use Illuminate\Http\Request;

class GuideCollectionController extends Controller
{
    public function view(Request $request, Collection $collection)
    {
        // コレクションの詳細を読み込み
        $collection->load(['fields']);
        
        // エミュレート選手ID
        $emulatePlayerId = $request->get('emulate_player_id');
        $emulatePlayer = null;
        
        if ($emulatePlayerId) {
            $emulatePlayer = Player::find($emulatePlayerId);
        }
        
        // アクセス制御チェック
        $competitionId = null;
        $playerId = null;
        
        if ($collection->is_competition_managed) {
            // 大会管理の場合は、ガイドページの大会IDを使用
            // ここでは仮で現在のリクエストから取得（実際はガイドページのコンテキストが必要）
            $competitionId = $request->get('competition_id');
        }
        
        if ($collection->is_player_managed) {
            if ($emulatePlayer) {
                // エミュレート中は指定された選手
                $playerId = $emulatePlayer->id;
                $competitionId = $request->get('competition_id') ?? $collection->competition_id ?? null;
            } else {
                // 実際の運用では、IPアドレスベースでの選手識別が必要
                // プレビューでは選手未選択状態を表示
                return view('guide.collection', [
                    'collection' => $collection,
                    'needsPlayerSelection' => true,
                    'emulatePlayer' => null,
                    'contents' => collect(),
                    'groupedContents' => collect()
                ]);
            }
        }
        
        // コンテンツ取得
        $query = CollectionContent::with(['field', 'competition', 'player'])
            ->where('collection_id', $collection->id);
            
        if ($competitionId) {
            $query->where('competition_id', $competitionId);
        }
        
        if ($playerId) {
            $query->where('player_id', $playerId);
        }
        
        $contents = $query->orderBy('created_at', 'desc')->get();
        
        // コンテンツをグループ化
        $groupedContents = [];
        
        if ($collection->is_player_managed) {
            // 選手でグループ化
            $groupedContents = $contents->groupBy(function ($item) {
                return $item->competition_id . '-' . $item->player_id;
            })->map(function ($group) {
                $first = $group->first();
                return [
                    'key' => $first->player ? $first->player->name : '未設定',
                    'competition' => $first->competition,
                    'player' => $first->player,
                    'items' => $group,
                    'updated_at' => $group->max('updated_at')
                ];
            })->sortByDesc('updated_at')->values();
        } else {
            // 表示順1のフィールドでグループ化
            $firstField = $collection->fields()->where('sort_order', 1)->first();
            
            if ($firstField) {
                $contexts = $contents->map(function ($item) {
                    return $item->competition_id . '-' . $item->player_id;
                })->unique();
                
                foreach ($contexts as $context) {
                    list($compId, $playerId) = explode('-', $context);
                    $compId = $compId ?: null;
                    $playerId = $playerId ?: null;
                    
                    $contextContents = $contents->filter(function ($item) use ($compId, $playerId) {
                        return $item->competition_id == $compId && $item->player_id == $playerId;
                    });
                    
                    $firstFieldContent = $contextContents->where('field_id', $firstField->id)->first();
                    $keyValue = $firstFieldContent ? $firstFieldContent->formatted_value : '未設定';
                    
                    $groupedContents[] = [
                        'key' => $keyValue,
                        'competition' => $contextContents->first()->competition,
                        'player' => null,
                        'items' => $contextContents,
                        'updated_at' => $contextContents->max('updated_at')
                    ];
                }
                
                $groupedContents = collect($groupedContents)->sortByDesc('updated_at')->values();
            }
        }
        
        return view('guide.collection', compact('collection', 'contents', 'groupedContents', 'emulatePlayer'));
    }
}
