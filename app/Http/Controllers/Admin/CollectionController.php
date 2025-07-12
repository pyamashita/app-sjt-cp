<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionAccessControl;
use App\Models\Competition;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Collection::with(['accessControls']);
        
        // 年度フィルタ
        if ($request->filled('year')) {
            $query->byYear($request->year);
        }
        
        // 検索
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $collections = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // 年度リスト（フィルタ用）
        $years = Collection::distinct()->pluck('year')->filter()->sort()->values();
        
        return view('admin.collections.index', compact('collections', 'years'));
    }

    public function create()
    {
        $competitions = Competition::orderBy('name')->get();
        
        return view('admin.collections.create', compact('competitions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:collections,name', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 10)],
            'is_competition_managed' => ['boolean'],
            'is_player_managed' => ['boolean'],
            'access_controls' => ['array'],
            'access_controls.*.ip_address' => ['required_with:access_controls', 'ip'],
            'access_controls.*.description' => ['nullable', 'string', 'max:255'],
        ]);

        // 選手ごと管理の場合は大会ごと管理を強制ON
        if ($validated['is_player_managed']) {
            $validated['is_competition_managed'] = true;
        }

        $collection = Collection::create($validated);

        // アクセス制限の登録
        if (!empty($validated['access_controls'])) {
            foreach ($validated['access_controls'] as $accessControl) {
                if (!empty($accessControl['ip_address'])) {
                    $collection->accessControls()->create($accessControl);
                }
            }
        }

        return redirect()->route('admin.collections.index')
            ->with('success', 'コレクションを作成しました。');
    }

    public function show(Collection $collection)
    {
        $collection->load(['contents', 'accessControls', 'data.content', 'data.competition', 'data.player']);
        
        return view('admin.collections.show', compact('collection'));
    }

    public function edit(Collection $collection)
    {
        $collection->load(['accessControls']);
        $competitions = Competition::orderBy('name')->get();
        
        return view('admin.collections.edit', compact('collection', 'competitions'));
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('collections', 'name')->ignore($collection->id),
                'regex:/^[a-zA-Z0-9_-]+$/'
            ],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 10)],
            'is_competition_managed' => ['boolean'],
            'is_player_managed' => ['boolean'],
            'access_controls' => ['array'],
            'access_controls.*.ip_address' => ['required_with:access_controls', 'ip'],
            'access_controls.*.description' => ['nullable', 'string', 'max:255'],
        ]);

        // 選手ごと管理の場合は大会ごと管理を強制ON
        if ($validated['is_player_managed']) {
            $validated['is_competition_managed'] = true;
        }

        $collection->update($validated);

        // アクセス制限の更新
        $collection->accessControls()->delete();
        if (!empty($validated['access_controls'])) {
            foreach ($validated['access_controls'] as $accessControl) {
                if (!empty($accessControl['ip_address'])) {
                    $collection->accessControls()->create($accessControl);
                }
            }
        }

        return redirect()->route('admin.collections.index')
            ->with('success', 'コレクションを更新しました。');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();
        
        return redirect()->route('admin.collections.index')
            ->with('success', 'コレクションを削除しました。');
    }

    public function addAccessControl(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'ip_address' => ['required', 'ip'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $collection->accessControls()->create($validated);

        return redirect()->back()->with('success', 'アクセス制限を追加しました。');
    }

    public function removeAccessControl(Collection $collection, CollectionAccessControl $accessControl)
    {
        if ($accessControl->collection_id !== $collection->id) {
            abort(404);
        }

        $accessControl->delete();

        return redirect()->back()->with('success', 'アクセス制限を削除しました。');
    }
}
