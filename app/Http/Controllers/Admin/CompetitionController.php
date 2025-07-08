<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $competitions = Competition::with('competitionDays')
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return view('admin.competitions.index', compact('competitions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.competitions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // デバッグ用：リクエストデータの確認
        \Log::info('Competition creation request:', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'venue' => 'required|string|max:255',
            'chief_judge' => 'nullable|string|max:255',
            'committee_members' => 'nullable|array',
            'committee_members.*' => 'string|max:255',
        ]);

        // 競技委員の配列から空の値を除去
        if (isset($validated['committee_members'])) {
            $validated['committee_members'] = array_values(array_filter($validated['committee_members'], function($member) {
                return !empty(trim($member));
            }));
        }

        \Log::info('Validated data:', $validated);

        Competition::create($validated);

        return redirect()->route('admin.competitions.index')
            ->with('success', '大会を作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Competition $competition): View
    {
        $competition->load(['competitionDays.competitionSchedules']);
        
        return view('admin.competitions.show', compact('competition'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Competition $competition): View
    {
        return view('admin.competitions.edit', compact('competition'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Competition $competition): RedirectResponse
    {
        // デバッグ用：リクエストデータの確認
        \Log::info('Competition update request:', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'venue' => 'required|string|max:255',
            'chief_judge' => 'nullable|string|max:255',
            'committee_members' => 'nullable|array',
            'committee_members.*' => 'string|max:255',
        ]);

        // 競技委員の配列から空の値を除去
        if (isset($validated['committee_members'])) {
            $validated['committee_members'] = array_values(array_filter($validated['committee_members'], function($member) {
                return !empty(trim($member));
            }));
        }

        \Log::info('Validated data:', $validated);

        $competition->update($validated);

        return redirect()->route('admin.competitions.index')
            ->with('success', '大会を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competition $competition): RedirectResponse
    {
        $competition->delete();

        return redirect()->route('admin.competitions.index')
            ->with('success', '大会を削除しました。');
    }
}
