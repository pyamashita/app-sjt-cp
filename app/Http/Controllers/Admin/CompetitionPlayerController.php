<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionPlayer;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CompetitionPlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $competitions = Competition::orderBy('start_date', 'desc')->get();
        $selectedCompetition = null;
        $competitionPlayers = collect();

        if ($request->filled('competition_id')) {
            $selectedCompetition = Competition::find($request->competition_id);
            if ($selectedCompetition) {
                $query = CompetitionPlayer::with(['player'])
                    ->byCompetition($request->competition_id);

                // 検索処理
                if ($request->filled('search')) {
                    $query->search($request->search);
                }

                $competitionPlayers = $query->orderByPlayerNumber()->paginate(15);
            }
        }

        return view('admin.competition-players.index', compact(
            'competitions', 
            'selectedCompetition', 
            'competitionPlayers'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $competitions = Competition::orderBy('start_date', 'desc')->get();
        $selectedCompetition = null;
        $availablePlayers = collect();

        if ($request->filled('competition_id')) {
            $selectedCompetition = Competition::find($request->competition_id);
            if ($selectedCompetition) {
                // 既に割り当て済みの選手を除外
                $assignedPlayerIds = CompetitionPlayer::where('competition_id', $selectedCompetition->id)
                    ->pluck('player_id');
                
                $availablePlayers = Player::whereNotIn('id', $assignedPlayerIds)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('admin.competition-players.create', compact(
            'competitions',
            'selectedCompetition',
            'availablePlayers'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'player_id' => 'required|exists:players,id',
            'player_number' => 'required|string|max:50',
        ]);

        // 重複チェック
        $existingAssignment = CompetitionPlayer::where([
            'competition_id' => $validated['competition_id'],
            'player_id' => $validated['player_id']
        ])->first();

        if ($existingAssignment) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'この選手は既にこの大会に割り当てられています。'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'この選手は既にこの大会に割り当てられています。')
                ->withInput();
        }

        // 選手番号の重複チェック
        $existingNumber = CompetitionPlayer::where([
            'competition_id' => $validated['competition_id'],
            'player_number' => $validated['player_number']
        ])->first();

        if ($existingNumber) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'この選手番号は既に使用されています。'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'この選手番号は既に使用されています。')
                ->withInput();
        }

        $competitionPlayer = CompetitionPlayer::create($validated);

        // JSON APIリクエストの場合
        if ($request->expectsJson()) {
            $competitionPlayer->load(['player', 'competition']);
            return response()->json([
                'message' => '選手を大会に割り当てました。',
                'competition_player' => [
                    'id' => $competitionPlayer->id,
                    'player_number' => $competitionPlayer->player_number,
                    'player' => [
                        'id' => $competitionPlayer->player->id,
                        'name' => $competitionPlayer->player->name,
                        'prefecture' => $competitionPlayer->player->prefecture,
                        'affiliation' => $competitionPlayer->player->affiliation,
                        'gender_label' => $competitionPlayer->player->gender_label,
                    ]
                ]
            ]);
        }

        return redirect()->route('admin.competition-players.index', ['competition_id' => $validated['competition_id']])
            ->with('success', '選手を大会に割り当てました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionPlayer $competitionPlayer): View
    {
        $competitionPlayer->load(['competition', 'player']);
        
        return view('admin.competition-players.show', compact('competitionPlayer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionPlayer $competitionPlayer): View
    {
        $competitionPlayer->load(['competition', 'player']);
        
        return view('admin.competition-players.edit', compact('competitionPlayer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetitionPlayer $competitionPlayer): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'player_number' => 'required|string|max:50',
        ]);

        // 選手番号の重複チェック（自分以外）
        $existingNumber = CompetitionPlayer::where([
            'competition_id' => $competitionPlayer->competition_id,
            'player_number' => $validated['player_number']
        ])->where('id', '!=', $competitionPlayer->id)->first();

        if ($existingNumber) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'この選手番号は既に使用されています。'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'この選手番号は既に使用されています。')
                ->withInput();
        }

        $competitionPlayer->update($validated);

        // JSON APIリクエストの場合
        if ($request->expectsJson()) {
            return response()->json([
                'message' => '選手番号を更新しました。'
            ]);
        }

        return redirect()->route('admin.competition-players.index', ['competition_id' => $competitionPlayer->competition_id])
            ->with('success', '選手番号を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionPlayer $competitionPlayer): RedirectResponse|JsonResponse
    {
        $competitionId = $competitionPlayer->competition_id;
        $competitionPlayer->delete();

        // JSON APIリクエストの場合
        if (request()->expectsJson()) {
            return response()->json([
                'message' => '選手の割り当てを解除しました。'
            ]);
        }

        return redirect()->route('admin.competition-players.index', ['competition_id' => $competitionId])
            ->with('success', '選手の割り当てを解除しました。');
    }

    /**
     * 大会選手割り当てをCSVエクスポート
     */
    public function export(Request $request): Response
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id'
        ]);

        $competition = Competition::find($request->competition_id);
        $query = CompetitionPlayer::with(['player'])
            ->byCompetition($request->competition_id);

        // 検索フィルタ適用
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $competitionPlayers = $query->orderByPlayerNumber()->get();

        $csvData = [];
        
        // ヘッダー行を追加
        $csvData[] = [
            '選手番号',
            '選手名',
            '都道府県',
            '所属',
            '性別'
        ];

        // データ行を追加
        foreach ($competitionPlayers as $cp) {
            $csvData[] = [
                $cp->player_number,
                $cp->player->name,
                $cp->player->prefecture,
                $cp->player->affiliation ?? '',
                $cp->player->gender_label
            ];
        }

        // CSV文字列を生成
        $csv = '';
        foreach ($csvData as $row) {
            $csv .= implode(',', array_map(function($field) {
                if (strpos($field, ',') !== false || strpos($field, "\n") !== false || strpos($field, '"') !== false) {
                    $field = '"' . str_replace('"', '""', $field) . '"';
                }
                return $field;
            }, $row)) . "\n";
        }

        // BOMを追加（Excel での文字化け防止）
        $csv = "\xEF\xBB\xBF" . $csv;

        // 安全なファイル名を生成
        $competitionName = $competition->name;
        $invalidChars = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];
        $safeCompetitionName = str_replace($invalidChars, '_', $competitionName);
        
        $filename = sprintf('%s_参加選手一覧_%s.csv', $safeCompetitionName, date('Y-m-d'));
        $encodedFilename = rawurlencode($filename);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
    }

    /**
     * CSVインポート
     */
    public function import(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'import_mode' => 'required|in:replace,append'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getPathname()));
            
            // BOMを除去
            if (!empty($csvData[0]) && !empty($csvData[0][0])) {
                $csvData[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $csvData[0][0]);
            }

            // ヘッダー行をスキップ
            array_shift($csvData);

            $errors = [];
            $successCount = 0;
            $competitionId = $request->competition_id;

            // 置換モードの場合、既存の割り当てを削除
            if ($request->import_mode === 'replace') {
                CompetitionPlayer::where('competition_id', $competitionId)->delete();
            }

            DB::beginTransaction();

            foreach ($csvData as $index => $row) {
                // 空行をスキップ
                if (empty(array_filter($row))) {
                    continue;
                }

                $rowNumber = $index + 2;

                // 選手を名前で検索
                $playerName = trim($row[1] ?? '');
                $player = Player::where('name', $playerName)->first();

                if (!$player) {
                    $errors[] = "行 {$rowNumber}: 選手「{$playerName}」が見つかりません";
                    continue;
                }

                // データの検証
                $validator = Validator::make([
                    'player_number' => $row[0] ?? '',
                    'competition_id' => $competitionId,
                    'player_id' => $player->id,
                ], [
                    'player_number' => 'required|string|max:50',
                    'competition_id' => 'required|exists:competitions,id',
                    'player_id' => 'required|exists:players,id',
                ]);

                if ($validator->fails()) {
                    $errors[] = "行 {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // 重複チェック
                $existing = CompetitionPlayer::where([
                    'competition_id' => $competitionId,
                    'player_id' => $player->id
                ])->first();

                if ($existing) {
                    $errors[] = "行 {$rowNumber}: 選手「{$playerName}」は既に割り当てられています";
                    continue;
                }

                // 選手番号の重複チェック
                $existingNumber = CompetitionPlayer::where([
                    'competition_id' => $competitionId,
                    'player_number' => $row[0]
                ])->first();

                if ($existingNumber) {
                    $errors[] = "行 {$rowNumber}: 選手番号「{$row[0]}」は既に使用されています";
                    continue;
                }

                // 選手割り当てを作成
                CompetitionPlayer::create([
                    'competition_id' => $competitionId,
                    'player_id' => $player->id,
                    'player_number' => $row[0]
                ]);

                $successCount++;
            }

            DB::commit();

            $message = "{$successCount}件の選手割り当てをインポートしました。";
            if (!empty($errors)) {
                $message .= " エラー: " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " など";
                }
            }

            // JSON APIリクエストの場合
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'imported_count' => $successCount,
                    'errors' => $errors
                ]);
            }

            return redirect()->route('admin.competition-players.index', ['competition_id' => $competitionId])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            $errorMessage = 'CSVファイルの読み込みに失敗しました: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * 選手番号の一括生成
     */
    public function generatePlayerNumbers(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'start_number' => 'required|integer|min:1',
            'format' => 'required|string|in:number,prefixed'
        ]);

        $competitionId = $request->competition_id;
        $startNumber = $request->start_number;
        $format = $request->format;

        try {
            DB::beginTransaction();

            $competitionPlayers = CompetitionPlayer::byCompetition($competitionId)
                ->orderByPlayerName()
                ->get();

            $currentNumber = $startNumber;
            foreach ($competitionPlayers as $cp) {
                $playerNumber = $format === 'prefixed' ? 
                    sprintf('No.%03d', $currentNumber) : 
                    (string)$currentNumber;
                
                $cp->update(['player_number' => $playerNumber]);
                $currentNumber++;
            }

            DB::commit();

            $message = count($competitionPlayers) . '件の選手番号を生成しました。';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('admin.competition-players.index', ['competition_id' => $competitionId])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            $errorMessage = '選手番号の生成に失敗しました: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return redirect()->back()->with('error', $errorMessage);
        }
    }
}
