<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Competition;
use App\Models\GuidePage;
use App\Models\GuidePageSection;
use App\Models\GuidePageGroup;
use App\Models\GuidePageItem;
use App\Models\Player;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuidePageController extends Controller
{
    public function index()
    {
        $guidePages = GuidePage::with(['competition', 'sections.groups.items'])
            ->latest()
            ->paginate(10);

        return view('admin.guide-pages.index', compact('guidePages'));
    }

    public function create()
    {
        $competitions = Competition::all();
        return view('admin.guide-pages.create', compact('competitions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'title' => 'required|string|max:255',
        ]);

        $guidePage = GuidePage::create([
            'competition_id' => $request->competition_id,
            'title' => $request->title,
        ]);

        return redirect()->route('admin.guide-pages.edit', $guidePage)
            ->with('success', 'ガイドページを作成しました。');
    }

    public function show(GuidePage $guidePage)
    {
        $guidePage->load(['competition', 'sections.groups.items.resource', 'sections.groups.items.collection']);
        return view('admin.guide-pages.show', compact('guidePage'));
    }

    public function edit(GuidePage $guidePage)
    {
        $guidePage->load(['competition', 'sections.groups.items.resource', 'sections.groups.items.collection']);
        $competitions = Competition::all();
        $resources = Resource::where('is_public', true)->get();
        $collections = Collection::all();
        
        return view('admin.guide-pages.edit', compact('guidePage', 'competitions', 'resources', 'collections'));
    }

    public function update(Request $request, GuidePage $guidePage)
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'title' => 'required|string|max:255',
        ]);

        $guidePage->update([
            'competition_id' => $request->competition_id,
            'title' => $request->title,
        ]);

        return redirect()->route('admin.guide-pages.edit', $guidePage)
            ->with('success', 'ガイドページを更新しました。');
    }

    public function destroy(GuidePage $guidePage)
    {
        $guidePage->delete();
        return redirect()->route('admin.guide-pages.index')
            ->with('success', 'ガイドページを削除しました。');
    }

    public function activate(GuidePage $guidePage)
    {
        $guidePage->activate();
        return redirect()->route('admin.guide-pages.index')
            ->with('success', 'ガイドページを有効化しました。');
    }

    public function preview(Request $request, GuidePage $guidePage)
    {
        $guidePage->load(['competition', 'sections.groups.items.resource', 'sections.groups.items.collection']);
        
        // 選手エミュレート用のデータ取得
        $emulatePlayerId = $request->get('emulate_player_id');
        $emulatePlayer = null;
        
        if ($emulatePlayerId) {
            $emulatePlayer = Player::select('players.*', 'competition_players.player_number')
                ->leftJoin('competition_players', function ($join) use ($guidePage) {
                    $join->on('players.id', '=', 'competition_players.player_id')
                         ->where('competition_players.competition_id', '=', $guidePage->competition_id);
                })
                ->where('players.id', $emulatePlayerId)
                ->first();
        }
        
        // 選手管理されているコレクションがあるかチェック
        $hasPlayerManagedCollections = false;
        foreach ($guidePage->sections as $section) {
            foreach ($section->groups as $group) {
                foreach ($group->items as $item) {
                    if ($item->type === 'collection' && $item->collection && $item->collection->is_player_managed) {
                        $hasPlayerManagedCollections = true;
                        break 3;
                    }
                }
            }
        }
        
        // エミュレート用の選手一覧（大会に登録されている選手のみ）
        $availablePlayers = collect();
        if ($hasPlayerManagedCollections) {
            $availablePlayers = Player::select('players.*', 'competition_players.player_number')
                ->join('competition_players', 'players.id', '=', 'competition_players.player_id')
                ->where('competition_players.competition_id', $guidePage->competition_id)
                ->orderBy('competition_players.player_number')
                ->orderBy('players.name')
                ->get();
        }
        
        return view('guide.preview', compact('guidePage', 'emulatePlayer', 'hasPlayerManagedCollections', 'availablePlayers'));
    }

    public function addSection(Request $request, GuidePage $guidePage)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $section = $guidePage->sections()->create([
            'title' => $request->title,
            'sort_order' => $guidePage->sections()->count(),
        ]);

        return response()->json([
            'success' => true,
            'section' => $section,
        ]);
    }

    public function addGroup(Request $request, GuidePageSection $section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $group = $section->groups()->create([
            'title' => $request->title,
            'sort_order' => $section->groups()->count(),
        ]);

        return response()->json([
            'success' => true,
            'group' => $group,
        ]);
    }

    public function addItem(Request $request, GuidePageGroup $group)
    {
        $request->validate([
            'type' => 'required|in:resource,link,text,collection',
            'title' => 'required|string|max:255',
            'url' => 'required_if:type,link|nullable|url',
            'resource_id' => 'required_if:type,resource|nullable|exists:resources,id',
            'text_content' => 'required_if:type,text|nullable|string|max:255',
            'show_copy_button' => 'boolean',
            'collection_id' => 'required_if:type,collection|nullable|exists:collections,id',
            'open_in_new_tab' => 'boolean',
        ]);

        $item = $group->items()->create([
            'type' => $request->type,
            'title' => $request->title,
            'url' => $request->url,
            'resource_id' => $request->resource_id,
            'text_content' => $request->text_content,
            'show_copy_button' => $request->boolean('show_copy_button', false),
            'collection_id' => $request->collection_id,
            'open_in_new_tab' => $request->boolean('open_in_new_tab', true),
            'sort_order' => $group->items()->count(),
        ]);

        return response()->json([
            'success' => true,
            'item' => $item->load(['resource', 'collection']),
        ]);
    }

    public function deleteSection(GuidePageSection $section)
    {
        $section->delete();
        return response()->json(['success' => true]);
    }

    public function deleteGroup(GuidePageGroup $group)
    {
        $group->delete();
        return response()->json(['success' => true]);
    }

    public function deleteItem(GuidePageItem $item)
    {
        $item->delete();
        return response()->json(['success' => true]);
    }

    public function updateSectionOrder(Request $request, GuidePage $guidePage)
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*' => 'exists:guide_page_sections,id',
        ]);

        DB::transaction(function () use ($request, $guidePage) {
            foreach ($request->sections as $index => $sectionId) {
                $guidePage->sections()->where('id', $sectionId)->update(['sort_order' => $index]);
            }
        });

        return response()->json(['success' => true]);
    }
}
