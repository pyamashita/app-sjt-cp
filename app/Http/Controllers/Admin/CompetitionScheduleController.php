<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionDay;
use App\Models\CompetitionSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class CompetitionScheduleController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(CompetitionDay $competitionDay): View
    {
        return view('admin.competition-schedules.create', compact('competitionDay'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CompetitionDay $competitionDay): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'content' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'count_up' => 'boolean',
            'auto_advance' => 'boolean',
        ]);

        // 表示順序を自動設定
        $maxSortOrder = $competitionDay->competitionSchedules()->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSortOrder + 1;

        $schedule = $competitionDay->competitionSchedules()->create($validated);

        // JSON APIリクエストの場合
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'スケジュールを作成しました。',
                'schedule' => [
                    'id' => $schedule->id,
                    'start_time' => $schedule->start_time->format('H:i'),
                    'content' => $schedule->content,
                    'notes' => $schedule->notes,
                    'count_up' => $schedule->count_up,
                    'auto_advance' => $schedule->auto_advance,
                    'order' => $schedule->sort_order
                ]
            ]);
        }

        return redirect()->route('admin.competitions.competition-days.show', [
            'competition' => $competitionDay->competition_id,
            'competition_day' => $competitionDay->id
        ])->with('success', 'スケジュールを作成しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionDay $competitionDay, CompetitionSchedule $competitionSchedule): View
    {
        return view('admin.competition-schedules.edit', compact('competitionDay', 'competitionSchedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetitionDay $competitionDay, CompetitionSchedule $competitionSchedule): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'content' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'count_up' => 'boolean',
            'auto_advance' => 'boolean',
        ]);

        $competitionSchedule->update($validated);

        // JSON APIリクエストの場合
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'スケジュールを更新しました。'
            ]);
        }

        return redirect()->route('admin.competitions.competition-days.show', [
            'competition' => $competitionDay->competition_id,
            'competition_day' => $competitionDay->id
        ])->with('success', 'スケジュールを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionDay $competitionDay, CompetitionSchedule $competitionSchedule): RedirectResponse|JsonResponse
    {
        $competitionSchedule->delete();

        // JSON APIリクエストの場合
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'スケジュールを削除しました。'
            ]);
        }

        return redirect()->route('admin.competitions.competition-days.show', [
            'competition' => $competitionDay->competition_id,
            'competition_day' => $competitionDay->id
        ])->with('success', 'スケジュールを削除しました。');
    }

    /**
     * スケジュールの順序を更新
     */
    public function updateOrder(Request $request, CompetitionDay $competitionDay): RedirectResponse
    {
        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.id' => 'required|integer|exists:competition_schedules,id',
            'schedules.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['schedules'] as $scheduleData) {
            CompetitionSchedule::where('id', $scheduleData['id'])
                ->update(['sort_order' => $scheduleData['sort_order']]);
        }

        return redirect()->back()->with('success', 'スケジュールの順序を更新しました。');
    }

    /**
     * スケジュールをCSVエクスポート
     */
    public function export(CompetitionDay $competitionDay): Response
    {
        $schedules = $competitionDay->competitionSchedules()
            ->orderBy('sort_order')
            ->get();

        $csvData = [];
        
        // ヘッダー行を追加
        $csvData[] = [
            '開始時刻',
            '内容',
            '備考',
            'カウントアップ',
            '自動送り'
        ];

        // データ行を追加
        foreach ($schedules as $schedule) {
            $csvData[] = [
                $schedule->start_time->format('H:i'),
                $schedule->content,
                $schedule->notes ?? '',
                $schedule->count_up ? '1' : '0',
                $schedule->auto_advance ? '1' : '0'
            ];
        }

        // CSV文字列を生成
        $csv = '';
        foreach ($csvData as $row) {
            $csv .= implode(',', array_map(function($field) {
                // フィールドに改行やカンマが含まれる場合はダブルクォートで囲む
                if (strpos($field, ',') !== false || strpos($field, "\n") !== false || strpos($field, '"') !== false) {
                    $field = '"' . str_replace('"', '""', $field) . '"';
                }
                return $field;
            }, $row)) . "\n";
        }

        // BOMを追加（Excel での文字化け防止）
        $csv = "\xEF\xBB\xBF" . $csv;

        // 日本語対応のファイル名生成
        $competitionName = $competitionDay->competition->name;
        $dayName = $competitionDay->day_name;
        
        // 安全なファイル名を生成（日本語を適切に処理）
        $safeCompetitionName = mb_convert_encoding($competitionName, 'UTF-8', 'UTF-8');
        $safeDayName = mb_convert_encoding($dayName, 'UTF-8', 'UTF-8');
        
        // ファイル名に使用できない文字を置換（Windows/Mac/Linux対応）
        $invalidChars = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];
        $safeCompetitionName = str_replace($invalidChars, '_', $safeCompetitionName);
        $safeDayName = str_replace($invalidChars, '_', $safeDayName);
        
        $filename = sprintf(
            '%s_%s_スケジュール.csv',
            $safeCompetitionName,
            $safeDayName
        );

        // ブラウザ互換性のためのファイル名エンコーディング
        $encodedFilename = rawurlencode($filename);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
    }

    /**
     * CSVインポート
     */
    public function import(Request $request, CompetitionDay $competitionDay): RedirectResponse|JsonResponse
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

            // 置換モードの場合、既存のスケジュールを削除
            if ($request->import_mode === 'replace') {
                $competitionDay->competitionSchedules()->delete();
            }

            $sortOrder = $request->import_mode === 'replace' ? 1 : 
                ($competitionDay->competitionSchedules()->max('sort_order') ?? 0) + 1;

            foreach ($csvData as $index => $row) {
                // 空行をスキップ
                if (empty(array_filter($row))) {
                    continue;
                }

                $rowNumber = $index + 2; // ヘッダー行を考慮した行番号

                // データの検証
                $validator = Validator::make([
                    'start_time' => $row[0] ?? '',
                    'content' => $row[1] ?? '',
                    'notes' => $row[2] ?? '',
                    'count_up' => $row[3] ?? '0',
                    'auto_advance' => $row[4] ?? '0'
                ], [
                    'start_time' => 'required|date_format:H:i',
                    'content' => 'required|string|max:255',
                    'notes' => 'nullable|string',
                    'count_up' => 'in:0,1',
                    'auto_advance' => 'in:0,1'
                ]);

                if ($validator->fails()) {
                    $errors[] = "行 {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // スケジュールを作成
                $competitionDay->competitionSchedules()->create([
                    'start_time' => $row[0],
                    'content' => $row[1],
                    'notes' => $row[2] ?: null,
                    'count_up' => (bool)($row[3] ?? false),
                    'auto_advance' => (bool)($row[4] ?? false),
                    'sort_order' => $sortOrder++
                ]);

                $successCount++;
            }

            $message = "{$successCount}件のスケジュールをインポートしました。";
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

            return redirect()->route('admin.competitions.competition-days.show', [
                'competition' => $competitionDay->competition_id,
                'competition_day' => $competitionDay->id
            ])->with('success', $message);

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
