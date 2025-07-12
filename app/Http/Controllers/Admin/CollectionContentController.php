<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionContent;
use App\Models\Competition;
use App\Models\Player;
use App\Models\Resource;
use Illuminate\Http\Request;

class CollectionContentController extends Controller
{
    public function index(Collection $collection, Request $request)
    {
        $query = CollectionContent::with(['field', 'competition', 'player'])
            ->where('collection_id', $collection->id);

        // 大会フィルタ
        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }

        // 選手フィルタ
        if ($request->filled('player_id')) {
            $query->where('player_id', $request->player_id);
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
                // まず全コンテキストを取得
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
                
                // 更新日時で並び替え
                $groupedContents = collect($groupedContents)->sortByDesc('updated_at')->values();
            }
        }

        // フィルタ用データ
        $competitions = Competition::orderBy('name')->get();
        $players = Player::orderBy('name')->get();

        return view('admin.collections.contents.index', compact('collection', 'groupedContents', 'competitions', 'players'));
    }

    public function create(Collection $collection, Request $request)
    {
        $collection->load('fields');
        
        // コンテキスト情報
        $competitionId = $request->competition_id;
        $playerId = $request->player_id;
        
        $competition = $competitionId ? Competition::find($competitionId) : null;
        
        // 選手データの取得（選手番号も含める）
        if ($playerId && $competitionId) {
            $player = Player::select('players.*', 'competition_players.player_number')
                ->leftJoin('competition_players', function ($join) use ($competitionId) {
                    $join->on('players.id', '=', 'competition_players.player_id')
                         ->where('competition_players.competition_id', '=', $competitionId);
                })
                ->where('players.id', $playerId)
                ->first();
        } else {
            $player = $playerId ? Player::find($playerId) : null;
        }

        // 既存データの取得
        $existingData = CollectionContent::where('collection_id', $collection->id)
            ->where('competition_id', $competitionId)
            ->where('player_id', $playerId)
            ->with('field')
            ->get()
            ->keyBy('field_id');

        // 選択可能なデータ
        $competitions = Competition::orderBy('name')->get();
        
        // 選手データの取得（大会が指定されている場合は選手番号も含める）
        if ($competitionId) {
            $players = Player::select('players.*', 'competition_players.player_number')
                ->leftJoin('competition_players', function ($join) use ($competitionId) {
                    $join->on('players.id', '=', 'competition_players.player_id')
                         ->where('competition_players.competition_id', '=', $competitionId);
                })
                ->orderBy('competition_players.player_number')
                ->orderBy('players.name')
                ->get();
        } else {
            $players = Player::orderBy('name')->get();
        }
        
        $resources = Resource::orderBy('original_name')->get();

        return view('admin.collections.contents.create', compact(
            'collection', 'competition', 'player', 'existingData', 
            'competitions', 'players', 'resources'
        ));
    }

    public function store(Request $request, Collection $collection)
    {
        $collection->load('fields');

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

        foreach ($collection->fields as $field) {
            $fieldName = "field_{$field->id}";
            $rules[$fieldName] = $field->validation_rules;
        }

        $validated = $request->validate($rules);

        // 既存データの削除
        CollectionContent::where('collection_id', $collection->id)
            ->where('competition_id', $competitionId)
            ->where('player_id', $playerId)
            ->delete();

        // 新しいデータの保存
        foreach ($collection->fields as $field) {
            $fieldName = "field_{$field->id}";
            $value = $validated[$fieldName] ?? null;

            if ($value !== null && $value !== '') {
                // boolean型の変換
                if ($field->content_type === 'boolean') {
                    $value = $value ? '1' : '0';
                }

                CollectionContent::create([
                    'collection_id' => $collection->id,
                    'field_id' => $field->id,
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

        return redirect()->route('admin.collections.contents.index', $collection)
            ->with('success', "コンテンツを保存しました{$contextMessage}。");
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

        $deleted = CollectionContent::where('collection_id', $collection->id)
            ->where('competition_id', $competitionId)
            ->where('player_id', $playerId)
            ->delete();

        if ($deleted > 0) {
            return response()->json([
                'success' => true,
                'message' => 'コンテンツを削除しました。'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '削除するコンテンツが見つかりません。'
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
        if ($request->filled('competition_id')) {
            // 大会に登録されている選手を選手番号付きで取得
            $players = Player::select('players.*', 'competition_players.player_number')
                ->join('competition_players', 'players.id', '=', 'competition_players.player_id')
                ->where('competition_players.competition_id', $request->competition_id)
                ->orderBy('competition_players.player_number')
                ->orderBy('players.name')
                ->get();
        } else {
            // 全選手を取得
            $players = Player::orderBy('name')->get();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $players = $players->filter(function ($player) use ($search) {
                return stripos($player->name, $search) !== false || 
                       stripos($player->name_kana ?? '', $search) !== false;
            });
        }

        return response()->json([
            'players' => $players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'name' => $player->name,
                    'name_kana' => $player->name_kana ?? '',
                    'player_number' => $player->player_number ?? null,
                ];
            })
        ]);
    }

    public function export(Collection $collection, Request $request)
    {
        $query = CollectionContent::with(['field', 'competition', 'player'])
            ->where('collection_id', $collection->id);

        // フィルタ適用
        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }

        if ($request->filled('player_id')) {
            $query->where('player_id', $request->player_id);
        }

        $contents = $query->orderBy('created_at', 'desc')->get();

        // CSVデータを準備
        $csvData = [];
        
        // ヘッダー行
        $headers = ['ID'];
        if ($collection->is_competition_managed) {
            $headers[] = '大会名';
        }
        if ($collection->is_player_managed) {
            $headers[] = '選手番号';
            $headers[] = '選手名';
        }
        $headers = array_merge($headers, ['フィールド名', 'フィールドタイプ', '値', '作成日時', '更新日時']);
        $csvData[] = $headers;

        // データ行
        foreach ($contents as $content) {
            $row = [$content->id];
            
            if ($collection->is_competition_managed) {
                $row[] = $content->competition ? $content->competition->name : '';
            }
            
            if ($collection->is_player_managed) {
                // 選手番号を取得
                $playerNumber = '';
                if ($content->player && $content->competition) {
                    $competitionPlayer = \App\Models\CompetitionPlayer::where('competition_id', $content->competition_id)
                        ->where('player_id', $content->player_id)
                        ->first();
                    $playerNumber = $competitionPlayer ? $competitionPlayer->player_number : '';
                }
                $row[] = $playerNumber;
                $row[] = $content->player ? $content->player->name : '';
            }
            
            $row[] = $content->field->name;
            $row[] = $content->field->content_type_display_name;
            $row[] = $content->formatted_value;
            $row[] = $content->created_at->format('Y-m-d H:i:s');
            $row[] = $content->updated_at->format('Y-m-d H:i:s');
            
            $csvData[] = $row;
        }

        // CSVファイルを生成
        $csv = '';
        foreach ($csvData as $row) {
            $csv .= implode(',', array_map(function($field) {
                // フィールドに特殊文字が含まれる場合はダブルクォートで囲む
                if (strpos($field, ',') !== false || strpos($field, "\n") !== false || strpos($field, '"') !== false) {
                    $field = '"' . str_replace('"', '""', $field) . '"';
                }
                return $field;
            }, $row)) . "\n";
        }

        // BOMを追加してExcelで正しく文字化けせずに開けるようにする
        $csv = "\xEF\xBB\xBF" . $csv;

        $filename = "collection_{$collection->name}_contents_" . date('Y-m-d_H-i-s') . '.csv';
        $encodedFilename = rawurlencode($filename);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
    }
}