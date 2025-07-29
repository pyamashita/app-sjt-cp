<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Device::query();

        // 検索
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // 端末種別でフィルタ
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // 利用者種別でフィルタ
        if ($request->filled('user_type')) {
            $query->ofUserType($request->user_type);
        }

        $devices = $query->latest()->paginate(20)->withQueryString();

        return view('admin.devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.devices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:PC,スマートフォン,その他',
            'user_type' => 'required|in:選手,競技関係者,ネットワーク',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/|max:17',
        ]);

        Device::create($validated);

        return redirect()->route('admin.devices.index')
            ->with('success', '端末を登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device): View
    {
        $device->load(['competitions' => function ($query) {
            $query->orderBy('start_date', 'desc');
        }]);
        
        return view('admin.devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device): View
    {
        return view('admin.devices.edit', compact('device'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:PC,スマートフォン,その他',
            'user_type' => 'required|in:選手,競技関係者,ネットワーク',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/|max:17',
        ]);

        $device->update($validated);

        return redirect()->route('admin.devices.index')
            ->with('success', '端末情報を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device): RedirectResponse
    {
        // 大会に割り当てられている場合は削除不可
        if ($device->competitions()->exists()) {
            return redirect()->route('admin.devices.index')
                ->with('error', 'この端末は大会に割り当てられているため削除できません。');
        }

        $device->delete();

        return redirect()->route('admin.devices.index')
            ->with('success', '端末を削除しました。');
    }

    /**
     * CSVエクスポート
     */
    public function export(Request $request): Response
    {
        $query = Device::query();

        // 検索フィルタ適用
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }
        if ($request->filled('user_type')) {
            $query->ofUserType($request->user_type);
        }

        $devices = $query->orderBy('name')->get();

        $csvData = [];
        
        // ヘッダー行を追加
        $csvData[] = Device::getCsvHeaders();

        // データ行を追加
        foreach ($devices as $device) {
            $csvData[] = $device->toCsvArray();
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

        $filename = '端末一覧_' . date('Y-m-d') . '.csv';
        $encodedFilename = rawurlencode($filename);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
    }

    /**
     * CSVインポート
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        
        // BOMを除去
        $content = str_replace("\xEF\xBB\xBF", '', $content);
        
        // 改行で分割
        $lines = array_filter(explode("\n", $content));
        
        // ヘッダー行をスキップ
        array_shift($lines);
        
        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($lines as $index => $line) {
                $data = str_getcsv($line);
                
                if (count($data) < 4) {
                    $errors[] = '行 ' . ($index + 2) . ': データが不足しています';
                    continue;
                }

                $deviceId = trim($data[0] ?? '');
                $name = trim($data[1] ?? '');
                $type = trim($data[2] ?? '');
                $userType = trim($data[3] ?? '');
                $ipAddress = trim($data[4] ?? '');
                $macAddress = trim($data[5] ?? '');

                // 必須項目のチェック
                if (empty($name) || empty($type) || empty($userType)) {
                    $errors[] = '行 ' . ($index + 2) . ': 必須項目が不足しています';
                    continue;
                }

                // 端末IDの重複チェック（指定されている場合）
                if (!empty($deviceId)) {
                    if (Device::where('device_id', $deviceId)->exists()) {
                        $errors[] = '行 ' . ($index + 2) . ': 端末ID "' . $deviceId . '" は既に存在します';
                        continue;
                    }
                    
                    // 端末IDのフォーマットチェック
                    if (!preg_match('/^TML-[a-z0-9]{4}$/', $deviceId)) {
                        $errors[] = '行 ' . ($index + 2) . ': 端末IDの形式が正しくありません（TML-xxxx形式）';
                        continue;
                    }
                }

                // 端末種別の検証
                if (!in_array($type, array_keys(Device::getTypes()))) {
                    $errors[] = '行 ' . ($index + 2) . ': 無効な端末種別です';
                    continue;
                }

                // 利用者種別の検証
                if (!in_array($userType, array_keys(Device::getUserTypes()))) {
                    $errors[] = '行 ' . ($index + 2) . ': 無効な利用者種別です';
                    continue;
                }

                // IPアドレスの検証（入力されている場合）
                if (!empty($ipAddress) && !filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $errors[] = '行 ' . ($index + 2) . ': 無効なIPアドレスです';
                    continue;
                }

                // MACアドレスの検証（入力されている場合）
                if (!empty($macAddress) && !preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macAddress)) {
                    $errors[] = '行 ' . ($index + 2) . ': 無効なMACアドレスです';
                    continue;
                }

                $createData = [
                    'name' => $name,
                    'type' => $type,
                    'user_type' => $userType,
                    'ip_address' => $ipAddress ?: null,
                    'mac_address' => $macAddress ?: null,
                ];
                
                // 端末IDが指定されている場合は使用、そうでなければ自動生成
                if (!empty($deviceId)) {
                    $createData['device_id'] = $deviceId;
                }
                
                Device::create($createData);

                $imported++;
            }

            DB::commit();

            $message = $imported . '件の端末をインポートしました。';
            if (!empty($errors)) {
                $message .= ' (' . count($errors) . '件のエラーがありました)';
            }

            return redirect()->route('admin.devices.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.devices.index')
                ->with('error', 'インポート中にエラーが発生しました: ' . $e->getMessage());
        }
    }
}