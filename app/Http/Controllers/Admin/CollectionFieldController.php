<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionField;
use Illuminate\Http\Request;

class CollectionFieldController extends Controller
{
    public function show(Collection $collection, CollectionField $field)
    {
        if ($field->collection_id !== $collection->id) {
            return response()->json(['success' => false, 'message' => '不正なリクエストです。'], 404);
        }

        return response()->json([
            'success' => true,
            'field' => $field
        ]);
    }

    public function store(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'content_type' => ['required', 'string', 'in:string,text,boolean,resource,date,time'],
            'max_length' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['boolean'],
        ]);

        // 名前の重複チェック
        if ($collection->fields()->where('name', $validated['name'])->exists()) {
            return response()->json([
                'success' => false,
                'errors' => ['name' => ['このフィールド名は既に使用されています。']]
            ]);
        }

        // sort_orderが未設定の場合は最後に追加
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = $collection->fields()->max('sort_order') + 1;
        }

        $field = $collection->fields()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'フィールドを追加しました。'
        ]);
    }

    public function update(Request $request, Collection $collection, CollectionField $field)
    {
        if ($field->collection_id !== $collection->id) {
            return response()->json(['success' => false, 'message' => '不正なリクエストです。'], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'content_type' => ['required', 'string', 'in:string,text,boolean,resource,date,time'],
            'max_length' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['boolean'],
        ]);

        // 名前の重複チェック（自分以外）
        if ($collection->fields()->where('name', $validated['name'])->where('id', '!=', $field->id)->exists()) {
            return response()->json([
                'success' => false,
                'errors' => ['name' => ['このフィールド名は既に使用されています。']]
            ]);
        }

        $field->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'フィールドを更新しました。'
        ]);
    }

    public function destroy(Collection $collection, CollectionField $field)
    {
        if ($field->collection_id !== $collection->id) {
            return response()->json(['success' => false, 'message' => '不正なリクエストです。'], 404);
        }

        // 関連するコンテンツも削除
        $field->contents()->delete();
        $field->delete();

        return response()->json([
            'success' => true,
            'message' => 'フィールドを削除しました。'
        ]);
    }
}
