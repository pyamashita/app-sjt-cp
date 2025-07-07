<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionDay;
use App\Models\CompetitionSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

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
}
