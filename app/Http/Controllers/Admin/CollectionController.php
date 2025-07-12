<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
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
        
        return view('admin.collections.index', compact('collections'));
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
            'is_competition_managed' => ['boolean'],
            'is_player_managed' => ['boolean'],
        ]);

        // 選手ごと管理の場合は大会ごと管理を強制ON
        if ($validated['is_player_managed']) {
            $validated['is_competition_managed'] = true;
        }

        $collection = Collection::create($validated);

        return redirect()->route('admin.collections.index')
            ->with('success', 'コレクションを作成しました。');
    }

    public function show(Collection $collection)
    {
        $collection->load(['fields', 'accessControls.apiToken', 'contents.field', 'contents.competition', 'contents.player']);
        $apiTokens = ApiToken::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.collections.show', compact('collection', 'apiTokens'));
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
            'is_competition_managed' => ['boolean'],
            'is_player_managed' => ['boolean'],
        ]);

        // 選手ごと管理の場合は大会ごと管理を強制ON
        if ($validated['is_player_managed']) {
            $validated['is_competition_managed'] = true;
        }

        $collection->update($validated);

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
            'type' => 'required|string|in:ip_whitelist,api_token,token_required',
            'value' => 'required|string',
        ], [
            'type.required' => 'タイプを選択してください。',
            'type.in' => '有効なタイプを選択してください。',
            'value.required' => '値を入力してください。',
        ]);

        // APIトークンの場合、存在確認
        if ($validated['type'] === 'api_token') {
            $request->validate([
                'value' => 'exists:api_tokens,id',
            ], [
                'value.exists' => '選択されたAPIトークンが見つかりません。',
            ]);
        }

        // IPアドレスの場合、形式確認
        if ($validated['type'] === 'ip_whitelist') {
            $request->validate([
                'value' => 'ip',
            ], [
                'value.ip' => '有効なIPアドレスを入力してください。',
            ]);
        }

        $collection->accessControls()->create([
            'type' => $validated['type'],
            'value' => $validated['value'],
            'is_active' => true,
        ]);

        return back()->with('success', 'アクセス制御を追加しました。');
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
