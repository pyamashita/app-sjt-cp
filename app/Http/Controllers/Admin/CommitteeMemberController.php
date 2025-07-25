<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class CommitteeMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = CommitteeMember::query();
        
        // 検索機能
        if ($search = $request->get('search')) {
            $query->searchByName($search);
        }
        
        // 並び替え
        $query->orderByNameKana();
        
        $committeeMembers = $query->paginate(20)->withQueryString();
        
        return view('admin.committee-members.index', compact('committeeMembers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.committee-members.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_kana' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'name.required' => '名前を入力してください。',
            'name_kana.required' => '名前ふりがなを入力してください。',
        ]);

        $validated['is_active'] = $request->has('is_active');

        CommitteeMember::create($validated);

        return redirect()
            ->route('admin.committee-members.index')
            ->with('success', '競技委員が正常に追加されました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(CommitteeMember $committeeMember): View
    {
        return view('admin.committee-members.show', compact('committeeMember'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommitteeMember $committeeMember): View
    {
        return view('admin.committee-members.edit', compact('committeeMember'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommitteeMember $committeeMember): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_kana' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'name.required' => '名前を入力してください。',
            'name_kana.required' => '名前ふりがなを入力してください。',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $committeeMember->update($validated);

        return redirect()
            ->route('admin.committee-members.index')
            ->with('success', '競技委員情報が正常に更新されました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommitteeMember $committeeMember): RedirectResponse
    {
        $committeeMember->delete();

        return redirect()
            ->route('admin.committee-members.index')
            ->with('success', '競技委員が正常に削除されました。');
    }

    /**
     * CSVエクスポート
     */
    public function export(Request $request)
    {
        $query = CommitteeMember::query();
        
        if ($search = $request->get('search')) {
            $query->searchByName($search);
        }
        
        $committeeMembers = $query->orderByNameKana()->get();
        
        $filename = '競技委員一覧_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($committeeMembers) {
            $file = fopen('php://output', 'w');
            
            // BOM for Excel UTF-8 compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // ヘッダー行
            fputcsv($file, ['ID', '名前', '名前ふりがな', '所属', '備考', '状態', '作成日時', '更新日時']);
            
            // データ行
            foreach ($committeeMembers as $member) {
                fputcsv($file, [
                    $member->id,
                    $member->name,
                    $member->name_kana,
                    $member->organization,
                    $member->description,
                    $member->is_active ? 'アクティブ' : '非アクティブ',
                    $member->created_at->format('Y-m-d H:i:s'),
                    $member->updated_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * CSVインポート
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'import_mode' => 'required|in:add,replace'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = [];

            // CSVファイルを読み込み
            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                // BOMをスキップ
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // ヘッダー行をスキップ
                $header = fgetcsv($handle);

                while (($data = fgetcsv($handle)) !== false) {
                    $csvData[] = $data;
                }
                fclose($handle);
            }

            $errors = [];
            $successCount = 0;

            // 置換モードの場合、既存の競技委員を削除
            if ($request->import_mode === 'replace') {
                CommitteeMember::truncate();
            }

            foreach ($csvData as $index => $row) {
                // 空行をスキップ
                if (empty(array_filter($row))) {
                    continue;
                }

                $rowNumber = $index + 2;

                // データの検証
                $validator = Validator::make([
                    'name' => $row[1] ?? '',         // 名前
                    'name_kana' => $row[2] ?? '',    // 名前ふりがな
                    'organization' => $row[3] ?? '',  // 所属
                    'description' => $row[4] ?? '',   // 備考
                    'is_active' => $row[5] ?? 'アクティブ'  // 状態
                ], [
                    'name' => 'required|string|max:255',
                    'name_kana' => 'required|string|max:255',
                    'organization' => 'nullable|string|max:255',
                    'description' => 'nullable|string',
                    'is_active' => 'string'
                ]);

                if ($validator->fails()) {
                    $errors[] = "行 {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // 状態の変換
                $isActive = ($row[5] ?? 'アクティブ') === 'アクティブ';

                // 競技委員を作成
                CommitteeMember::create([
                    'name' => $row[1],
                    'name_kana' => $row[2],
                    'organization' => $row[3] ?: null,
                    'description' => $row[4] ?: null,
                    'is_active' => $isActive
                ]);

                $successCount++;
            }

            $message = "{$successCount}件の競技委員をインポートしました。";
            if (!empty($errors)) {
                $message .= " エラー: " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " など";
                }
            }

            return redirect()->route('admin.committee-members.index')->with('success', $message);

        } catch (\Exception $e) {
            $errorMessage = 'CSVファイルの読み込みに失敗しました: ' . $e->getMessage();
            return redirect()->route('admin.committee-members.index')->with('error', $errorMessage);
        }
    }
}
