<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionContent;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionContentController extends Controller
{
    public function store(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('collection_contents', 'name')->where('collection_id', $collection->id)
            ],
            'content_type' => ['required', Rule::in(['string', 'text', 'boolean', 'resource', 'date', 'time'])],
            'max_length' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'is_required' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // デフォルト値設定
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = $collection->contents()->count();
        }

        // コンテンツタイプに応じたmax_length設定
        if ($validated['content_type'] === 'string' && !isset($validated['max_length'])) {
            $validated['max_length'] = 255;
        } elseif ($validated['content_type'] === 'text' && !isset($validated['max_length'])) {
            $validated['max_length'] = 5000;
        } elseif (!in_array($validated['content_type'], ['string', 'text'])) {
            $validated['max_length'] = null;
        }

        $validated['collection_id'] = $collection->id;
        $content = CollectionContent::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'コンテンツを追加しました。',
            'content' => $content->load('collection')
        ]);
    }

    public function update(Request $request, Collection $collection, CollectionContent $content)
    {
        if ($content->collection_id !== $collection->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('collection_contents', 'name')
                    ->where('collection_id', $collection->id)
                    ->ignore($content->id)
            ],
            'content_type' => ['required', Rule::in(['string', 'text', 'boolean', 'resource', 'date', 'time'])],
            'max_length' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'is_required' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // コンテンツタイプに応じたmax_length設定
        if ($validated['content_type'] === 'string' && !isset($validated['max_length'])) {
            $validated['max_length'] = 255;
        } elseif ($validated['content_type'] === 'text' && !isset($validated['max_length'])) {
            $validated['max_length'] = 5000;
        } elseif (!in_array($validated['content_type'], ['string', 'text'])) {
            $validated['max_length'] = null;
        }

        $content->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'コンテンツを更新しました。',
            'content' => $content->fresh()
        ]);
    }

    public function destroy(Collection $collection, CollectionContent $content)
    {
        if ($content->collection_id !== $collection->id) {
            abort(404);
        }

        // データが存在する場合は警告
        $dataCount = $content->data()->count();
        if ($dataCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "このコンテンツには{$dataCount}件のデータが存在します。先にデータを削除してください。"
            ], 422);
        }

        $content->delete();

        return response()->json([
            'success' => true,
            'message' => 'コンテンツを削除しました。'
        ]);
    }

    public function updateOrder(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'content_ids' => ['required', 'array'],
            'content_ids.*' => ['integer', 'exists:collection_contents,id']
        ]);

        foreach ($validated['content_ids'] as $index => $contentId) {
            CollectionContent::where('id', $contentId)
                ->where('collection_id', $collection->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'コンテンツの順序を更新しました。'
        ]);
    }

    public function getResources(Request $request)
    {
        $query = Resource::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $resources = $query->orderBy('original_name')->get();

        return response()->json([
            'resources' => $resources->map(function ($resource) {
                return [
                    'id' => $resource->id,
                    'name' => $resource->original_name,
                    'description' => $resource->description,
                    'size' => $resource->file_size ? number_format($resource->file_size / 1024, 1) . ' KB' : null,
                    'created_at' => $resource->created_at->format('Y/m/d'),
                ];
            })
        ]);
    }
}