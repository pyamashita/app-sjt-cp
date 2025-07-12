<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionData;
use App\Models\Competition;
use App\Models\Player;
use App\Models\Resource;
use Illuminate\Http\Request;

class CollectionDataController extends Controller
{
    public function index(Collection $collection, Request $request)
    {
        $query = CollectionData::with(['content', 'competition', 'player'])
            ->where('collection_id', $collection->id);

        // 大会フィルタ
        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }

        // 選手フィルタ
        if ($request->filled('player_id')) {
            $query->where('player_id', $request->player_id);
        }

        $data = $query->orderBy('created_at', 'desc')->paginate(20);

        // フィルタ用データ
        $competitions = Competition::orderBy('name')->get();
        $players = Player::orderBy('name')->get();

        return view('admin.collections.data.index', compact('collection', 'data', 'competitions', 'players'));
    }

    public function create(Collection $collection, Request $request)
    {
        $collection->load('contents');
        
        // コンテキスト情報
        $competitionId = $request->competition_id;
        $playerId = $request->player_id;
        
        $competition = $competitionId ? Competition::find($competitionId) : null;
        $player = $playerId ? Player::find($playerId) : null;

        // 既存データの取得
        $existingData = CollectionData::where('collection_id', $collection->id)
            ->where('competition_id', $competitionId)
            ->where('player_id', $playerId)
            ->with('content')
            ->get()
            ->keyBy('content_id');

        // 選択可能なデータ
        $competitions = Competition::orderBy('name')->get();
        $players = Player::orderBy('name')->get();
        $resources = Resource::orderBy('original_name')->get();

        return view('admin.collections.data.create', compact(
            'collection', 'competition', 'player', 'existingData', 
            'competitions', 'players', 'resources'
        ));
    }

    public function store(Request $request, Collection $collection)
    {
        $collection->load('contents');

        // コンテキスト検証
        $competitionId = $request->competition_id;
        $playerId = $request->player_id;

        if ($collection->is_competition_managed && !$competitionId) {
            return back()->withErrors(['competition_id' => '大会を選択してください。']);
        }

        if ($collection->is_player_managed && !$playerId) {
            return back()->withErrors(['player_id' => '選手を選択してください。']);
        }

        // バリデーションルール動的生成
        $rules = [
            'competition_id' => $collection->is_competition_managed ? 'required|exists:competitions,id' : 'nullable',
            'player_id' => $collection->is_player_managed ? 'required|exists:players,id' : 'nullable',
        ];

        foreach ($collection->contents as $content) {
            $fieldName = "content_{$content->id}";
            $rules[$fieldName] = $content->validation_rules;
        }

        $validated = $request->validate($rules);

        // 既存データの削除
        CollectionData::where('collection_id', $collection->id)
            ->where('competition_id', $competitionId)
            ->where('player_id', $playerId)
            ->delete();

        // 新しいデータの保存
        foreach ($collection->contents as $content) {
            $fieldName = "content_{$content->id}";
            $value = $validated[$fieldName] ?? null;

            if ($value !== null && $value !== '') {
                // boolean型の変換
                if ($content->content_type === 'boolean') {
                    $value = $value ? '1' : '0';
                }

                CollectionData::create([
                    'collection_id' => $collection->id,
                    'content_id' => $content->id,
                    'competition_id' => $competitionId,
                    'player_id' => $playerId,
                    'value' => $value,
                ]);
            }
        }

        $contextMessage = '';
        if ($collection->is_player_managed && $playerId) {
            $player = Player::find($playerId);
            $competition = Competition::find($competitionId);
            $contextMessage = " ({$competition->name} - {$player->name})";
        } elseif ($collection->is_competition_managed && $competitionId) {
            $competition = Competition::find($competitionId);
            $contextMessage = " ({$competition->name})";
        }

        return redirect()->route('admin.collections.data.index', $collection)
            ->with('success', "データを保存しました{$contextMessage}。");
    }

    public function edit(Collection $collection, Request $request)
    {
        // create メソッドと同じロジックを使用
        return $this->create($collection, $request);
    }

    public function destroy(Collection $collection, Request $request)
    {
        $competitionId = $request->competition_id;
        $playerId = $request->player_id;

        $deleted = CollectionData::where('collection_id', $collection->id)
            ->where('competition_id', $competitionId)
            ->where('player_id', $playerId)
            ->delete();

        if ($deleted > 0) {
            return response()->json([
                'success' => true,
                'message' => 'データを削除しました。'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '削除するデータが見つかりません。'
        ], 404);
    }

    public function getCompetitions(Request $request)
    {
        $query = Competition::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $competitions = $query->orderBy('name')->get();

        return response()->json([
            'competitions' => $competitions->map(function ($competition) {
                return [
                    'id' => $competition->id,
                    'name' => $competition->name,
                    'year' => $competition->year,
                    'status' => $competition->status,
                ];
            })
        ]);
    }

    public function getPlayers(Request $request)
    {
        $query = Player::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_kana', 'like', "%{$search}%");
            });
        }

        if ($request->filled('competition_id')) {
            // 大会に登録されている選手のみ
            $query->whereHas('competitionPlayers', function ($q) use ($request) {
                $q->where('competition_id', $request->competition_id);
            });
        }

        $players = $query->orderBy('name')->get();

        return response()->json([
            'players' => $players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'name' => $player->name,
                    'name_kana' => $player->name_kana,
                    'number' => $player->number,
                ];
            })
        ]);
    }
}