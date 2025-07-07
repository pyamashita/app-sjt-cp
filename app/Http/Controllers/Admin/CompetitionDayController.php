<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionDay;
use App\Models\CompetitionSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompetitionDayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Competition $competition): View
    {
        $competitionDays = $competition->competitionDays()
            ->with('competitionSchedules')
            ->orderBy('date')
            ->get();

        return view('admin.competition-days.index', compact('competition', 'competitionDays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Competition $competition): View
    {
        return view('admin.competition-days.create', compact('competition'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'day_name' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:' . $competition->start_date . '|before_or_equal:' . $competition->end_date,
        ]);

        $competition->competitionDays()->create($validated);

        return redirect()->route('admin.competitions.competition-days.index', $competition)
            ->with('success', '競技日程を作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Competition $competition, CompetitionDay $competitionDay): View
    {
        $competitionDay->load(['competitionSchedules' => function ($query) {
            $query->orderBy('sort_order')->orderBy('start_time');
        }]);

        return view('admin.competition-days.show', compact('competition', 'competitionDay'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Competition $competition, CompetitionDay $competitionDay): View
    {
        return view('admin.competition-days.edit', compact('competition', 'competitionDay'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Competition $competition, CompetitionDay $competitionDay): RedirectResponse
    {
        $validated = $request->validate([
            'day_name' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:' . $competition->start_date . '|before_or_equal:' . $competition->end_date,
        ]);

        $competitionDay->update($validated);

        return redirect()->route('admin.competitions.competition-days.index', $competition)
            ->with('success', '競技日程を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competition $competition, CompetitionDay $competitionDay): RedirectResponse
    {
        $competitionDay->delete();

        return redirect()->route('admin.competitions.competition-days.index', $competition)
            ->with('success', '競技日程を削除しました。');
    }
}
