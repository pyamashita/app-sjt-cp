<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\GuidePage;
use App\Models\GuidePageSection;
use App\Models\GuidePageGroup;
use App\Models\GuidePageItem;
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
        $guidePage->load(['competition', 'sections.groups.items.resource']);
        return view('admin.guide-pages.show', compact('guidePage'));
    }

    public function edit(GuidePage $guidePage)
    {
        $guidePage->load(['competition', 'sections.groups.items.resource']);
        $competitions = Competition::all();
        $resources = Resource::where('is_public', true)->get();
        
        return view('admin.guide-pages.edit', compact('guidePage', 'competitions', 'resources'));
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

    public function preview(GuidePage $guidePage)
    {
        $guidePage->load(['competition', 'sections.groups.items.resource']);
        return view('guide.preview', compact('guidePage'));
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
            'type' => 'required|in:resource,link',
            'title' => 'required|string|max:255',
            'url' => 'required_if:type,link|nullable|url',
            'resource_id' => 'required_if:type,resource|nullable|exists:resources,id',
            'open_in_new_tab' => 'boolean',
        ]);

        $item = $group->items()->create([
            'type' => $request->type,
            'title' => $request->title,
            'url' => $request->url,
            'resource_id' => $request->resource_id,
            'open_in_new_tab' => $request->boolean('open_in_new_tab', true),
            'sort_order' => $group->items()->count(),
        ]);

        return response()->json([
            'success' => true,
            'item' => $item->load('resource'),
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
