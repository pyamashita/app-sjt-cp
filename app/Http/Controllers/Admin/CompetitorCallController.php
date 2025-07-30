<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitorCall;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class CompetitorCallController extends Controller
{
    /**
     * 選手呼び出し一覧画面
     */
    public function index(Request $request): View
    {
        $query = CompetitorCall::query()
            ->orderBy('called_at', 'desc');

        // フィルタリング
        if ($request->filled('device_id')) {
            $query->byDevice($request->device_id);
        }

        if ($request->filled('call_type')) {
            $query->byCallType($request->call_type);
        }

        if ($request->filled('date_from')) {
            $query->dateRange($request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->dateRange(null, $request->date_to . ' 23:59:59');
        }

        // ページネーション
        $competitorCalls = $query->paginate(50);

        // フィルタ用データ
        $filterData = [
            'call_types' => CompetitorCall::getCallTypeNames(),
            'device_ids' => CompetitorCall::select('device_id')
                ->distinct()
                ->orderBy('device_id')
                ->pluck('device_id')
                ->toArray(),
        ];

        return view('admin.competitor-calls.index', compact('competitorCalls', 'filterData'));
    }

    /**
     * 選手呼び出し詳細画面
     */
    public function show(CompetitorCall $competitorCall): View
    {
        return view('admin.competitor-calls.show', compact('competitorCall'));
    }

    /**
     * 選手呼び出し削除
     */
    public function destroy(CompetitorCall $competitorCall)
    {
        $competitorCall->delete();

        return redirect()
            ->route('admin.competitor-calls.index')
            ->with('success', '選手呼び出し記録を削除しました');
    }

    /**
     * 選手呼び出し一括削除
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:competitor_calls,id',
        ]);

        $deletedCount = CompetitorCall::whereIn('id', $request->ids)->delete();

        return redirect()
            ->route('admin.competitor-calls.index')
            ->with('success', "{$deletedCount}件の選手呼び出し記録を削除しました");
    }

    /**
     * 統計情報取得（API）
     */
    public function statistics(Request $request)
    {
        $query = CompetitorCall::query();

        // 日付範囲フィルタ
        if ($request->filled('date_from')) {
            $query->dateRange($request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->dateRange(null, $request->date_to . ' 23:59:59');
        }

        // 統計データ作成
        $statistics = [
            'total_calls' => $query->count(),
            'calls_by_type' => $query->selectRaw('call_type, COUNT(*) as count')
                ->groupBy('call_type')
                ->pluck('count', 'call_type')
                ->toArray(),
            'calls_by_device' => $query->selectRaw('device_id, COUNT(*) as count')
                ->groupBy('device_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'device_id')
                ->toArray(),
            'calls_by_hour' => $query->selectRaw('HOUR(called_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->pluck('count', 'hour')
                ->toArray(),
        ];

        return response()->json($statistics);
    }
}
