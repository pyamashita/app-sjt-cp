<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CommitteeMember;
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
        try {
            $committeeMembers = CommitteeMember::active()->orderByNameKana()->get();
        } catch (\Exception $e) {
            // テーブルが存在しない場合は空のコレクション
            $committeeMembers = collect();
        }
        
        return view('admin.competitions.create', compact('committeeMembers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'venue' => 'required|string|max:255',
            'chief_judge_id' => 'nullable|exists:committee_members,id',
            'committee_member_ids' => 'nullable|array',
            'committee_member_ids.*' => 'exists:committee_members,id',
        ], [
            'name.required' => '大会名を入力してください。',
            'start_date.required' => '開始日を入力してください。',
            'end_date.required' => '終了日を入力してください。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付を選択してください。',
            'venue.required' => '開催場所を入力してください。',
            'chief_judge_id.exists' => '選択された競技主査が存在しません。',
            'committee_member_ids.*.exists' => '選択された競技委員が存在しません。',
        ]);

        // 大会を作成（競技委員情報は除く）
        $competitionData = array_intersect_key($validated, array_flip(['name', 'start_date', 'end_date', 'venue']));
        $competition = Competition::create($competitionData);

        // 競技主査を関連付け（テーブルが存在する場合のみ）
        try {
            if (isset($validated['chief_judge_id']) && $validated['chief_judge_id']) {
                $competition->committeeMembers()->attach($validated['chief_judge_id'], ['role' => '競技主査']);
            }

            // 競技委員を関連付け
            if (isset($validated['committee_member_ids']) && !empty($validated['committee_member_ids'])) {
                $committeeData = [];
                foreach ($validated['committee_member_ids'] as $memberId) {
                    // 競技主査と重複しないようにチェック
                    if ($memberId != ($validated['chief_judge_id'] ?? null)) {
                        $committeeData[$memberId] = ['role' => '競技委員'];
                    }
                }
                if (!empty($committeeData)) {
                    $competition->committeeMembers()->attach($committeeData);
                }
            }
        } catch (\Exception $e) {
            // テーブルが存在しない場合はスキップ
            \Log::info('Committee member table not found, skipping committee member assignment');
        }

        return redirect()->route('admin.competitions.index')
            ->with('success', '大会を作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Competition $competition): View
    {
        $competition->load(['competitionDays.competitionSchedules', 'committeeMembers']);
        
        return view('admin.competitions.show', compact('competition'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Competition $competition): View
    {
        try {
            $committeeMembers = CommitteeMember::active()->orderByNameKana()->get();
        } catch (\Exception $e) {
            // テーブルが存在しない場合は空のコレクション
            $committeeMembers = collect();
        }
        
        // テーブルが存在する場合のみリレーションをロード
        try {
            $competition->load('committeeMembers');
        } catch (\Exception $e) {
            // テーブルが存在しない場合は空のコレクションを設定
            $competition->setRelation('committeeMembers', collect());
        }
        
        return view('admin.competitions.edit', compact('competition', 'committeeMembers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'venue' => 'required|string|max:255',
            'chief_judge_id' => 'nullable|exists:committee_members,id',
            'committee_member_ids' => 'nullable|array',
            'committee_member_ids.*' => 'exists:committee_members,id',
        ], [
            'name.required' => '大会名を入力してください。',
            'start_date.required' => '開始日を入力してください。',
            'end_date.required' => '終了日を入力してください。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付を選択してください。',
            'venue.required' => '開催場所を入力してください。',
            'chief_judge_id.exists' => '選択された競技主査が存在しません。',
            'committee_member_ids.*.exists' => '選択された競技委員が存在しません。',
        ]);

        // 大会基本情報を更新
        $competitionData = array_intersect_key($validated, array_flip(['name', 'start_date', 'end_date', 'venue']));
        $competition->update($competitionData);

        // 既存の競技委員関連付けを削除（テーブルが存在する場合のみ）
        try {
            $competition->committeeMembers()->detach();

            // 競技主査を関連付け
            if (isset($validated['chief_judge_id']) && $validated['chief_judge_id']) {
                $competition->committeeMembers()->attach($validated['chief_judge_id'], ['role' => '競技主査']);
            }

            // 競技委員を関連付け
            if (isset($validated['committee_member_ids']) && !empty($validated['committee_member_ids'])) {
                $committeeData = [];
                foreach ($validated['committee_member_ids'] as $memberId) {
                    // 競技主査と重複しないようにチェック
                    if ($memberId != ($validated['chief_judge_id'] ?? null)) {
                        $committeeData[$memberId] = ['role' => '競技委員'];
                    }
                }
                if (!empty($committeeData)) {
                    $competition->committeeMembers()->attach($committeeData);
                }
            }
        } catch (\Exception $e) {
            // テーブルが存在しない刴合はスキップ
            \Log::info('Committee member table not found, skipping committee member assignment');
        }

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
