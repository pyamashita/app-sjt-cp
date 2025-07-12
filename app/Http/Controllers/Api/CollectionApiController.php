<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionContent;
use App\Models\CollectionAccessControl;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CollectionApiController extends Controller
{
    /**
     * コレクション一覧取得
     */
    public function index(Request $request): JsonResponse
    {
        // アクセス制御チェック
        if (!$this->checkAccess($request)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $collections = Collection::select(['id', 'name', 'display_name', 'description', 'is_competition_managed', 'is_player_managed'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $collections
        ]);
    }

    /**
     * 特定コレクションの詳細取得
     */
    public function show(Request $request, Collection $collection): JsonResponse
    {
        // アクセス制御チェック
        if (!$this->checkCollectionAccess($request, $collection)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $collection->load(['fields' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $collection->id,
                'name' => $collection->name,
                'display_name' => $collection->display_name,
                'description' => $collection->description,
                'is_competition_managed' => $collection->is_competition_managed,
                'is_player_managed' => $collection->is_player_managed,
                'fields' => $collection->fields->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'name' => $field->name,
                        'content_type' => $field->content_type,
                        'is_required' => $field->is_required,
                        'sort_order' => $field->sort_order,
                    ];
                })
            ]
        ]);
    }

    /**
     * コレクションコンテンツ取得
     */
    public function contents(Request $request, Collection $collection): JsonResponse
    {
        // アクセス制御チェック
        if (!$this->checkCollectionAccess($request, $collection)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $query = CollectionContent::with(['field', 'competition', 'player'])
            ->where('collection_id', $collection->id);

        // フィルタリング
        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }

        if ($request->filled('player_id')) {
            $query->where('player_id', $request->player_id);
        }

        if ($request->filled('field_id')) {
            $query->where('field_id', $request->field_id);
        }

        // ページング
        $perPage = min($request->get('per_page', 50), 100); // 最大100件
        $contents = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $contents->items(),
            'pagination' => [
                'current_page' => $contents->currentPage(),
                'last_page' => $contents->lastPage(),
                'per_page' => $contents->perPage(),
                'total' => $contents->total(),
            ]
        ]);
    }

    /**
     * 特定コンテンツの取得
     */
    public function getContent(Request $request, Collection $collection): JsonResponse
    {
        // アクセス制御チェック
        if (!$this->checkCollectionAccess($request, $collection)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'competition_id' => 'nullable|exists:competitions,id',
            'player_id' => 'nullable|exists:players,id',
        ]);

        $query = CollectionContent::with(['field', 'competition', 'player'])
            ->where('collection_id', $collection->id);

        if ($validated['competition_id'] ?? null) {
            $query->where('competition_id', $validated['competition_id']);
        }

        if ($validated['player_id'] ?? null) {
            $query->where('player_id', $validated['player_id']);
        }

        $contents = $query->get();

        // フィールド別にグループ化
        $groupedContents = $contents->groupBy('field_id')->map(function ($items) {
            $item = $items->first();
            return [
                'field_id' => $item->field_id,
                'field_name' => $item->field->name,
                'field_type' => $item->field->content_type,
                'value' => $item->value,
                'formatted_value' => $item->formatted_value,
                'updated_at' => $item->updated_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'collection_id' => $collection->id,
                'competition_id' => $validated['competition_id'] ?? null,
                'player_id' => $validated['player_id'] ?? null,
                'contents' => $groupedContents
            ]
        ]);
    }

    /**
     * コンテンツの作成・更新
     */
    public function storeContent(Request $request, Collection $collection): JsonResponse
    {
        // アクセス制御チェック
        if (!$this->checkCollectionAccess($request, $collection)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // トークン必須チェック
        if (!$this->checkTokenRequired($request, $collection)) {
            return response()->json(['error' => 'API token required'], 401);
        }

        $validated = $request->validate([
            'competition_id' => $collection->is_competition_managed ? 'required|exists:competitions,id' : 'nullable',
            'player_id' => $collection->is_player_managed ? 'required|exists:players,id' : 'nullable',
            'contents' => 'required|array',
            'contents.*.field_id' => 'required|exists:collection_fields,id',
            'contents.*.value' => 'nullable',
        ]);

        $collection->load('fields');

        // 既存データを削除
        CollectionContent::where('collection_id', $collection->id)
            ->where('competition_id', $validated['competition_id'] ?? null)
            ->where('player_id', $validated['player_id'] ?? null)
            ->delete();

        // 新しいデータを保存
        $savedContents = [];
        foreach ($validated['contents'] as $contentData) {
            $field = $collection->fields->where('id', $contentData['field_id'])->first();
            if (!$field) {
                continue;
            }

            $value = $contentData['value'] ?? null;
            if ($value !== null && $value !== '') {
                // boolean型の変換
                if ($field->content_type === 'boolean') {
                    $value = $value ? '1' : '0';
                }

                $content = CollectionContent::create([
                    'collection_id' => $collection->id,
                    'field_id' => $field->id,
                    'competition_id' => $validated['competition_id'] ?? null,
                    'player_id' => $validated['player_id'] ?? null,
                    'value' => $value,
                ]);

                $savedContents[] = [
                    'field_id' => $content->field_id,
                    'field_name' => $field->name,
                    'value' => $content->value,
                    'formatted_value' => $content->formatted_value,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Content saved successfully',
            'data' => [
                'collection_id' => $collection->id,
                'competition_id' => $validated['competition_id'] ?? null,
                'player_id' => $validated['player_id'] ?? null,
                'contents' => $savedContents
            ]
        ]);
    }

    /**
     * アクセス制御チェック（全般）
     */
    private function checkAccess(Request $request): bool
    {
        $ipAddress = $request->ip();
        $apiToken = $request->bearerToken() ?: $request->get('api_token');

        // 管理画面からのアクセスは許可
        if ($request->hasSession() && auth()->check()) {
            return true;
        }

        // 基本的なアクセス制御をここで実装
        return true; // 一旦すべて許可
    }

    /**
     * コレクション固有のアクセス制御チェック
     */
    private function checkCollectionAccess(Request $request, Collection $collection): bool
    {
        $ipAddress = $request->ip();
        $apiToken = $request->bearerToken() ?: $request->get('api_token');

        return CollectionAccessControl::isAccessAllowed($collection, $ipAddress, $apiToken);
    }

    /**
     * トークン必須チェック
     */
    private function checkTokenRequired(Request $request, Collection $collection): bool
    {
        // 管理画面からのアクセスは許可
        if ($request->hasSession() && auth()->check()) {
            return true;
        }

        $tokenRequiredControls = $collection->accessControls()
            ->where('type', 'token_required')
            ->where('is_active', true)
            ->exists();

        if ($tokenRequiredControls) {
            $apiToken = $request->bearerToken() ?: $request->get('api_token');
            return !empty($apiToken);
        }

        return true;
    }
}