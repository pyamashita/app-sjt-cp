<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Player::query();

        // 検索処理
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // 都道府県フィルタ
        if ($request->filled('prefecture')) {
            $query->byPrefecture($request->prefecture);
        }

        // 性別フィルタ
        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        $players = $query->orderBy('name')->paginate(15);
        $prefectures = Player::getPrefectures();
        $genders = Player::getGenders();

        return view('admin.players.index', compact('players', 'prefectures', 'genders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $prefectures = Player::getPrefectures();
        $genders = Player::getGenders();
        
        return view('admin.players.create', compact('prefectures', 'genders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prefecture' => 'required|string|in:' . implode(',', Player::getPrefectures()),
            'affiliation' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
        ]);

        $player = Player::create($validated);

        // JSON APIリクエストの場合
        if ($request->expectsJson()) {
            return response()->json([
                'message' => '選手を追加しました。',
                'player' => [
                    'id' => $player->id,
                    'name' => $player->name,
                    'prefecture' => $player->prefecture,
                    'affiliation' => $player->affiliation,
                    'gender' => $player->gender,
                    'gender_label' => $player->gender_label,
                    'full_name' => $player->full_name
                ]
            ]);
        }

        return redirect()->route('admin.players.index')
            ->with('success', '選手を追加しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Player $player): View
    {
        $player->load(['competitionPlayers.competition']);
        
        return view('admin.players.show', compact('player'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Player $player): View
    {
        $prefectures = Player::getPrefectures();
        $genders = Player::getGenders();
        
        return view('admin.players.edit', compact('player', 'prefectures', 'genders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Player $player): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prefecture' => 'required|string|in:' . implode(',', Player::getPrefectures()),
            'affiliation' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
        ]);

        $player->update($validated);

        // JSON APIリクエストの場合
        if ($request->expectsJson()) {
            return response()->json([
                'message' => '選手情報を更新しました。'
            ]);
        }

        return redirect()->route('admin.players.index')
            ->with('success', '選手情報を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Player $player): RedirectResponse|JsonResponse
    {
        // 大会に参加している場合は削除不可
        if ($player->competitionPlayers()->exists()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'この選手は大会に参加しているため削除できません。'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'この選手は大会に参加しているため削除できません。');
        }

        $player->delete();

        // JSON APIリクエストの場合
        if (request()->expectsJson()) {
            return response()->json([
                'message' => '選手を削除しました。'
            ]);
        }

        return redirect()->route('admin.players.index')
            ->with('success', '選手を削除しました。');
    }

    /**
     * 選手をCSVエクスポート
     */
    public function export(Request $request): Response
    {
        $query = Player::query();

        // フィルタ適用
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('prefecture')) {
            $query->byPrefecture($request->prefecture);
        }
        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        $players = $query->orderBy('name')->get();

        $csvData = [];
        
        // ヘッダー行を追加
        $csvData[] = [
            '選手名',
            '都道府県',
            '所属',
            '性別'
        ];

        // データ行を追加
        foreach ($players as $player) {
            $csvData[] = [
                $player->name,
                $player->prefecture,
                $player->affiliation ?? '',
                $player->gender_label
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

        $filename = '選手情報_' . date('Y-m-d') . '.csv';
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
            $genderMap = array_flip(Player::getGenders());
            $prefectures = Player::getPrefectures();

            // 置換モードの場合、既存の選手を削除
            if ($request->import_mode === 'replace') {
                Player::truncate();
            }

            foreach ($csvData as $index => $row) {
                // 空行をスキップ
                if (empty(array_filter($row))) {
                    continue;
                }

                $rowNumber = $index + 2;

                // 性別の変換
                $gender = $row[3] ?? '';
                if (isset($genderMap[$gender])) {
                    $gender = $genderMap[$gender];
                } elseif (!in_array($gender, ['male', 'female', 'other'])) {
                    $gender = 'other'; // デフォルト
                }

                // データの検証
                $validator = Validator::make([
                    'name' => $row[0] ?? '',
                    'prefecture' => $row[1] ?? '',
                    'affiliation' => $row[2] ?? '',
                    'gender' => $gender
                ], [
                    'name' => 'required|string|max:255',
                    'prefecture' => 'required|string|in:' . implode(',', $prefectures),
                    'affiliation' => 'nullable|string|max:255',
                    'gender' => 'required|in:male,female,other'
                ]);

                if ($validator->fails()) {
                    $errors[] = "行 {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // 選手を作成
                Player::create([
                    'name' => $row[0],
                    'prefecture' => $row[1],
                    'affiliation' => $row[2] ?: null,
                    'gender' => $gender
                ]);

                $successCount++;
            }

            $message = "{$successCount}件の選手をインポートしました。";
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

            return redirect()->route('admin.players.index')->with('success', $message);

        } catch (\Exception $e) {
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
}