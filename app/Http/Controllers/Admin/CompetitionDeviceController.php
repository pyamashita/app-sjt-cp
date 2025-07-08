<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionDevice;
use App\Models\Device;
use App\Models\CompetitionPlayer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CompetitionDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CompetitionDevice::with(['competition', 'device']);

        // 大会でフィルタ
        if ($request->filled('competition_id')) {
            $query->forCompetition($request->competition_id);
        }

        // 検索
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $competitionDevices = $query->orderBy('player_number')->paginate(20)->withQueryString();
        $competitions = Competition::orderBy('start_date', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json($competitionDevices);
        }

        return view('admin.competition-devices.index', compact('competitionDevices', 'competitions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $competitions = Competition::orderBy('start_date', 'desc')->get();
        $devices = Device::forPlayers()->orderBy('name')->get();
        
        // 選択された大会がある場合
        $selectedCompetition = null;
        $assignedDevices = collect();
        $availablePlayers = collect();
        
        if ($request->filled('competition_id')) {
            $selectedCompetition = Competition::find($request->competition_id);
            if ($selectedCompetition) {
                // すでに割り当てられている端末を取得
                $assignedDevices = CompetitionDevice::where('competition_id', $selectedCompetition->id)
                    ->pluck('device_id');
                
                // この大会に参加している選手を取得
                $availablePlayers = CompetitionPlayer::where('competition_id', $selectedCompetition->id)
                    ->with('player')
                    ->orderBy('player_number')
                    ->get();
            }
        }
        
        return view('admin.competition-devices.create', compact(
            'competitions', 
            'devices', 
            'selectedCompetition', 
            'assignedDevices',
            'availablePlayers'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'device_id' => 'required|exists:devices,id',
            'player_number' => 'required|string|max:255',
        ]);

        // 重複チェック
        $exists = CompetitionDevice::where('competition_id', $validated['competition_id'])
            ->where(function ($query) use ($validated) {
                $query->where('device_id', $validated['device_id'])
                      ->orWhere('player_number', $validated['player_number']);
            })
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'この端末または選手番号はすでに登録されています。');
        }

        CompetitionDevice::create($validated);

        return redirect()->route('admin.competition-devices.index')
            ->with('success', '端末を割り当てました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionDevice $competitionDevice): View
    {
        $competitionDevice->load(['competition', 'device']);
        
        return view('admin.competition-devices.show', compact('competitionDevice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionDevice $competitionDevice): View
    {
        $competitions = Competition::orderBy('start_date', 'desc')->get();
        $devices = Device::forPlayers()->orderBy('name')->get();
        
        // この大会に参加している選手を取得
        $availablePlayers = CompetitionPlayer::where('competition_id', $competitionDevice->competition_id)
            ->with('player')
            ->orderBy('player_number')
            ->get();
        
        return view('admin.competition-devices.edit', compact(
            'competitionDevice', 
            'competitions', 
            'devices',
            'availablePlayers'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetitionDevice $competitionDevice): RedirectResponse
    {
        $validated = $request->validate([
            'player_number' => 'required|string|max:255',
        ]);

        // 重複チェック（自分自身を除く）
        $exists = CompetitionDevice::where('competition_id', $competitionDevice->competition_id)
            ->where('player_number', $validated['player_number'])
            ->where('id', '!=', $competitionDevice->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'この選手番号はすでに使用されています。');
        }

        $competitionDevice->update($validated);

        return redirect()->route('admin.competition-devices.index')
            ->with('success', '割り当てを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionDevice $competitionDevice): RedirectResponse
    {
        $competitionDevice->delete();

        return redirect()->route('admin.competition-devices.index')
            ->with('success', '割り当てを解除しました。');
    }

    /**
     * CSVエクスポート
     */
    public function export(Request $request): BinaryFileResponse
    {
        $query = CompetitionDevice::with(['competition', 'device']);

        // 大会でフィルタ
        if ($request->filled('competition_id')) {
            $query->forCompetition($request->competition_id);
            $competition = Competition::find($request->competition_id);
            $filename = $competition->name . '_端末割当_' . now()->format('Ymd_His') . '.csv';
        } else {
            $filename = '端末割当一覧_' . now()->format('Ymd_His') . '.csv';
        }

        $competitionDevices = $query->orderBy('player_number')->get();
        
        $path = 'exports/' . $filename;
        
        // UTF-8 BOM付きでCSVを作成
        $csv = chr(0xEF) . chr(0xBB) . chr(0xBF);
        $csv .= implode(',', CompetitionDevice::getCsvHeaders()) . "\n";
        
        foreach ($competitionDevices as $competitionDevice) {
            $data = $competitionDevice->toCsvArray();
            $csv .= implode(',', array_map(function ($item) {
                return '"' . str_replace('"', '""', $item) . '"';
            }, $data)) . "\n";
        }
        
        Storage::put($path, $csv);
        
        return response()->download(storage_path('app/' . $path), $filename)
            ->deleteFileAfterSend(true);
    }

    /**
     * CSVインポート
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $competitionId = $request->competition_id;
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
                
                if (count($data) < 2) {
                    $errors[] = '行 ' . ($index + 2) . ': データが不足しています';
                    continue;
                }

                $playerNumber = trim($data[0] ?? '');
                $deviceName = trim($data[1] ?? '');

                // 必須項目のチェック
                if (empty($playerNumber) || empty($deviceName)) {
                    $errors[] = '行 ' . ($index + 2) . ': 必須項目が不足しています';
                    continue;
                }

                // 端末の検索
                $device = Device::where('name', $deviceName)
                    ->where('user_type', '選手')
                    ->first();

                if (!$device) {
                    $errors[] = '行 ' . ($index + 2) . ': 端末「' . $deviceName . '」が見つかりません';
                    continue;
                }

                // 重複チェック
                $exists = CompetitionDevice::where('competition_id', $competitionId)
                    ->where(function ($query) use ($device, $playerNumber) {
                        $query->where('device_id', $device->id)
                              ->orWhere('player_number', $playerNumber);
                    })
                    ->exists();

                if ($exists) {
                    $errors[] = '行 ' . ($index + 2) . ': この端末または選手番号はすでに登録されています';
                    continue;
                }

                CompetitionDevice::create([
                    'competition_id' => $competitionId,
                    'device_id' => $device->id,
                    'player_number' => $playerNumber,
                ]);

                $imported++;
            }

            DB::commit();

            $message = $imported . '件の割り当てをインポートしました。';
            if (!empty($errors)) {
                $message .= ' (' . count($errors) . '件のエラーがありました)';
            }

            return redirect()->route('admin.competition-devices.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.competition-devices.index')
                ->with('error', 'インポート中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * 選手番号の候補を取得（AJAX用）
     */
    public function getAvailablePlayerNumbers(Request $request): JsonResponse
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id',
        ]);

        $playerNumbers = CompetitionPlayer::where('competition_id', $request->competition_id)
            ->with('player')
            ->orderBy('player_number')
            ->get()
            ->map(function ($cp) {
                return [
                    'player_number' => $cp->player_number,
                    'player_name' => $cp->player->name,
                ];
            });

        return response()->json($playerNumbers);
    }
}